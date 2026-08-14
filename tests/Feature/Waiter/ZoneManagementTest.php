<?php

namespace Tests\Feature\Waiter;

use App\Models\Table;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ZoneManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function serveur_can_create_zone_and_generate_tables(): void
    {
        $this->actingAsServeur()
            ->post(route('waiter.settings.zones.store'), [
                'name' => 'Terrasse Nord',
                'prefix' => 'TN',
                'tables_count' => 3,
                'description' => 'Zone test',
            ])
            ->assertRedirect(route('waiter.settings.zones'))
            ->assertSessionHas('success');

        $zone = Zone::where('name', 'Terrasse Nord')->firstOrFail();
        $this->assertSame('TN', $zone->prefix);
        $this->assertSame(3, $zone->tables_count);
        $this->assertSame(3, $zone->tables()->count());
        $this->assertDatabaseHas('tables', [
            'zone_id' => $zone->id,
            'name' => 'TN001',
        ]);
    }

    #[Test]
    public function zone_update_can_increase_table_count(): void
    {
        $this->actingAsServeur()
            ->post(route('waiter.settings.zones.store'), [
                'name' => 'Salon VIP',
                'prefix' => 'SV',
                'tables_count' => 2,
            ])
            ->assertRedirect(route('waiter.settings.zones'));

        $zone = Zone::where('name', 'Salon VIP')->firstOrFail();

        $this->actingAsServeur()
            ->put(route('waiter.settings.zones.update', $zone), [
                'name' => 'Salon VIP',
                'prefix' => 'SV',
                'tables_count' => 4,
            ])
            ->assertRedirect(route('waiter.settings.zones'));

        $this->assertSame(4, $zone->fresh()->tables()->count());
        $this->assertDatabaseHas('tables', ['zone_id' => $zone->id, 'name' => 'SV004']);
    }

    #[Test]
    public function deleting_zone_unassigns_tables(): void
    {
        $this->actingAsServeur()
            ->post(route('waiter.settings.zones.store'), [
                'name' => 'Zone Temp',
                'prefix' => 'ZT',
                'tables_count' => 2,
            ])
            ->assertRedirect();

        $zone = Zone::where('name', 'Zone Temp')->firstOrFail();
        $tableIds = $zone->tables()->pluck('id');

        $this->actingAsServeur()
            ->delete(route('waiter.settings.zones.destroy', $zone))
            ->assertRedirect(route('waiter.settings.zones'));

        $this->assertDatabaseMissing('zones', ['id' => $zone->id]);
        foreach ($tableIds as $tableId) {
            $this->assertDatabaseHas('tables', [
                'id' => $tableId,
                'zone_id' => null,
            ]);
        }
    }

    #[Test]
    public function waiter_index_exposes_zones_for_filtering(): void
    {
        $zone = Zone::create([
            'name' => 'Piscine Ext',
            'prefix' => 'PE',
            'tables_count' => 1,
        ]);
        Table::factory()->create([
            'zone_id' => $zone->id,
            'zone' => $zone->name,
            'name' => 'PE001',
            'status' => 'free',
        ]);

        $this->actingAsServeur()
            ->get(route('waiter.index'))
            ->assertOk()
            ->assertViewHas('zones', fn ($zones) => $zones->contains('id', $zone->id));
    }

    #[Test]
    public function zone_name_must_be_unique(): void
    {
        Zone::create(['name' => 'DupZone', 'prefix' => 'DZ', 'tables_count' => 1]);

        $this->actingAsServeur()
            ->from(route('waiter.settings.zones'))
            ->post(route('waiter.settings.zones.store'), [
                'name' => 'DupZone',
                'prefix' => 'D2',
                'tables_count' => 1,
            ])
            ->assertRedirect(route('waiter.settings.zones'))
            ->assertSessionHasErrors('name');
    }
}
