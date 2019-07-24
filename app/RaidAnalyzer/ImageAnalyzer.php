<?php

namespace App\RaidAnalyzer;

use App\RaidAnalyzer\GymSearch;
use App\RaidAnalyzer\Coordinates;
use App\RaidAnalyzer\ColorPicker;
use App\RaidAnalyzer\MicrosoftOCR;
use App\RaidAnalyzer\PokemonSearch;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ImageAnalyzer {

    function __construct( $source, $guild ) {

        $this->debug = true;

        $this->result = (object) array(
            'gym' => false,
            'eggLevel' => false,
            'pokemon'   => false,
            'date' => false,
            'error' => false,
            'logs' => '',
        );

        $this->start = microtime(true);
        if( $this->debug ) $this->_log('========== Début du traitement '.$source.' ==========');

        $this->gymSearch = new GymSearch($guild);
        $this->pokemonSearch = new PokemonSearch();
        $this->colorPicker = new ColorPicker();
        $this->MicrosoftOCR = new MicrosoftOCR();
        $this->imageData = $this->saveImage( $source );
        $this->coordinates = new Coordinates($this->imageData->width, $this->imageData->height);
        $this->imageData->type = $this->getImageType();

        //Result
        $this->run();
    }

    private function _log( $text, $extra = '' ) {
        if( is_array( $text ) ) {
            Log::debug( print_r($text, true) );
        } else {
            $this->result->logs .= "{$text}\r\n";
            Log::debug( $text );
        }
    }

    public function run() {

         if( $this->imageData->type == 'egg' ) {
             $this->ocr = $this->MicrosoftOCR->read( $this->imageData->url );
             $this->_log($this->ocr);
             $this->result->gym = $this->getGym();
             $this->result->date = $this->getTime();
             $this->result->eggLevel = $this->getEggLevel();
         }

        elseif( $this->imageData->type == 'pokemon' ) {
             $this->ocr = $this->MicrosoftOCR->read( $this->imageData->url );
             $this->_log($this->ocr);
             $this->result->gym = $this->getGym();
             $this->result->date = $this->getTime();
             $this->result->pokemon = $this->getPokemon();
             if( $this->result->pokemon ) {
                 $this->result->eggLevel = $this->result->pokemon->getRaidLevel();
             }
         }

         $time_elapsed_secs = microtime(true) - $this->start;
         if( $this->debug ) $this->_log('========== Fin du traitement '.$this->imageData->source.' ('.round($time_elapsed_secs, 3).'s) ==========');
    }

    /**
     *
     * @param type $souce
     * @return boolean
     */
    private function saveImage( $source ) {

        $filename = 'capture-' . time();
        $path = storage_path('app/public/captures/'.$filename.'.jpg');
        $url = env('APP_URL').Storage::url('captures/'.$filename.'.jpg');

        //Create Img from file
        if( strstr($source, '.jpg') ) {
            if( $this->debug ) $this->_log('Img extension : JPG');
            $image = imagecreatefromjpeg($source);
        } elseif( strstr($source, '.jpeg') ) {
            if( $this->debug ) $this->_log('Img extension : JPEG');
            $image = imagecreatefromjpeg($source);
        } elseif( strstr($source, '.png') ) {
            if( $this->debug ) $this->_log('Img extension : PNG');
            $image = imagecreatefrompng($source);
        } else {
            $this->result->error = 'File type not allowed';
            return false;
        }

        //If image has android bar
        $firtPixel = $this->getFirstPixel($image);
        $lastPixel = $this->getLastPixel($image);
        if( $this->debug ) $this->_log( 'First pixel : ' . $firtPixel );
        if( $this->debug ) $this->_log( 'Last pixel : ' . $lastPixel );
        if( $firtPixel > 1 || $lastPixel < (imagesy($image) - 1) ) {
            if( $this->debug ) $this->_log( 'Image has android bar. Crop to get needed size' );
            $image = $this->cropImage($image, $firtPixel, $lastPixel);
        }

        //Return data
        $imageData = (object) array(
            'source'   => $source,
            'filename'  => $filename,
            'path'  => $path,
            'url'   => $url,
            'width' => imagesx($image),
            'height' => imagesy($image),
        );

        $ratio = $imageData->width / $imageData->height;
        if( $this->debug ) $this->_log('Img ratio : '.$ratio);

        //Destroy temp Img
        imagejpeg($image, $path);
        imagedestroy($image);
        return $imageData;

    }

    /**
     *
     * @param type $image
     * @return int
     */
    private function getFirstPixel( $image ) {
        $height = imagesy($image);
        $partage_decran = false;
        for($y = 0;$y < $height;$y += 1) {

            //Get the color of the pixel
            $rgb = imagecolorsforindex($image, imagecolorat($image, 2, $y));
            // get the closest color from palette
            if( $rgb['red'] < 3 && $rgb['blue'] < 3 & $rgb['green'] < 3 ) {
                continue;
            }
            if( $rgb['red'] == 36 && $rgb['green'] == 132 & $rgb['blue'] == 232 ) {
                $partage_decran = true;
                continue;
            }
            return ( $partage_decran ) ? $y / 2 : $y ;
        }

        return 0;
    }


    /**
     *
     * @param type $image
     * @return int
     */
    private function getLastPixel( $image ) {
        $height = imagesy($image);
        for($y = $height;$y > 0;$y -= 1) {

            //Get the color of the pixel
            $rgb = imagecolorsforindex($image, imagecolorat($image, 2, $y - 1));
            // get the closest color from palette
            if( $rgb['red'] == 0 && $rgb['blue'] == 0 & $rgb['green'] == 0 ) {
                continue;
            }
            if( $rgb['red'] == 36 && $rgb['green'] == 132 & $rgb['blue'] == 232 ) {
                continue;
            }
            if( $rgb['red'] > 230 && $rgb['red'] == $rgb['blue'] && $rgb['red'] == $rgb['green']  ) {
                continue;
            }

            return $y;
        }

        return 0;
    }

    /**
     *
     * @param type $image
     * @param type $firstPixel
     * @param type $lastPixel
     * @return type
     */
    private function cropImage( $image, $firstPixel, $lastPixel ) {
        $image2 = imagecrop($image, ['x' => 0, 'y' => $firstPixel, 'width' => imagesx($image), 'height' => $lastPixel - $firstPixel]);
        if ($image2 !== FALSE) {
            imagedestroy($image);
            return $image2;
        }
    }


    private function getImageType() {

        $image = imagecreatefromjpeg($this->imageData->path);

        //Check for Future Raid
        if( $this->debug ) $this->_log('---------- Check if image is Raid Announce ----------');
        $rgb = $this->colorPicker->pickColor( $image, $this->coordinates->forImgTypeEgg()->x, $this->coordinates->forImgTypeEgg()->y );
        if( $this->colorPicker->isFutureTimerColor( $rgb ) ) {
            if( $this->debug ) $this->_log('Great ! Img seems to include an egg');
            return 'egg';
        }

        //Check for active Raid - v1
        if( $this->debug ) $this->_log('IMG does not seem to be an egg. Trying to check if it includes a pokemon');
        $rgb = $this->colorPicker->pickColor( $image, $this->coordinates->forImgTypePokemon()->x, $this->coordinates->forImgTypePokemon()->y );
        if( $this->colorPicker->isActiveTimerColor($rgb) ) {
            if( $this->debug ) $this->_log('Great ! Img seems to include a pokemon');
            return 'pokemon';
        }

        //else
        $this->result->error = 'Img does not seem to be a raid announce';
        imagedestroy($image);
        return false;
    }

    private function getEggLevel() {
        $egg_level = 0;
        $image = imagecreatefromjpeg($this->imageData->path);

        if( $this->debug ) $this->_log('---------- Egg level Extraction ----------');
		foreach( array(5,4,3,2,1) as $egglevel ) {
			$count_egg_level = 0;
			foreach( $this->coordinates->forEggLevel() as $coor ) {
				if( !in_array($egglevel, $coor->lvl) ) {
					continue;
				}
				$rgb = $this->colorPicker->pickColor($image, $coor->x, $coor->y );
				if( $this->colorPicker->isEgglevelColor($rgb) ) {
					$count_egg_level += 1;
					if( $this->debug ) $this->_log('Pixel matches');
				} else {
					if( $this->debug ) $this->_log('Pixel does not match');
				}
			}

			if( $this->debug ) $this->_log($count_egg_level . ' matching pixels, '.$egglevel.' expected');

			if( $egglevel === $count_egg_level ) {
				imagedestroy($image);
				$egglevel = $egglevel;
				return $egglevel;
			}
		}

        imagedestroy($image);
        return false;
    }

    function getTime() {
        $minutes = false;
        foreach( $this->ocr as $line ) {
            if( preg_match( '/^[0-9]:[0-9][0-9]:[0-9][0-9]/i', $line ) ) {
                $timer = explode(':', $line);
                $minutes = $timer[1];
            }
        }

        if( $minutes ) {
            $date = new \DateTime();
            $date->setTimezone(new \DateTimeZone('Europe/Paris'));
            if( $this->imageData->type == 'egg' ) {
                $date->modify('+'.$minutes.' minutes');
            } else {
                $minutes = 45 - $minutes;
                $date->modify('-'.$minutes.' minutes');
            }
            return $date->format('Y-m-d H:i:s');
        }

        return false;
    }

    function getGym() {

        $query = implode(' ', $this->ocr);
        $gym = $this->gymSearch->findGym($query, 70);
        if( $gym ) {
            if( $this->debug ) $this->_log('Gym finded in database : ' . $gym->name );
            return $gym;
        }
        if( $this->debug ) $this->_log('Nothing found in database :(' );

    }

    function getPokemon() {
        $query = implode(' ', $this->ocr);
        $pokemon = $this->pokemonSearch->findPokemon($query, 70);
        if( $pokemon ) {
            if( $this->debug ) $this->_log('Pokemon finded in database : ' . $pokemon->getNameFr() );
            return $pokemon;
        }

        $ocr_count = count( $this->ocr );
        $start = $this->ocr[ $ocr_count - 3 ];
        $end = $this->ocr[ $ocr_count - 2 ];
        if( strlen( $start ) <= 4 ) {
            $pokemon = $this->pokemonSearch->findPokemonFromFragments(
                    $this->ocr[ $ocr_count - 3 ],
                    $this->ocr[ $ocr_count - 2 ]
                    );
            if( $pokemon ) {
                if( $this->debug ) $this->_log('Pokemon finded in database : ' . $pokemon->getNameFr() );
                return $pokemon);
            }
        }

        if( $this->debug ) $this->_log('Nothing found in database :(' );
        return false;

    }

}
