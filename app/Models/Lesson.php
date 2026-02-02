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
        'duration_seconds',
        'order',
        'content_type',
        'quiz_data'
    ];

    protected $casts = [
        'quiz_data' => 'array',
    ];

    /**
     * Relacionamento: Lesson pertence a um SubModule
     */
    public function subModule()
    {
        return $this->belongsTo(SubModule::class);
    }

    /**
     * Compatibilidade com código antigo - acessa SubModule como module
     */
    public function module()
    {
        return $this->subModule;
    }

    /**
     * Relacionamento através do SubModule para Course
     */
    public function course()
    {
        return $this->subModule->module->course();
    }

    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }
}

