<?php

namespace App\Core\Analyzer\Traits;

use App\Core\Discord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Core\Discord\MessageTranslator;

trait Textable
{

  public function initTextable()
  {
    $this->source_text = MessageTranslator::from($this->guild)->translate($this->source_text);
  }
}