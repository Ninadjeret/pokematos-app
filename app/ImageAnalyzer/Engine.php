<?php

namespace App\ImageAnalyzer;

use thiagoalessio\TesseractOCR\TesseractOCR;

class Engine {

    function __construct() {

        $this->debug = true;

        $this->result = (object) array(
            'gym' => false,
            'eggLevel' => false,
            'pokemon'   => false,
            'date' => false,
            'error' => false,
            'logs' => '',
        );
    }

    public function run() {
        $ocr = new TesseractOCR();
        $ocr->executable('C:\Program Files\Tesseract-OCR');
        $ocr->image('test.jpg');
        return $ocr->run();
        return 'tttt';
        //return $ocr->version();
    }

}
