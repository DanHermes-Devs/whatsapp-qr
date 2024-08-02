import express from 'express';
import { Server } from 'socket.io';
import qrcode from 'qrcode';
import cors from 'cors';
import axios from 'axios';
import whatsappWebPkg from 'whatsapp-web.js';
const { Client, LocalAuth } = whatsappWebPkg;

const app = express();
const server = app.listen(3000, () => {
    console.log('Server is running on port 3000');
});

app.use(cors({ origin: 'http://whatsapp-qr.test' }));

const io = new Server(server, {
    cors: {
        origin: "http://whatsapp-qr.test",
        methods: ["GET", "POST"]
    }
});

const client = new Client({
    authStrategy: new LocalAuth({ clientId: 'client-one' })
});

client.on('qr', qr => {
    qrcode.toDataURL(qr, (err, url) => {
        if (err) {
            console.error('Error generating QR code:', err);
            return;
        }
        io.emit('qr', url);
    });
});

client.on('authenticated', () => {
    console.log('AUTHENTICATED');
});

client.on('ready', () => {
    console.log('Client is ready!');
    io.emit('ready', 'WhatsApp is ready!');
});

io.on('connection', (socket) => {
    console.log('New client connected');

    socket.on('request-chats', async () => {
        console.log('request-chats received');
        try {
            const chats = await client.getChats();
            console.log('Chats fetched:', chats.length);
            const chatsWithMessages = await Promise.all(chats.map(async (chat) => {
                try {
                    const messages = await chat.fetchMessages({ limit: 10 });
                    console.log(`Messages fetched for chat ${chat.id._serialized}:`, messages.length);
                    return {
                        id: chat.id._serialized,
                        name: chat.name || chat.id.user,
                        messages: messages.map(message => ({
                            fromMe: message.fromMe,
                            body: message.body,
                            timestamp: message.timestamp,
                            senderName: message.sender && message.sender.pushname ? message.sender.pushname : "Unknown"
                        }))
                    };
                } catch (err) {
                    console.error(`Error fetching messages for chat ${chat.id._serialized}:`, err);
                    return null;
                }
            }));

            const validChats = chatsWithMessages.filter(chat => chat !== null);
            console.log('Sending valid chats to client:', JSON.stringify(validChats, null, 2));

            await axios.post('http://whatsapp-qr.test/save-chats', { chats: validChats });

            socket.emit('chats', validChats);
        } catch (error) {
            console.error('Error getting chats:', error);
            socket.emit('error', 'Error getting chats');
        }
    });

    socket.on('send-message', async (data) => {
        console.log('send-message received:', data);
        try {
            const chat = await client.getChatById(data.chatId);
            const message = await chat.sendMessage(data.message);
            console.log('Message sent:', message);
            socket.emit('message-sent', { chatId: data.chatId, message: data.message });
        } catch (error) {
            console.error('Error sending message:', error);
            socket.emit('error', 'Error sending message');
        }
    });
});

client.on('auth_failure', (msg) => {
    console.error('AUTHENTICATION FAILURE', msg);
});

client.initialize();
