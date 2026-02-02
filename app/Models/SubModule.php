<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'order',
    ];

    /**
     * Relacionamento: SubModule pertence a um Module
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Relacionamento: SubModule tem muitas Lessons
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Relacionamento através do Module para Course
     */
    public function course()
    {
        return $this->module->course();
    }
}
