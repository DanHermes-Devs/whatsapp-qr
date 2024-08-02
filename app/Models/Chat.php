<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id', 'from_me', 'body', 'timestamp', 'sender_name'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
