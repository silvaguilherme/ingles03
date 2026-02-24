<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'sub_module_id',
        'title',
        'sub_title',
        'video_key',
        'pdf_key',
        'audio_key',
        'audio_list',
        'duration_seconds',
        'order',
        'content_type',
        'quiz_data'
    ];

    protected $casts = [
        'quiz_data' => 'array',
        'audio_list' => 'array',
    ];

    /**
     * Relacionamento: Lesson pertence a um SubModule
     */
    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }

    /**
     * Relacionamento através do SubModule para Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class, null, null)->through('subModule');
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }
}

