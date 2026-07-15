<?php

namespace Tests\Feature\Cashier;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierTicketReportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_open_ticket_center(): void
    {
        $admin = User::factory()->admin()->create();

        Commande::factory()->payee()->create([
            'type' => 'kitchen',
            'total' => 120.50,
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('cashier.tickets'));

        $response->assertOk();
        $response->assertSee('Tickets et rapport PDF');
        $response->assertSee('120.50', false);
    }

    #[Test]
    public function caissier_cannot_access_ticket_center(): void
    {
        $caissier = User::factory()->caissier()->create();

        $response = $this->actingAs($caissier)->get(route('cashier.tickets'));

        $response->assertRedirect(route('pos.index'));
    }

    #[Test]
    public function admin_can_print_summary_ticket_for_a_day(): void
    {
        $admin = User::factory()->admin()->create();
        $date = now()->toDateString();

        Commande::factory()->payee()->create([
            'type' => 'kitchen',
            'total' => 200.00,
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('cashier.tickets.print', [
            'date' => $date,
            'type' => 'summary',
        ]));

        $response->assertOk();
        $response->assertSee('Ticket resume du CA');
        $response->assertSee('200.00', false);
    }

    #[Test]
    public function admin_can_download_pdf_report_for_date_range(): void
    {
        $admin = User::factory()->admin()->create();

        Commande::factory()->count(2)->payee()->create([
            'type' => 'kitchen',
            'total' => 50.00,
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('cashier.tickets.report.pdf', [
            'date_start' => now()->toDateString(),
            'date_end' => now()->toDateString(),
            'type' => 'detailed',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    #[Test]
    public function detailed_ticket_aggregates_same_product_quantities_for_the_day(): void
    {
        $admin = User::factory()->admin()->create();
        $date = now()->toDateString();

        $product = Produit::factory()->create(['name' => 'Pizza Margherita']);

        $orderA = Commande::factory()->payee()->create([
            'type' => 'kitchen',
            'total' => 120.00,
            'updated_at' => now(),
        ]);

        $orderB = Commande::factory()->payee()->create([
            'type' => 'kitchen',
            'total' => 80.00,
            'updated_at' => now(),
        ]);

        CommandeDetail::factory()->forCommande($orderA)->forProduct($product)->create([
            'quantity' => 2,
            'price' => 40,
        ]);

        CommandeDetail::factory()->forCommande($orderB)->forProduct($product)->create([
            'quantity' => 3,
            'price' => 40,
        ]);

        $response = $this->actingAs($admin)->get(route('cashier.tickets.print', [
            'date' => $date,
            'type' => 'detailed',
        ]));

        $response->assertOk();
        $response->assertSee('Pizza Margherita');
        $response->assertSee('x5');
        $response->assertSee('200.00', false);
    }
}
