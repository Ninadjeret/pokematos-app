<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizTheme extends Model
{
    protected $table = 'quiz_themes';
    protected $fillable = ['name'];
}
