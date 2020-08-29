<?php

namespace Tests\Feature\Analyzer\Image;

use Tests\TestCase;
use App\Models\Guild;
use Illuminate\Support\Facades\Log;
use App\Core\RaidAnalyzer\ImageAnalyzer;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RaidTest extends TestCase
{

    /** @test */
    public function saving_image_set_correct_cropped_image()
    {
        $path = 'tests/analyzer/raid/crop';
        $images = array_diff(scandir(storage_path($path)), array('..', '.'));
        foreach ($images as $image) {
            $complete_path = storage_path($path) . '/' . $image;
            $expected = explode('---', explode('.', $image)[0]);
            $analyzer = new ImageAnalyzer($complete_path, Guild::find(1));
            $this->assertEquals($expected[1], $analyzer->imageData->height, "Value of {$expected[0]} must be {$expected[1]}");
            $this->assertEquals($expected[2], $analyzer->imageData->height_ocr, "Value of {$expected[0]} must be {$expected[2]}");
        }
    }

    /** @test */
    public function detect_type_egg_from_image()
    {
        $path = 'tests/analyzer/raid/egg';
        $images = array_diff(scandir(storage_path($path)), array('..', '.'));
        foreach ($images as $image) {
            $complete_path = storage_path($path) . '/' . $image;
            $expected = explode('---', explode('.', $image)[0]);
            $analyzer = new ImageAnalyzer($complete_path, Guild::find(1));
            if ($expected[2] === 'yes') {
                $this->assertEquals('egg', $analyzer->imageData->type, "Error for {$image}");
            } else {
                $this->assertEquals(false, $analyzer->imageData->type, "Error for {$image}");
            }
        }
    }

    /** @test */
    public function detect_egg_level_from_image()
    {
        $path = 'tests/analyzer/raid/egg';
        $images = array_diff(scandir(storage_path($path)), array('..', '.'));
        foreach ($images as $image) {
            $complete_path = storage_path($path) . '/' . $image;
            $expected = explode('---', explode('.', $image)[0]);
            $analyzer = new ImageAnalyzer($complete_path, Guild::find(1));
            $egg_level = $analyzer->getEggLevel();
            Log::debug("{$expected[1]} ::> {$egg_level}");
            $this->assertEquals($expected[1], $egg_level, "Error for {$image}");
        }
    }

    /** @test */
    public function detect_egg_level_from_image_v2()
    {
        $path = 'tests/analyzer/raid/eggv2';
        $images = array_diff(scandir(storage_path($path)), array('..', '.'));
        foreach ($images as $image) {
            $complete_path = storage_path($path) . '/' . $image;
            $expected = explode('---', explode('.', $image)[0]);
            $analyzer = new ImageAnalyzer($complete_path, Guild::find(1));
            $egg_level = $analyzer->getEggLevelv2();
            Log::debug("{$expected[1]} ::> {$egg_level}");
            $this->assertEquals($expected[1], $egg_level, "Error for {$image}");
        }
    }

    /** @test */
    public function detect_type_pokemon_from_image()
    {
        $path = 'tests/analyzer/raid/pokemon';
        $images = array_diff(scandir(storage_path($path)), array('..', '.'));
        foreach ($images as $image) {
            $complete_path = storage_path($path) . '/' . $image;
            $analyzer = new ImageAnalyzer($complete_path, Guild::find(1));
            $this->assertEquals('pokemon', $analyzer->imageData->type, "Error for {$image}");
        }
    }
}