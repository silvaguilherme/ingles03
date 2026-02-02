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
}

