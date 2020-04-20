<?php

namespace App\Models;

use App\Models\QuizTheme;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';
    protected $fillable = ['question', 'answer',' explanation', 'tip', 'alt_answers', 'about_pogo', 'difficulty', 'theme_id'];
    protected $appends = ['theme'];

    public function getThemeAttribute() {
        if( empty($this->theme_id) ) return false;
        return QuizTheme::find($this->theme_id);
    }
}
