<?php
namespace App\RaidAnalyzer;

use Illuminate\Support\Facades\Log;

class MicrosoftOCR {

    function __construct() {
        $this->apiKey = config('app.microsoft_api_key');
        $this->baseUrl = 'https://westeurope.api.cognitive.microsoft.com/vision/v2.0/recognizeText?mode=Printed';
        $this->cp_line = false;
    }

    public function read( $image_url ) {
        $headers = $this->recognizeText($image_url);
        $requestURL = $this->getResultUrl($headers);
        if( $requestURL ) {
            return $this->getRecognizedText( $requestURL );
        }
    }

    private function recognizeText( $image_url ) {

        //Set varialbes
        $post_data = array(
           "url" => "$image_url"
        );

        //First call to perform Analizis
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data) );
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
           'Content-Type: application/json',
           'Ocp-Apim-Subscription-Key:'.$this->apiKey
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }

    private function getResultUrl( $headers ) {
        $data = explode("\n",$headers);
        if( empty( $data ) ) {
            return false;
        }

        foreach( $data as $header_line ) {
            if( strstr($header_line, 'Operation-Location') ) {
                return trim(str_replace('Operation-Location: ', '', $header_line));
            }
        }

        return false;
    }

    private function getRecognizedText( $requestURL ) {

        $iteration = 0;
        $continue = true;
        while( $continue ) {
            //Log::debug('___ iteration '.$iteration.' ___');
            sleep(1);
            $ch2 = curl_init($requestURL);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
               'Ocp-Apim-Subscription-Key:'.$this->apiKey
            ));
            $output2 = curl_exec($ch2);
            $result = json_decode($output2);
            //Log::debug( print_r( $output2, true) );
            if( $result->status == 'Succeeded' || $iteration === 10 ) {
                $continue = false;
            }
            $iteration++;
        }


        $lines = array();
        $num_ligne = 0;
        $num_ligne_invitation = 0;
        foreach( $result->recognitionResult->lines as $line ) {

            $num_ligne++;

            //exceptions de base
            if( $line->text == 'Cette Arene est trop loin.'
                || $line->text == '>'
                || $line->text == 'O'
                || $line->text == 'X'
                || $line->text == 'COMBAT'
                || $line->text == 'GROUPE PRIVE'
                || $line->text == 'Walk closer to interact with this Gym.'
                || $line->text == '+'
                || $line->text == 'ARENE DE RAID EX'
                || $line->text == 'BATTLE'
                || $line->text == 'PRIVATE GROUP'
                || $line->text == 'PC'
                || $line->text == 'P'
                || $line->text == 'A'
                || $line->text == 'AA'
                || $line->text == 'AAA'
                || $line->text == 'IV'
                || strstr($line->text, 'using a Remote Raid Pass')
                || strstr($line->text, 'Raid a distance')
                || strstr($line->text, 'utilisant un pass')
                || $line->text == 'D'
            ) {
                continue;
            }

            //Exceptions raidex zone géographique
            if( $line->text == 'INVITATION' ) $num_ligne_invitation = $num_ligne;
            if( $num_ligne_invitation > 0 && ($num_ligne_invitation + 3) == $num_ligne ) {
                continue;
            }

            //exceptions RaidEx
            if( $line->text == 'Itineraire'
                || $line->text == 'Itineraire'
                || $line->text == 'Un Excellent ami et toi etes invites a un Raid EX.'
                || $line->text == 'INVITATION'
                || $line->text == 'Il s\'agit d\'une recompense pour ta victoire a'
                || $line->text == 'INVITER'
                || strstr($line->text, 'sur invitation uniquement')
                || strstr($line->text, 'Felicitations,')
                || strstr($line->text, 'Rends-toi a I\'Arene a l\'heure indiquee')
                || strstr($line->text, ' Combat de Raid EX')
            ) {
                continue;
            }
            if(preg_match('/^[0-9]+$/i', $line->text) ) {
                if( strlen($line->text) === 4 || strlen($line->text) === 5 ) {
                    $this->cp_line = $line->text;
                }
                continue;
            }
            if(preg_match('/^(PC|CP|P) [0-9]+$/i', $line->text) ) {
                $line->text = str_replace('PC ', '', $line->text );
                $line->text = str_replace('Pc ', '', $line->text );
                $line->text = str_replace('CP ', '', $line->text );
                $line->text = str_replace('P ', '', $line->text );
                $line->text = str_replace('p ', '', $line->text );
                if( strlen($line->text) === 4 || strlen($line->text) === 5 ) {
                    $this->cp_line = $line->text;
                }
                continue;
            }
            if(preg_match('/^(PC|CP|P)[0-9]+$/i', $line->text) ) {
                $line->text = str_replace('PC', '', $line->text );
                $line->text = str_replace('Pc', '', $line->text );
                $line->text = str_replace('CP', '', $line->text );
                $line->text = str_replace('P', '', $line->text );
                $line->text = str_replace('P', '', $line->text );
                if( strlen($line->text) === 4 || strlen($line->text) === 5 ) {
                    $this->cp_line = $line->text;
                }
                continue;
            }
            $lines[] = $line->text;
        }

        return $lines;
    }

}
