<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnkiCardProgress extends Model
{
    use HasFactory;

    protected $table = 'anki_card_progress';

    protected $fillable = [
        'user_id',
        'anki_card_id',
        'interval',
        'ease_factor',
        'repetitions',
        'lapses',
        'next_review',
        'last_reviewed',
        'status',
    ];

    protected $casts = [
        'next_review' => 'datetime',
        'last_reviewed' => 'datetime',
    ];

    /**
     * Relacionamento: AnkiCardProgress pertence a um User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento: AnkiCardProgress pertence a um AnkiCard
     */
    public function card()
    {
        return $this->belongsTo(AnkiCard::class, 'anki_card_id');
    }

    /**
     * Verificar se o card está pronto para revisão
     */
    public function isReadyForReview()
    {
        if ($this->status === 'new' || $this->status === 'learning') {
            return true;
        }

        if ($this->next_review && now()->greaterThanOrEqualTo($this->next_review)) {
            return true;
        }

        return false;
    }

    /**
     * Atualizar progresso após responder corretamente
     */
    public function reviewCorrect()
    {
        $this->repetitions++;
        $this->ease_factor = max(1.3, $this->ease_factor + (0.1 - (5 - 5) * (0.08 + (5 - 5) * 0.02)));

        // Calcular novo intervalo
        if ($this->repetitions === 1) {
            $this->interval = 1;
        } else if ($this->repetitions === 2) {
            $this->interval = 3;
        } else {
            $this->interval = round($this->interval * $this->ease_factor);
        }

        $this->next_review = now()->addDays($this->interval);
        $this->last_reviewed = now();
        $this->status = 'review';
        $this->save();
    }

    /**
     * Atualizar progresso após responder incorretamente
     */
    public function reviewIncorrect()
    {
        $this->lapses++;
        $this->ease_factor = max(1.3, $this->ease_factor - 0.2);
        $this->repetitions = 0;
        $this->interval = 1;
        $this->next_review = now()->addMinutes(10);
        $this->last_reviewed = now();
        $this->status = 'learning';
        $this->save();
    }
}
