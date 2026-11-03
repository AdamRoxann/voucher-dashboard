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
        // $stores = [
        //     'Merchant Mombee',
        //     'Merchant Trademark',
        //     'Merchant WOF',
        //     'Merchant PPM',
        //     'Merchant BFF',
        //     'Merchant Inacraft',
        //     'Merchant Jacoweek',
        // ];

        // foreach ($stores as $name) {
        //     Store::firstOrCreate(
        //         ['name' => $name],
        //         ['is_active' => true]
        //     );
        // }

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
            'TPH (Tembusan Permata Hijau)' => [
                'IMRECCI',
                'Piknikoclock',
                'Neverbath',
                'Larasca Studio',
                'tapmakingthings',
                "Let's go Eonnie",
                'Buttermooni',
                'Aellie Studio',
                'Loose Goose',
                'la.ideas',
                'Laviencrave',
                'Temali',
                'LITTLE GANGNAM',
                'Runreira',
                'Rukkimoon',
                'Scarbeadycat',
                'pkz__archeeves',
                'Children of Art',
                'Kind of Things',
                'Canain Artelier',
                'casabay',
                'Made by our hand',
                'LOUNA',
                'Roll 4 Youth',
                'Kaladara',
                'Lalaland corner',
                'Bottled by Fused',
                'Mora & Olfi',
                'Wishful Haul',
                'Gidae Studio',
                'RATNA RADESA',
                'Channidraw',
                'Tangkap Seni',
                'NakaShaki',
                'Starstruck Goods',
                'Sparklore',
                'Vanaco',
                'Notte.id',
                'perky paper',
                'RYUDONUT',
                'wouldnotexist',
                'enhyra',
                'Peterpon',
                'Sweet suuunday',
                'SCRIBWELL',
                'Jul in June',
                'Lunna Lavender',
                'Twentys Studio',
                'Asobi',
                'Pajama Plane',
                'Untai Senar',
                'Djoealin',
                'kipowa',
                'Dango Room',
                'naarakid',
                'Sajangneeds',
                'Madewithuu',
                'nalaastudio',
                'Trinketism',
                'Phoebe and Bagel',

                'MMRS',
                'SRIDEVI',
                'ERABELLA',
                'COMMUNITY',
                'COMMUNITY',
                'BONAVIE',
                'FRACAS',
                'LO’RUVA',
                'SCENTPLUS',
                'UNSPOKEN',
                'BARSTEN',
                'NERDIES',
                'MANDJHA',
                'MANDJHA',
                'PUCELLE',
                'FERMO',
                'KARALIZ',
                'READY SET',
                'KALAWANGSA',
                'EVOMI',
                'Ming Ming',
                'Origins Bakery by Madmilk',
                'ORVIA',
                'Korté Chocolate',
                'Sunday Batch',
                'Koultoura Coffee',
                'RUMAH SANGRAI BLOOM',
                'Jack Runners Roastery',
                'Headroom Coffee',
                'Sakara Kopi Bali',
                'MINUM KOPI NUSANTARA',
                'Morning Person coffee roaster',
                'Wombats Coffee',
                'Roti Puter',
                'Lil Soes',
                'Kios Brios',
                'Kopi Kalyan',
                'Hakuji Tearoom',
                'Here For A Friend',
                'RONA ROASTERY',
                'ARC by Ratio Coffee Brewers',
                'ROEMAH KOFFIE',
                'PIGEONHOLE COFFEE',
                'nod.',
                'Cloudline Coffee Roasters',
                'The Matcha Tasting Room',
                'Senda Tea',
                'MATCHAE',
                'TeaAmo Tea House',
                'SIAM SIPS',
                'Ipokane coffee roaster',
                'Anomali Coffee',
                'Sumber baru coffee roastery',
                'Kopi es tak kie',
                'Kawisari coffee',
                'Basalt Bean',
                'Pinara Coffee Roaster',
                'AYAYA HOMECAFE',
                'Pasarteh',
                'Hvala',
                'PÉKO Tea Bar',
                'Mewcha Matcha',
                'FORDO COFFEE LAB YOGYAKARTA',
                'Tomoro coffee',
                "Carpenter's Bakehouse",
                'TITIK TEMU',
                'Lutmar x Dream Dates',
                'Kucing Ekor Dua',
                'Roti Macan',
                'Bakpaonyakoko',
                'Umma Patisserie',
                'Mukjayo',
                'Mukjayo',
                'Bekmie',
                'EL GUAPO',
                'Pempek meraso',
                'Madhouse Matcha',
                'Hey Kuro',
            ],
            // 'The Hall Senayan City (Lantai 8)' => ['Merchant Mombee', 'Merchant Trademark'],
            'The Hall Senayan City (Lantai 8)' => [
                'Takabook, My Kids Collection',
                'Mirugo',
                'Ciakpo',
                'Crystal of The Sea',
                'Porto Kids',
                'Kooka',
                'Dooglee, Tuutuu',
                'Booboo',
                'Pigeon',
                'Paninti (Oonew, Emily, Bear, Sucvent, dll)',
                'Omica',
                'Cocolatte',
                'Pokana',
                'Cotton Mom',
                'Eclipse Hampers',
                'Yummy Bites',
                'Mooimom',
                'Libby Baby',
                'Gea Baby, Sanosan, Oryn',
                'Wetbrush',
                'Mustela, Interlac, Farlin, Motherlove',
                'Brothmaster',
                'Naomichan',
                'Sakumini',
                'Grouu',
                'Moell',
                'Gently',
                'Morinaga, Morigro',
                'Morigro',
                'Pediasure',
                'Ace Kids',
                'Nutrilon',
                'Wyeth S26',
                'Frisian Flag',
                'Baby Happy',
                'Etawalin',
                'Enfagrow',
                'Kodomo',
                'BETTERCLO',
                'CHAMELIA',
                'DAVELLINE',
                'DIORA STORE',
                'FABE.CO',
                'FEMMEWEAR',
                'FIMELO',
                'GRACIAS LABEL',
                'HAICLO',
                'HAVEN BUTIQ',
                'HEEJUPTOGO',
                'HEVAFSID',
                'JEARA LOUNGEWEAR',
                'KALS AND FREY',
                'KIIWII BEE',
                'KS DARA',
                'LAFINIA OFFICIAL',
                'MASA',
                'MEMORIES OF SUMMER',
                'MIDI',
                'MIXGALLERY',
                'MKY CLOTHING',
                'MOUVA',
                'NUKA NUKA',
                'PGE OFFICIAL',
                'RYE STUDIO',
                'SATTKA',
                'SHAYBEE',
                'WEAR LABEL',
            ],
            // 'Lantai 5 Senayan City' => ['Inacraft', 'BFF', 'WOF', 'Erajaya', 'Blilbli', 'Optik Seis'],
        'Lantai 5 Senayan City' => [
            'Astraea leather craft',
            'Oak Leather Gallery',
            'Suardana Silver Bali',
            'Merona Store Pakaian',
            'Tokonaken',
            'Wrekso',
            'Rita Rose Collection',
            'MEDIVIDI',
            'WDrupadi',
            'Batik Luwes Putera Jogja',
            'Ria Atelier by Kepompong',
            'Pepari',
            'Retota',
            'KEEWA',
            'Metaflora',
            'Pisalin',
            'The Kartinis',
            'Sana',
            'Isago',
            'Personal Chemistry',
            'Citizens of The World',
            'Tide Bali',
            'Berly Jewelry',
            'Petit Lulun',
            'Sausan Hijab',
            'Cosmo Colony',
            'Nutana',
            'Tieka Huza',
            'Damakara',
            'Heiji Active Wear',
            'Skin Dewi',
            'Type See',
            'Cleanova',
            'Artolouis',
            'Earthora',
            'Scilla Shoes',
            'GAMMA',
            'Gammara Leather',
            'Bocorocco',
            'SISESA',
            'VOYEJ Leather Goods',
            'SEKAIBLUE.CO',
            'ABRASI',
            'Life Behind Bars',
            'Zyn Goods',
            'WOODCUTTER',
            'Carnivores Soul',
            'Earthy Genova',
            'Sage Denim',
            'Pendi Jeans co',
        ],
            'LG Senayan City' => ['Dwidayatour', 'Antavaya', 'Panorama JTB'],
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
