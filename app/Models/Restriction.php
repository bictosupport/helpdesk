<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasFactory;

    protected $primaryKey = 'restriction_id';

    protected $fillable = [
        'user_id',
        'up_to_date',
        'restriction_message',
        'remarks',
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
