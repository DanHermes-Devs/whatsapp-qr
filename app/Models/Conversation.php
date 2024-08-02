<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id', 'name'
    ];

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
