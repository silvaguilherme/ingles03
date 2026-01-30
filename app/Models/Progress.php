<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{

protected $table = 'progress';
    protected $fillable = ['user_id','lesson_id','watched_seconds','percentage','completed'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

}
