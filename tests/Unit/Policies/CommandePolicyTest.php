<?php

namespace Tests\Unit\Policies;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\Table;
use App\Models\User;
use App\Policies\CommandePolicy;
use App\Support\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommandePolicyTest extends TestCase
{
    use RefreshDatabase;

    private CommandePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CommandePolicy();
    }

    #[Test]
    public function admin_and_super_admin_can_update_unpaid_waiter_orders(): void
    {
        $commande = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'en_cuisine',
            'table_id' => Table::factory(),
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->assertTrue($this->policy->update($admin, $commande));
        $this->assertTrue($this->policy->update(SuperAdmin::make(), $commande));
        $this->assertTrue($this->policy->view($admin, $commande));
    }

    #[Test]
    public function other_roles_can_view_but_cannot_update(): void
    {
        $commande = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'en_cuisine',
            'table_id' => Table::factory(),
        ]);

        $caissier = User::factory()->create(['role' => 'caissier', 'status' => 'active']);
        $serveur = User::factory()->create(['role' => 'serveur', 'status' => 'active']);

        $this->assertTrue($this->policy->view($caissier, $commande));
        $this->assertTrue($this->policy->view($serveur, $commande));
        $this->assertFalse($this->policy->update($caissier, $commande));
        $this->assertFalse($this->policy->update($serveur, $commande));
    }

    #[Test]
    public function paid_kitchen_orders_remain_updatable_for_the_same_vente(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $paid = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'payee',
            'table_id' => Table::factory(),
        ]);

        $this->assertTrue($this->policy->update($admin, $paid));
    }

    #[Test]
    public function paid_or_cancelled_supplier_orders_are_not_updatable(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $cancelled = Commande::factory()->create([
            'type' => 'kitchen',
            'status' => 'annule',
            'table_id' => Table::factory(),
        ]);
        $supplier = Commande::factory()->supplier()->create([
            'fournisseur_id' => Fournisseur::factory(),
            'table_id' => null,
        ]);

        $this->assertFalse($this->policy->update($admin, $cancelled));
        $this->assertFalse($this->policy->update($admin, $supplier));
    }
}
