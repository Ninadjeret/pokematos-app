<?php

namespace App\Core\Tools;

use \Intervention\Image\Facades\Image;

class PokemonImagify
{

  function __construct($path)
  {
    $this->im = imagecreatefrompng($path);
    $this->width = imagesx($this->im);
    $this->height = imagesy($this->im);
  }

  public function save($path)
  {
    imagepng($this->im, $path);
    imagedestroy($this->im);
  }

  public function cropTransparentBg()
  {
    $first_x = false;
    for ($x = 0; $x < $this->width; $x++) {
      for ($y = 0; $y < $this->height; $y++) {
        $rgb = imagecolorat($this->im, $x, $y);
        $colors = imagecolorsforindex($this->im, $rgb);
        if (!$first_x && $colors['alpha'] === 0) $first_x = $x;
      }
    }

    $first_y = false;
    for ($y = 0; $y < $this->height; $y++) {
      for ($x = 0; $x < $this->width; $x++) {
        $rgb = imagecolorat($this->im, $x, $y);
        $colors = imagecolorsforindex($this->im, $rgb);
        if (!$first_y && $colors['alpha'] === 0) $first_y = $y;
      }
    }

    $last_x = false;
    for ($x = $this->width - 1; $x > 0; $x--) {
      for ($y = $this->height - 1; $y > 0; $y--) {
        $rgb = imagecolorat($this->im, $x, $y);
        $colors = imagecolorsforindex($this->im, $rgb);
        if (!$last_x && $colors['alpha'] === 0) $last_x = $x;
      }
    }

    $last_y = false;
    for ($y = $this->height - 1; $y > 0; $y--) {
      for ($x = $this->width - 1; $x > 0; $x--) {
        $rgb = imagecolorat($this->im, $x, $y);
        $colors = imagecolorsforindex($this->im, $rgb);
        if (!$last_y && $colors['alpha'] === 0) $last_y = $y;
      }
    }

    $width = ($last_x - $first_x > 0) ? $last_x - $first_x : 1;
    $height = ($last_y - $first_y > 0) ? $last_y - $first_y : 1;

    $this->im = imagecrop($this->im, ['x' => $first_x, 'y' => $first_y, 'width' => $width, 'height' => $height]);
    $this->im = $this->resizeImage($this->im, $width, $height);
  }

  public function resizeImage($image, int $newWidth, int $newHeight)
  {
    if ($newWidth > 139) {
      $newHeight = $newHeight * 139 / $newWidth;
      $newWidth = 139;
    }
    if ($newHeight > 92) {
      $newWidth = $newWidth * 92 / $newHeight;
      $newHeight = 92;
    }
    $newImg = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($newImg, false);
    imagesavealpha($newImg, true);
    $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
    imagefilledrectangle($newImg, 0, 0, $newWidth, $newHeight, $transparent);
    $src_w = imagesx($image);
    $src_h = imagesy($image);
    imagecopyresampled($newImg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $src_w, $src_h);
    return $newImg;
  }

  public function createRaidThumbnail($path, $save)
  {
    $img = Image::make(storage_path() . '/app/pokemon/map_marker_pokemon.png');
    $img->insert($path, 'bottom', 0, 52);
    $img->save($save);
  }

  public function createQuestThumbnail($path, $save)
  {
    $img = Image::make(storage_path() . '/app/pokemon/map_marker_quest_pokemon.png');
    $img->insert($path, 'bottom', 0, 52);
    $img->save($save);
  }

  public function createEnergyThumbnail($path, $save)
  {
    $img = Image::make(storage_path() . '/app/pokemon/mega_energy.png');

    $pkmn = Image::make($path);
    $pkmn->resize(null, 120, function ($constraint) {
        $constraint->aspectRatio();
    });

    $img->insert($pkmn, 'bottom-right', 0, 0);
    $img->save($save);
  }

  public function createEnergyQuestThumbnail($path, $save)
  {
    $img = Image::make(storage_path() . '/app/pokemon/map_marker_quest_energy.png');

    $pkmn = Image::make($path);
    $pkmn->resize(null, 60, function ($constraint) {
        $constraint->aspectRatio();
    });
    
    $img->insert($pkmn, 'bottom-right', 0, 52);
    $img->save($save);
  }
}