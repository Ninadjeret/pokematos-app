<?php

namespace App\Models;

use App\Models\QuizTheme;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';
    protected $fillable = ['question', 'answer', 'explanation', 'tip', 'alt_answers', 'about_pogo', 'difficulty', 'theme_id'];
    protected $appends = ['theme'];
    protected $casts = [
        'alt_answers' => 'array',
        'about_pogo' => 'boolean',
    ];

    public function getAltAnswersAttribute($value)
    {
        if (empty($value)) return [];
        return json_decode($value);
    }

    public function getThemeAttribute()
    {
        if (empty($this->theme_id)) return false;
        return QuizTheme::find($this->theme_id);
    }
}