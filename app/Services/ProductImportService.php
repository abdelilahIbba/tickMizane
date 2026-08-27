<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Produit;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Shuchkin\SimpleXLS;

class ProductImportService
{
    private const DEFAULT_STOCK_QUANTITY = 100;
    private const DEFAULT_ALERT_STOCK = 10;

    /**
     * Import products from a legacy XLS file.
     *
        * @return array{products_created:int,products_skipped:int,categories_created:int,rows_processed:int}
     */
    public function importFromXls(string $filePath): array
    {
        $xls = SimpleXLS::parseFile($filePath);

        if (!$xls) {
            throw new RuntimeException('Le fichier Excel est invalide ou non lisible.');
        }

        $items = $this->extractImportRows($xls);

        if (empty($items)) {
            throw new RuntimeException('Aucun produit importable n\'a ete detecte dans ce fichier.');
        }

        $stats = [
            'products_created' => 0,
            'products_skipped' => 0,
            'categories_created' => 0,
            'rows_processed' => count($items),
        ];

        DB::transaction(function () use ($items, &$stats): void {
            $categoryCache = [];

            foreach ($items as $item) {
                $categoryKey = mb_strtolower($item['category']);

                if (!isset($categoryCache[$categoryKey])) {
                    $category = $this->findCategoryByName($item['category']);

                    if (!$category) {
                        $category = Category::create([
                            'name' => $item['category'],
                            'status' => 'active',
                        ]);
                        $stats['categories_created']++;
                    } elseif ($category->status !== 'active') {
                        $category->update(['status' => 'active']);
                    }

                    $categoryCache[$categoryKey] = $category;
                }

                $category = $categoryCache[$categoryKey];
                $existingProduct = $this->findProductByCategoryAndName($category->id, $item['name']);

                if ($existingProduct) {
                    $stats['products_skipped']++;
                    continue;
                }

                $payload = [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'price_vente' => $item['price_vente'],
                    'price_achat' => round($item['price_vente'] * 0.45, 2),
                    'stock_quantity' => self::DEFAULT_STOCK_QUANTITY,
                    'alert_stock' => self::DEFAULT_ALERT_STOCK,
                    'unit' => $this->inferUnit($item['name']),
                    'status' => 'active',
                    'kitchen_active' => $this->inferKitchenActive($item['category'], $item['name']),
                ];

                $created = Produit::create($payload);

                StockMovement::create([
                    'produit_id' => $created->id,
                    'type' => 'in',
                    'quantity' => self::DEFAULT_STOCK_QUANTITY,
                    'reason' => 'ajustement',
                ]);

                $stats['products_created']++;
            }
        });

        return $stats;
    }

    /**
     * @return array<int,array{category:string,name:string,price_vente:float}>
     */
    private function extractImportRows(SimpleXLS $xls): array
    {
        $items = [];

        foreach ($xls->sheetNames() as $sheetIndex => $sheetName) {
            $rows = $xls->rows($sheetIndex);
            $currentCategory = null;

            foreach ($rows as $row) {
                $label = $this->normalizeLabel((string)($row[1] ?? ''));
                $priceRaw = $this->normalizeLabel((string)($row[2] ?? ''));

                if ($label === '') {
                    continue;
                }

                $price = $this->parsePrice($priceRaw);
                if ($price !== null) {
                    $effectiveCategory = $currentCategory ?? $this->defaultCategoryFromSheetName($sheetName);
                    if ($effectiveCategory === null) {
                        continue;
                    }

                    $items[] = [
                        'category' => $effectiveCategory,
                        'name' => $label,
                        'price_vente' => $price,
                    ];
                    continue;
                }

                if ($this->looksLikeCategoryHeader($label)) {
                    $currentCategory = $this->normalizeCategoryName($label);
                }
            }
        }

        return $items;
    }

    private function defaultCategoryFromSheetName(string $sheetName): ?string
    {
        $clean = $this->normalizeCategoryName($sheetName);
        return $clean !== '' ? $clean : null;
    }

    private function looksLikeCategoryHeader(string $label): bool
    {
        if (mb_strlen($label) > 95) {
            return false;
        }

        return (bool) preg_match(
            '/(boissons|dessert|dejeuner|dejeuner leger|plats|menu|petit\-?dejeuner|petits plaisirs|breakfast)/iu',
            $this->stripAccents(mb_strtolower($label))
        );
    }

    private function normalizeCategoryName(string $label): string
    {
        $withoutEmoji = preg_replace('/^[^\p{L}\p{N}]+/u', '', trim($label)) ?? trim($label);
        return preg_replace('/\s+/u', ' ', $withoutEmoji) ?? $withoutEmoji;
    }

    private function normalizeLabel(string $value): string
    {
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = trim($value);
        return preg_replace('/\s+/u', ' ', $value) ?? $value;
    }

    private function parsePrice(string $priceRaw): ?float
    {
        if ($priceRaw === '') {
            return null;
        }

        $normalized = str_replace([' ', ','], ['', '.'], $priceRaw);

        if (!preg_match('/^-?\d+(\.\d+)?$/', $normalized)) {
            return null;
        }

        $value = (float) $normalized;
        return $value > 0 ? round($value, 2) : null;
    }

    private function findCategoryByName(string $name): ?Category
    {
        return Category::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
    }

    private function findProductByCategoryAndName(int $categoryId, string $name): ?Produit
    {
        return Produit::query()
            ->where('category_id', $categoryId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();
    }

    private function inferKitchenActive(string $category, string $productName): bool
    {
        $categoryNormalized = $this->stripAccents(mb_strtolower($category));
        $productNormalized = $this->stripAccents(mb_strtolower($productName));

        if (str_contains($categoryNormalized, 'boissons')) {
            if (str_contains($productNormalized, 'eau') || str_contains($productNormalized, 'soda') || str_contains($productNormalized, 'ice tea')) {
                return false;
            }
        }

        return true;
    }

    private function inferUnit(string $name): string
    {
        $normalized = $this->stripAccents(mb_strtolower($name));

        if (str_contains($normalized, 'eau') || str_contains($normalized, 'soda') || str_contains($normalized, 'ice tea')) {
            return 'bouteille';
        }

        if (str_contains($normalized, 'jus') || str_contains($normalized, 'citronnade') || str_contains($normalized, 'mojito')) {
            return 'verre';
        }

        if (str_contains($normalized, 'cafe') || str_contains($normalized, 'the')) {
            return 'tasse';
        }

        return 'portion';
    }

    private function stripAccents(string $value): string
    {
        return strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'œ' => 'oe',
        ]);
    }
}
