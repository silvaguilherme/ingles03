<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{

protected $fillable = ['module_id','title','video_key','pdf_key','duration_seconds','order'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

}
