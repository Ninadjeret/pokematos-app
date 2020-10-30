<?php

namespace Tests\Feature\Analyzer;

use Tests\TestCase;
use App\Models\Guild;
use App\Models\Stop;
use Illuminate\Support\Facades\Log;
use App\Core\Analyzer\GymSearch;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GymSearchTest extends TestCase
{

    /** @test */
    public function extract_gym_name_from_ocr_array()
    {
        $ocr = ['Chartres De Bzh Deco', '0:40:42', 'A'];
        $gymSearch = new GymSearch(Guild::find(1));
        $name = $gymSearch->extractGymName($ocr);
        $this->assertEquals('Chartres De Bzh Deco', $name);
    }

    /** @test */
    public function find_gym_with_unique_partial_name()
    {
        $stop = Stop::firstOrCreate(['niantic_name' => 'Chartres De Bzh Deco'], ['name' => 'Chartres De Bzh Deco', 'city_id' => 1, 'lat' => '48.04553', 'lng' => '-1.60334', 'gym' => 1, 'ex' => 1]);
        $stop2 = Stop::firstOrCreate(['niantic_name' => 'Chartres De Biorez'], ['name' => 'Chartres De Biorez', 'city_id' => 1, 'lat' => '48.04553', 'lng' => '-1.60334', 'gym' => 1, 'ex' => 1])->delete();
        $ocr = ['Chartres De', '0:40:42', 'A'];
        $gymSearch = new GymSearch(Guild::find(1));
        $result = $gymSearch->findGym($ocr, 70);
        $this->assertEquals($stop->id, $result->gym->id);
    }

    /** @test */
    public function cant_find_gym_with_non_unique_partial_name()
    {
        $stop = Stop::firstOrCreate(['niantic_name' => 'Chartres De Bzh Deco'], ['name' => 'Chartres De Bzh Deco', 'city_id' => 1, 'lat' => '48.04553', 'lng' => '-1.60334', 'gym' => 1, 'ex' => 1]);
        $stop2 = Stop::firstOrCreate(['niantic_name' => 'Chartres De Biorez'], ['name' => 'Chartres De Biorez', 'city_id' => 1, 'lat' => '48.04553', 'lng' => '-1.60334', 'gym' => 1, 'ex' => 1]);
        $ocr = ['Chartres De', '0:40:42', 'A'];
        $gymSearch = new GymSearch(Guild::find(1));
        $result = $gymSearch->findGym($ocr, 70);
        Log::debug(print_r($result, true));
        $this->assertEquals(false, $result);
    }
}
