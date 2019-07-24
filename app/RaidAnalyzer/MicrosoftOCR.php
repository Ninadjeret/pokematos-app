<?php
namespace App\RaidAnalyzer;

use Illuminate\Support\Facades\Log;

class MicrosoftOCR {

    function __construct() {
        $this->apiKey = '';
        $this->baseUrl = 'https://westeurope.api.cognitive.microsoft.com/vision/v2.0/recognizeText?mode=Printed';
    }

    public function read( $image_url ) {

        return array('4G', '$ 53 4 15:22', 'Echelle de vie', 'Ectoplasma', '0:42:14');

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
        Log::debug( print_r( $result, true) );
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
            Log::debug('___ iteration '.$iteration.' ___');
            sleep(1);
            $ch2 = curl_init($requestURL);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
               'Ocp-Apim-Subscription-Key:'.$this->apiKey
            ));
            $output2 = curl_exec($ch2);
            $result = json_decode($output2);
            Log::debug( print_r( $output2, true) );
            if( $result->status == 'Succeeded' || $iteration === 10 ) {
                $continue = false;
            }
            $iteration++;
        }


        $lines = array();
        foreach( $result->recognitionResult->lines as $line ) {
            if( $line->text == 'Cette Arene est trop loin.' || $line->text == 'X' ) {
                continue;
            }
            if( $line->text == 'COMBAT' ) {
                continue;
            }
            if( $line->text == 'GROUPE PRIVE' ) {
                continue;
            }
            if( $line->text == 'Walk closer to interact with this Gym.' ) {
                continue;
            }
            if(preg_match('/^CP/', $line->text) ) {
                $this->cp_line = $line->text;
                continue;
            }
            if(preg_match('/^[0-9]+$/', $line->text) ) {
                continue;
            }
            $lines[] = $line->text;
        }

        return $lines;
    }

}
