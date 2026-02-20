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
     * Atualizar progresso baseado na qualidade da resposta (Algoritmo SM-2)
     * Quality: 0 = Fail, 1 = Hard, 2 = Ok, 3 = Easy
     */
    public function recordReview(int $quality)
    {
        $this->last_reviewed = now();
        $this->repetitions++;

        // Algoritmo SM-2 do Anki
        // EF' := EF + (0.1 - (5 - q) * (0.08 + (5 - q) * 0.02))
        $newEF = $this->ease_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $this->ease_factor = max(1.3, $newEF); // Mínimo de 1.3

        if ($quality < 2) { // Errou ou achou difícil
            $this->lapses++;
            $this->repetitions = 1;
            $this->interval = 1;
            $this->status = 'learning';
            $this->next_review = now()->addMinutes(10); // Revisar em 10 minutos
        } else {
            // Usuário acertou
            if ($this->repetitions === 1) {
                $this->interval = 1;
            } else if ($this->repetitions === 2) {
                $this->interval = 3;
            } else {
                // Formula: I(n) := I(n-1) * EF
                $this->interval = round($this->interval * $this->ease_factor);
            }

            $this->status = 'review';
            $this->next_review = now()->addDays($this->interval);
        }

        $this->save();
    }

    /**
     * Atualizar progresso após responder corretamente (legado)
     */
    public function reviewCorrect()
    {
        $this->recordReview(3); // Fácil
    }

    /**
     * Atualizar progresso após responder incorretamente (legado)
     */
    public function reviewIncorrect()
    {
        $this->recordReview(0); // Errou
    }
}
