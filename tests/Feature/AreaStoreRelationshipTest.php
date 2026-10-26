<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaStoreRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_claim_form_offers_area_and_stores_with_area_metadata(): void
    {
        $area = Area::create(['name' => 'Hall 1']);
        $store = Store::create([
            'name' => 'Merchant Mombee',
            'area_id' => $area->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('claims.create'));

        $response->assertOk()
            ->assertSee('name="area_id"', false)
            ->assertSee('data-area-id="'.$area->id.'"', false)
            ->assertSee('Merchant Mombee');

        $this->assertSame($area->id, $store->area->id);
        $this->assertTrue($area->stores->contains('id', $store->id));
    }

    public function test_store_must_belong_to_selected_area_for_a_claim(): void
    {
        $areaOne = Area::create(['name' => 'Hall 1']);
        $areaTwo = Area::create(['name' => 'Hall 2']);
        $store = Store::create([
            'name' => 'Merchant Mombee',
            'area_id' => $areaOne->id,
            'is_active' => true,
        ]);

        $response = $this->from(route('claims.create'))->post(route('claims.store'), [
            'claim_date' => '2026-10-26',
            'area_id' => $areaTwo->id,
            'store_id' => $store->id,
            'pic_name' => 'Test PIC',
            'pic_phone' => '081234567890',
            'total_claimed' => '250000',
            'mandiri_account_number' => '1234567890',
            'account_holder_name' => 'Test PIC',
            'transaction_date' => '2026-10-26',
        ]);

        $response->assertRedirect(route('claims.create'))
            ->assertSessionHasErrors('store_id');
    }
}
