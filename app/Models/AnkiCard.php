<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnkiCard extends Model
{
    use HasFactory;

    protected $table = 'anki_cards';

    protected $fillable = [
        'anki_deck_id',
        'front',
        'back',
        'extra',
        'tags',
        'order',
    ];

    /**
     * Relacionamento: AnkiCard pertence a um AnkiDeck
     */
    public function deck()
    {
        return $this->belongsTo(AnkiDeck::class, 'anki_deck_id');
    }

    /**
     * Relacionamento: AnkiCard tem muitos AnkiCardProgress
     */
    public function progress()
    {
        return $this->hasMany(AnkiCardProgress::class, 'anki_card_id');
    }

    /**
     * Obter progresso do usuário atual neste card
     */
    public function userProgress($userId)
    {
        return $this->progress()->where('user_id', $userId)->first();
    }

    /**
     * Obter o submodulo do card
     */
    public function submodule()
    {
        return $this->deck->submodule();
    }
}
