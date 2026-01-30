<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{

protected $fillable = ['module_id','title','sub_title','video_key','pdf_key','duration_seconds','order','content_type','quiz_data'];

protected $casts = [
        'quiz_data' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

}
