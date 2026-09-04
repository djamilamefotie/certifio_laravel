<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'article_ref',
        'montant',
        'devise',
        'telephone',
        'operateur',
        'statut',
        'transaction_id_monetbil',
        'payment_url',
        'donnees_webhook',
    ];

    protected $casts = [
        'donnees_webhook' => 'array',
        'montant' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estReussi(): bool
    {
        return $this->statut === 'reussi';
    }
}