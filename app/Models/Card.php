<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    protected $appends = ['links'];
    protected $fillable = [
        'title_color',
        'background_color',
        'icon_color',
        'share_color',
        'qr_image',
        'image',
        'user_id'
    ];

    /**
     * Get the user that owns the card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items associated with the card.
     */
    public function links()
    {
        return $this->hasMany(CardLink::class);
    }

    public function getLinksAttribute(){
        return $this->links();
    }
}
