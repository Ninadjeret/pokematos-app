<?php
use Illuminate\Database\Seeder;
class PokemonsTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // check if table users is empty
        if(DB::table('cities')->get()->count() == 0){
            DB::table('cities')->insert([
                [
                    'name' => 'Rennes',
                    'slug' => 'rennes',
                    'lat' => 48.045741,
                    'lng' => -1.6082411,
                ],
                [
                    'name' => 'Dijon',
                    'slug' => 'dijon',
                    'lat' => 47.331881,
                    'lng' => 5.032221,
                ]
            ]);
        } // end check if table users is empty

        if(DB::table('guilds')->get()->count() == 0){
            DB::table('guilds')->insert([
                [
                    'discord_id' => '377559922214305792',
                    'name'  => 'Pokématos',
                    'type'  => 'discord',
                    'city_id'  => 1,
                    'active' => 1
                ],
                /*[
                    'discord_id' => '606583247635349516',
                    'name'  => 'Bac à sable 2',
                    'type'  => 'discord',
                    'city_id'  => 2,
                    'active' => 1
                ],*/
            ]);
        } // end check if table users is empty

        if(DB::table('zones')->get()->count() == 0){
            DB::table('zones')->insert([
                [
                    'name'  => 'Vern',
                    'city_id'  => 1,
                ],
                [
                    'name'  => 'Centre ville',
                    'city_id'  => 2,
                ],
            ]);
        } // end check if table users is empty

        if(DB::table('quest_rewards')->get()->count() == 0){
            DB::table('quest_rewards')->insert([
                [
                    'name'  => '1 super bonbon',
                    'type'  => 'candy',
                ],
                [
                    'name'  => '3 super bonbons',
                    'type'  => 'candy',
                ],
                [
                    'name'  => '5 super bonbons',
                    'type'  => 'candy',
                ],
                [
                    'name'  => '200 poussières étoile',
                    'type'  => 'stardust',
                ],
                [
                    'name'  => '500 poussières étoile',
                    'type'  => 'stardust',
                ],
                [
                    'name'  => '1000 poussières étoile',
                    'type'  => 'stardust',
                ],
                [
                    'name'  => '3 Pokéballs',
                    'type'  => 'pokeball',
                ],
                [
                    'name'  => '5 Pokéballs',
                    'type'  => 'pokeball',
                ],
                [
                    'name'  => '10 Pokéballs',
                    'type'  => 'pokeball',
                ],
                [
                    'name'  => '2 Superballs',
                    'type'  => 'superball',
                ],
            ]);
        } // end check if table users is empty

        $arenes_vern = file_get_contents('https://www.profchen.fr/api/v1/gyms?token=AsdxZRqPkrst67utwHVM2w4rt4HjxGNcX8XVJDryMtffBFZk3VGM47HkvnF9');
        $arenes_vern = json_decode($arenes_vern);
        $arenes_dijon = file_get_contents('https://dijon.profchen.fr/api/v1/gyms?token=AsdxZRqPkrst67utwHVM2w4rt4HjxGNcX8XVJDryMtffBFZk3VGM47HkvnF9');
        $arenes_dijon = json_decode($arenes_dijon);
        if(DB::table('stops')->get()->count() == 0){
            foreach( $arenes_vern as $arene ) {
                error_log('Import de '.$arene->nameFr);
                DB::table('stops')->insert([
                    [
                        'niantic_name'  => $arene->nianticId,
                        'name' => $arene->nameFr,
                        'lat' => $arene->GPSCoordinates->lat,
                        'lng' => $arene->GPSCoordinates->lng,
                        'ex' => $arene->raidEx,
                        'gym' => 1,
                        'city_id' => 1,
                        'zone_id' => 1,
                    ],
                ]);
            }
            foreach( $arenes_dijon as $arene ) {
                error_log('Import de '.$arene->nameFr);
                DB::table('stops')->insert([
                    [
                        'niantic_name'  => $arene->nianticId,
                        'name' => $arene->nameFr,
                        'lat' => $arene->GPSCoordinates->lat,
                        'lng' => $arene->GPSCoordinates->lng,
                        'ex' => $arene->raidEx,
                        'gym' => 1,
                        'city_id' => 2,
                        'zone_id' => 2,
                    ],
                ]);
            }
        } // end check if table users is empty


        $game_master = file_get_contents('https://raw.githubusercontent.com/pokemongo-dev-contrib/pokemongo-game-master/master/versions/latest/GAME_MASTER.json');
        $game_master = json_decode($game_master);

        $names_fr = file_get_contents('https://raw.githubusercontent.com/sindresorhus/pokemon/master/data/fr.json');
        $names_fr = json_decode($names_fr, true);
        if(DB::table('pokemons')->get()->count() == 0){
            foreach( $game_master as $game_master_2 ) {
                if( is_array($game_master_2) ) { foreach( $game_master_2 as $node ) {
                if( !isset($node->pokemonSettings) || empty($node->pokemonSettings) ) continue;
                if( strstr($node->templateId, '_NORMAL') ) continue;
                if( strstr($node->templateId, '_PURIFIED') ) continue;
                if( strstr($node->templateId, '_SHADOW') ) continue;

                $pokedex_id = substr($node->templateId, 2, 3);
                $name_ocr = ( isset($names_fr[(int)$pokedex_id]) ) ? $names_fr[(int)$pokedex_id - 1] : null;
                $form_id = ( isset($node->pokemonSettings->form) ) ? $node->pokemonSettings->form : '00';

                $forms = [
                    'ALOLA' => 'd\'Alola',
                    'SPEED' => 'Vitesse',
                    'ATTACK' => 'Attaque',
                    'DEFENSE' => 'Défense',
                    'PLANT' => 'Plante',
                    'SANDY' => 'Sable',
                    'TRASH' => 'Déchet',
                    'RAINY' => 'Pluie',
                    'SNOWY' => 'Neige',
                    'SUNNY' => 'Soleil',
                    'OVERCAST' => 'Couvert',
                ];

                $name_fr = $name_ocr;
                if( !empty( $form_id ) && $form_id != '00' ) {
                    foreach( $forms as $form => $label ) {
                        if( strstr($node->templateId, $form) ) {
                            $name_fr = $name_ocr.' '.$label;
                        }
                    }
                }

                error_log('Import de '.$node->pokemonSettings->pokemonId);
                DB::table('pokemons')->insert([
                    [
                        'pokedex_id' => $pokedex_id,
                        'niantic_id'  => $node->templateId,
                        'name_fr'   => $name_fr,
                        'name_ocr'   => $name_ocr,
                        'form_id'  => $form_id,
                        'base_att'  => $node->pokemonSettings->stats->baseAttack,
                        'base_def'  => $node->pokemonSettings->stats->baseDefense,
                        'base_sta'  => $node->pokemonSettings->stats->baseStamina,
                    ],
                ]);
            }}}
        } // end check if table users is empty


    }
}
