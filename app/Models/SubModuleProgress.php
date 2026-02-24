<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubModuleProgress extends Model
{
    protected $table = 'sub_module_progress';

    protected $fillable = ['user_id', 'sub_module_id', 'completed'];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function subModule()
    {
        return $this->belongsTo(SubModule::class, 'sub_module_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
