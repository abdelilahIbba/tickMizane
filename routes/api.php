<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\VenteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| Public Routes (No authentication required)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require authentication)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->group(function () {
        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Products (full CRUD)
        Route::apiResource('products', ProductController::class);
        Route::get('/products/barcode/{barcode}', [ProductController::class, 'barcode']);
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);

        // Stock Movements
        Route::get('/stock', [StockController::class, 'index']);
        Route::post('/stock/{product}/adjust', [StockController::class, 'adjust']);
        Route::post('/stock/{product}/add', [StockController::class, 'add']);
        Route::post('/stock/{product}/remove', [StockController::class, 'remove']);
        Route::post('/stock/bulk-adjust', [StockController::class, 'bulkAdjust']);
        Route::get('/stock/low-stock', [StockController::class, 'lowStock']);
        Route::get('/stock/stats', [StockController::class, 'stats']);
        Route::get('/stock/{product}/history', [StockController::class, 'history']);
        Route::get('/stock/valuation', [StockController::class, 'valuation']);

        // Tables management (admin can manage all tables)
        Route::apiResource('tables', TableController::class);
        Route::get('/tables/zones', [TableController::class, 'zones']);
        Route::get('/tables/zone/{zone}', [TableController::class, 'byZone']);
        Route::get('/tables/summary', [TableController::class, 'summary']);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/dashboard', [ReportController::class, 'dashboard']);
            Route::get('/sales/daily', [ReportController::class, 'dailySales']);
            Route::get('/sales/range', [ReportController::class, 'salesByRange']);
            Route::get('/products', [ReportController::class, 'productReport']);
            Route::get('/categories', [ReportController::class, 'categoryReport']);
            Route::get('/users', [ReportController::class, 'userReport']);
            Route::get('/stock', [ReportController::class, 'stockReport']);
            Route::get('/payments', [ReportController::class, 'paymentReport']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Caissier Routes (+ Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:caissier'])->group(function () {
        // POS Data
        Route::get('/pos', [PosController::class, 'index']);
        Route::get('/pos/categories', [CategoryController::class, 'posCategories']);
        Route::get('/pos/products/{category}', [ProductController::class, 'byCategory']);

        // POS Operations
        Route::post('/pos/sale', [PosController::class, 'createSale']);
        Route::post('/pos/checkout', [PosController::class, 'checkout']);
        Route::get('/pos/pending', [PosController::class, 'pendingSales']);
        Route::get('/pos/sale/{vente}', [PosController::class, 'getSale']);
        Route::post('/pos/sale/{vente}/payment', [PosController::class, 'processPayment']);
        Route::post('/pos/sale/{vente}/cancel', [PosController::class, 'cancelSale']);
        Route::post('/pos/sale/{vente}/items', [PosController::class, 'addItems']);

        // Ventes
        Route::get('/ventes', [VenteController::class, 'index']);
        Route::get('/ventes/my-sales', [VenteController::class, 'mySales']);
        Route::get('/ventes/{vente}', [VenteController::class, 'show']);
        Route::get('/ventes/{vente}/payments', [VenteController::class, 'payments']);
        Route::post('/ventes/{vente}/payment', [VenteController::class, 'addPayment']);
        Route::get('/ventes/{vente}/receipt', [VenteController::class, 'receipt']);
        Route::get('/ventes/search', [VenteController::class, 'search']);
        Route::get('/ventes/stats', [VenteController::class, 'stats']);
        Route::post('/payments/{payment}/refund', [VenteController::class, 'refund']);

        // Today's dashboard
        Route::get('/dashboard', [ReportController::class, 'dashboard']);
    });

    /*
    |--------------------------------------------------------------------------
    | Serveur Routes (+ Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:serveur'])->group(function () {
        // Tables
        Route::get('/tables', [TableController::class, 'index']);
        Route::get('/tables/available', [TableController::class, 'available']);
        Route::get('/tables/{table}', [TableController::class, 'show']);
        Route::patch('/tables/{table}/status', [TableController::class, 'updateStatus']);
        Route::post('/tables/{table}/transfer', [TableController::class, 'transfer']);
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications (All authenticated users)
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->group(function () {
        Route::get('/', function (Request $request) {
            return $request->user()->notifications()->latest()->paginate(20);
        });
        Route::get('/unread', function (Request $request) {
            return $request->user()->unreadNotifications()->latest()->get();
        });
        Route::get('/count', function (Request $request) {
            return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
        });
        Route::post('/{id}/read', function (Request $request, $id) {
            $request->user()->notifications()->where('id', $id)->first()?->markAsRead();
            return response()->json(['success' => true]);
        });
        Route::post('/read-all', function (Request $request) {
            $request->user()->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        });
    });
});
