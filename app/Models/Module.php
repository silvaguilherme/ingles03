<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['course_id', 'title', 'order'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relacionamento: Module tem muitos SubModules
     */
    public function subModules()
    {
        return $this->hasMany(SubModule::class)->orderBy('order');
    }

    /**
     * Relacionamento: Module tem muitas Lessons (através de SubModules)
     * Para compatibilidade com código antigo
     */
    public function lessons()
    {
        return $this->hasManyThrough(
            Lesson::class,
            SubModule::class,
            'module_id',      // FK em sub_modules que aponta para modules
            'sub_module_id'   // FK em lessons que aponta para sub_modules
        );
    }
}

