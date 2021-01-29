<?php

namespace Tests\Feature\Analyzer;

use Tests\TestCase;
use App\Models\Pokemon;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\PokemonSearch;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PokemonSearchTest extends TestCase
{
    /** @test */
    public function extract_pokemon_name_from_partial_ocr()
    {
        $ocr = ['Tour Segmuller', 'Ra', 'za', '0:31:34'];
        Pokemon::where('name_fr', 'Rayquaza')->first()->update(['boss' => true, 'boss_level' => 5]);
        $pokemonSearch = new PokemonSearch();
        $pokemon = $pokemonSearch->findPokemon($ocr, null, 70);
        $this->assertEquals('Rayquaza', $pokemon->pokemon->name_fr);
    }
}
