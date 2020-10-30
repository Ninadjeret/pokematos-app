<?php

namespace App\Core\Analyzer;

use Illuminate\Support\Facades\Log;

class ColorPicker
{

    function __construct()
    {
        $this->debug = true;
    }

    private function _log($text)
    {
        if (is_array($text)) {
            Log::channel('raids')->info(print_r($text, true));
        } else {
            Log::channel('raids')->info($text);
        }
    }

    public function pickColor($image, $x, $y)
    {
        $rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));
        if (!isset($_GET['debugludo'])) :
            if ($this->debug) $this->_log('Test pixel at x:' . $x . ' & y:' . $y);
            if ($this->debug) $this->_log('Result : R:' . $rgb['red'] . ' G:' . $rgb['green'] . ' B:' . $rgb['blue']);
        endif;
        return $rgb;
    }

    public function isFutureTimerColor($rgb)
    {
        if (
            ($rgb['red'] >= 230 && $rgb['red'] <= 255) &&
            ($rgb['green'] >= 130 && $rgb['green'] <= 145) &&
            ($rgb['blue'] >= 144 && $rgb['blue'] <= 160)
        ) {
            return true;
        }
        return false;
    }

    public function isActiveTimerColor($rgb)
    {
        if (
            ($rgb['red'] >= 220 && $rgb['red'] <= 256) &&
            ($rgb['green'] >= 110 && $rgb['green'] <= 130) &&
            ($rgb['blue'] >= 40 && $rgb['blue'] <= 70)
        ) {
            return true;
        }

        if (
            ($rgb['red'] >= 220 && $rgb['red'] <= 255) &&
            ($rgb['green'] >= 100 && $rgb['green'] <= 135) &&
            ($rgb['blue'] >= 40 && $rgb['blue'] <= 90)
        ) {
            return true;
        }

        return false;
    }

    public function isEgglevelColor($rgb)
    {
        if ($rgb['red'] > 233 && $rgb['green'] > 233 && $rgb['blue'] > 233) {
            return true;
        }
        return false;
    }

    public function isExBackground($rgb)
    {
        if (
            ($rgb['red'] >= 0 && $rgb['red'] <= 10) &&
            ($rgb['green'] >= 40 && $rgb['green'] <= 50) &&
            ($rgb['blue'] >= 70 && $rgb['blue'] <= 80)
        ) {
            return true;
        }

        return false;
    }
}
