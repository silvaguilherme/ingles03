<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnkiDeck extends Model
{
    use HasFactory;

    protected $table = 'anki_decks';

    protected $fillable = [
        'submodule_id',
        'name',
        'description',
        'file_path',
        'total_cards',
    ];

    /**
     * Relacionamento: AnkiDeck pertence a um SubModule
     */
    public function submodule()
    {
        return $this->belongsTo(SubModule::class);
    }

    /**
     * Relacionamento: AnkiDeck tem muitos AnkiCards
     */
    public function cards()
    {
        return $this->hasMany(AnkiCard::class);
    }

    /**
     * Obter o deck do submodulo via relacionamento
     */
    public function module()
    {
        return $this->submodule->module();
    }

    /**
     * Obter o curso via submodulo
     */
    public function course()
    {
        return $this->submodule->course();
    }
}
