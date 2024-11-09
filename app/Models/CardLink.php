<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'logo',
        'card_id'
    ];

    /**
     * Get the card that this item belongs to.
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
