<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function saveChats(Request $request)
    {
        $chats = $request->input('chats');
        foreach ($chats as $chatData) {
            $conversation = Conversation::updateOrCreate(
                ['chat_id' => $chatData['id']],
                ['name' => $chatData['name']]
            );

            foreach ($chatData['messages'] as $messageData) {
                Chat::create([
                    'conversation_id' => $conversation->id,
                    'from_me' => $messageData['fromMe'],
                    'body' => $messageData['body'],
                    'timestamp' => $messageData['timestamp'],
                    'sender_name' => $messageData['senderName']
                ]);
            }
        }
        return response()->json(['message' => 'Chats saved successfully']);
    }

    public function getChats()
    {
        $conversations = Conversation::with('chats')->get();
        return response()->json($conversations);
    }
}
