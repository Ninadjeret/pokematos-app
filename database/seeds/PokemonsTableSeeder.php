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
        if(DB::table('users')->get()->count() == 0){
            DB::table('users')->insert([
                [
                    'name' => 'florian',
                    'email' => 'florian@voyelle.fr',
                    'password' => bcrypt('florian'),
                    'discord_id' => 539079553813839873,
                    'discord_name' => 'Ninadjeret',
                    'guilds'    => '["377440443258109953", "400277491941638147"]',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'name' => 'kisulov',
                    'email' => 'kisulov@kisulov.fr',
                    'password' => bcrypt('kisulov'),
                    'discord_id' => 484022213507547152,
                    'discord_name' => 'Kisulov',
                    'guilds'    => '["377440443258109953"]',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ]);
        } // end check if table users is empty

        // check if table users is empty
        if(DB::table('cities')->get()->count() == 0){
            DB::table('cities')->insert([
                [
                    'name' => 'Rennes',
                    'slug' => 'rennes',
                ],
                [
                    'name' => 'Dijon',
                    'slug' => 'dijon',
                ],
                [
                    'name' => 'Besançon',
                    'slug' => 'besancon',
                ],
                [
                    'name' => 'Thionville',
                    'slug' => 'thionville',
                ]
            ]);
        } // end check if table users is empty

        if(DB::table('guilds')->get()->count() == 0){
            DB::table('guilds')->insert([
                [
                    'guild_id' => '377440443258109953',
                    'name'  => 'Pokemon Go Rennes',
                    'type'  => 'discord',
                    'city_id'  => 1,
                ],
                [
                    'guild_id' => '400277491941638147',
                    'name'  => 'Pokemon GO - Dijon',
                    'type'  => 'discord',
                    'city_id'  => 2,
                ],
            ]);
        } // end check if table users is empty
    }
}
