<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // The base list makes sure the requested merchant names exist.
        // This does not delete or deactivate any existing stores.
        $stores = [
            'Merchant Mombee',
            'Merchant Trademark',
            'Merchant WOF',
            'Merchant PPM',
            'Merchant BFF',
            'Merchant Inacraft',
            'Merchant Jacoweek',
        ];

        foreach ($stores as $name) {
            Store::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        //Mombee
        // Takabook, My Kids Collection
        // Mirugo
        // Ciakpo
        // Crystal of The Sea
        // Porto Kids
        // Kooka
        // Dooglee, Tuutuu
        // Booboo
        // Pigeon
        // Paninti (Oonew, Emily, Bear, Sucvent, dll)
        // Omica
        // Cocolatte
        // Pokana
        // Cotton Mom
        // Eclipse Hampers
        // Yummy Bites
        // Mooimom
        // Libby Baby
        // Gea Baby, Sanosan, Oryn
        // Wetbrush
        // Mustela, Interlac, Farlin, Motherlove
        // Brothmaster
        // Naomichan
        // Sakumini
        // Grouu
        // Moell, Mom Uung
        // Gently
        // Morinaga, Morigro
        // Morigro
        // Pediasure
        // Ace Kids
        // Nutrilon
        // Wyeth S26
        // Frisian Flag
        // Baby Happy
        // Etawalin
        // Enfagrow
        // Kodomo

        // Assign stores to areas here if you prefer seeding the area map.
        // Any area name can be added as a key; each item is a store name.
        // Example:
        // $areaAssignments = [
        //     'Hall 1' => ['Merchant Mombee', 'Merchant Trademark'],
        //     'Hall 2' => ['Merchant WOF', 'Merchant PPM'],
        // ];
        //
        // Leaving the map empty means you can create areas and assign stores
        // through the Areas and Stores pages in the admin UI instead.
        $areaAssignments = [
            'TPH (Tembusan Permata Hijau)' => ['CoI (Collector of Indonesia)', 'OMO Market', 'PPM (Perfume Pop Market)', 'Jacoweek', 'FnB Bank Mandiri'],
            'The Hall Senayan City (Lantai 8)' => ['Merchant Mombee', 'Merchant Trademark'],
            'Lantai 5 Senayan City' => ['Inacraft', 'BFF', 'WOF', 'Erajaya', 'Blilbli', 'Optik Seis'],
            'LG Senayan City' => ['Mandiri Travel Deals'],
        ];

        foreach ($areaAssignments as $areaName => $storeNames) {
            $area = Area::firstOrCreate(['name' => trim($areaName)]);

            foreach ($storeNames as $storeName) {
                $store = Store::firstOrCreate(
                    ['name' => trim($storeName)],
                    ['is_active' => true]
                );

                // Updating the relationship is safe: claims continue to reference store_id.
                if ((int) $store->area_id !== (int) $area->id) {
                    $store->update(['area_id' => $area->id]);
                }
            }
        }
    }
}
