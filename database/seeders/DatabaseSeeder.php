<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            '33PERCENTACTIVE','ARENA','AURA ACTIVEWEAR','AVGAL','AYRE STUDIO','BEIGE STUDIO',
            'BERLY JEWELRY','BWBYAZ','BWYBAZ','BY ANIND','BYBLANK','BYEOL','CALLALUNA_STUDIO',
            'CTC.LEATHER','DAVELLINE','DONAT BAHAGIA','ESSI','FITYOU','FLOWNLESS','FTR',
            'GOKYO DONBURI','HAICLO','HAIDEE & ORLIN','HERPALAIS','HEXXA','HOUSE OF KAIARA',
            'Hyln studio','HYUN STUDIO','IE.DE','INESCA','INESSA','IT’S PRETTY BASIC','JENNROSE',
            'JENNROSE Clothing','JNE','JOLIE JEWELRY TALE','KAN EYEWEAR','KEAN EYEWEAR','Kold Treats',
            'LADOUCE','LARENA','LORUVA','LOUNA','Luknits','LUKNITS.ID','MAIA','MANDIRI','MARDEEN',
            'Margaux','MARGAUXJAKARTA','MATCHANESAN SERMUPUN','MEMORIES OF SUMMER','MICA','MOM JEANS',
            'MORSE','MYCOLORID','MYKAMI OFFICIAL','NIBBI','NIPPIES','NOIR SUR BLANC','ON LEAVE',
            'ONE SHOOT ONE KILL','PAGE & LANE','PAISLEWITHLOVE','PASTELLY','PASTELLYS','PINELABEL',
            'RALYKA','RICE RICE','RODA PADATI','ROOTEO OFFICIAL','RYE STUDIO','SAIA MODEST',
            'SAKARA KOPI BALI','SANVANINA','SERUMPUN','SHIHIN TAIWAN','SIMPLY JEANS','SIMPLY ROSE',
            'SOEL PROJECT','SOLHÉR','SOOLHA','SOUFLAB','STACK MATES','STREET SNACKS','SWEET BERRY',
            'TGIF PROJECT','THE BEST KAYND','THE LADIES AFFAIR','Thesilversky','WEAR LABEL','WETBRUSH',
            'YE-RIN','YOOWLLD',
        ];

        foreach ($stores as $name) {
            Store::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
