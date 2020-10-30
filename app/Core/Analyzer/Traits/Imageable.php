<?php

namespace App\Core\Analyzer\Traits;

use Illuminate\Support\Str;
use App\Core\Analyzer\ColorPicker;
use App\Core\Analyzer\Coordinates;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait Imageable
{

  public function initImageable()
  {
    $this->colorPicker = new ColorPicker();
    $this->imageData = $this->saveImage($this->source_url);
    if (empty($this->result->error)) {
      $this->coordinates = new Coordinates($this->imageData->width, $this->imageData->height);
      $this->imageData->type = $this->getImageType();
    }
  }

  private function saveImage($source)
  {

    $filename = 'capture-' . time() . '-' . Str::random(20);
    $path = storage_path('app/public/captures/' . $filename . '.jpg');
    $path_ocr = storage_path('app/public/captures/' . $filename . '-ocr.jpg');
    $url = env('APP_URL') . Storage::url('captures/' . $filename . '.jpg');
    $url_ocr = env('APP_URL') . Storage::url('captures/' . $filename . '-ocr.jpg');

    //Create Img from file
    $mimetype = exif_imagetype($source);
    if ($mimetype == 2) {
      if ($this->debug) $this->_log('Img extension : JPG');
      $image = imagecreatefromjpeg($source);
    } elseif ($mimetype == 3) {
      if ($this->debug) $this->_log('Img extension : PNG');
      $image = imagecreatefrompng($source);
    } else {
      $this->result->error = 'Format de de fichier non accepté';
      return false;
    }

    //If image has android bar
    $firtPixel = $this->getFirstPixel($image);
    $lastPixel = $this->getLastPixel($image);
    if ($this->debug) $this->_log('First pixel : ' . $firtPixel);
    if ($this->debug) $this->_log('Last pixel : ' . $lastPixel);
    if ($firtPixel > 1 || $lastPixel < (imagesy($image) - 1)) {
      if ($this->debug) $this->_log('Image has android bar. Crop to get needed size');
      $image = $this->cropImage($image, $firtPixel + 1, $lastPixel - 1, 0, $source);
    }

    imagejpeg($image, $path);

    $image_ocr = imagecreatefromjpeg($path);
    $lastPixel = $this->getLastPixel($image_ocr);
    $image_ocr = $this->cropImage($image_ocr, $lastPixel * 0.04, $lastPixel, imagesx($image) * $this->crop_width_ratio, $source);
    imagejpeg($image_ocr, $path_ocr);

    //Return data
    $imageData = (object) array(
      'source'   => $source,
      'filename'  => $filename,
      'path'  => $path,
      'patch_ocr' => $path_ocr,
      'url'   => $url,
      'url_ocr' => $url_ocr,
      'width' => imagesx($image),
      'height' => imagesy($image),
      'width_ocr' => imagesx($image_ocr),
      'height_ocr' => imagesy($image_ocr),
    );

    $ratio = $imageData->width / $imageData->height;
    if ($this->debug) $this->_log('Img ratio : ' . $ratio);

    imagedestroy($image_ocr);
    imagedestroy($image);
    return $imageData;
  }

  /**
   *
   * @param type $image
   * @return int
   */
  private function getFirstPixel($image)
  {
    $height = imagesy($image);
    $partage_decran = false;
    for ($y = 0; $y < $height; $y += 1) {

      //Get the color of the pixel
      $rgb = imagecolorsforindex($image, imagecolorat($image, 2, $y));
      // get the closest color from palette
      if ($rgb['red'] < 3 && $rgb['blue'] < 3 & $rgb['green'] < 3) {
        continue;
      }
      if ($rgb['red'] == 36 && $rgb['green'] == 132 & $rgb['blue'] == 232) {
        $partage_decran = true;
        continue;
      }
      return ($partage_decran) ? $y / 2 : $y;
    }

    return 0;
  }


  /**
   *
   * @param type $image
   * @return int
   */
  private function getLastPixel($image)
  {
    $height = imagesy($image);
    for ($y = $height; $y > 0; $y -= 1) {

      //Get the color of the pixel
      $rgb = imagecolorsforindex($image, imagecolorat($image, 2, $y - 1));
      // get the closest color from palette
      if ($rgb['red'] == 0 && $rgb['blue'] == 0 & $rgb['green'] == 0) {
        continue;
      }
      if ($rgb['red'] == 36 && $rgb['green'] == 132 & $rgb['blue'] == 232) {
        continue;
      }
      if ($rgb['red'] > 230 && $rgb['red'] == $rgb['blue'] && $rgb['red'] == $rgb['green']) {
        continue;
      }
      if ($rgb['red'] ==  $rgb['blue'] && $rgb['red'] == $rgb['green']) {
        continue;
      }
      return ($y > 0) ? $y : 1;
    }

    return 1;
  }

  /**
   *
   * @param type $image
   * @param type $firstPixel
   * @param type $lastPixel
   * @return type
   */
  private function cropImage($image, $firstPixel, $lastPixel, $left = 0, $source)
  {
    Log::debug($source);
    Log::debug($firstPixel);
    Log::debug($lastPixel);
    $image2 = imagecrop($image, ['x' => $left, 'y' => $firstPixel, 'width' => imagesx($image) - $left, 'height' => $lastPixel - $firstPixel]);
    if ($image2 !== FALSE) {
      imagedestroy($image);
      return $image2;
    }
  }
}