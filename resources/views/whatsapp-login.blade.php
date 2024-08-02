<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Login</title>
    <style>
        #chats {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .chat strong {
            display: block;
            margin-bottom: 5px;
        }
        .chat ul {
            list-style: none;
            padding: 0;
        }
        .chat li {
            margin-bottom: 5px;
        }
        #conversation {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
        #message-input {
            width: calc(100% - 22px); /* Ajusta según el padding del contenedor */
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        #send-button {
            margin-top: 10px;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Scan the QR Code to login to WhatsApp</h1>
    <img id="qrcode" src="" alt="QR Code">
    <h2 id="status"></h2>
    <div id="chats"></div>
    <div id="conversation" style="display: none;">
        <h3>Conversation</h3>
        <ul id="messages-list"></ul>
        <input type="text" id="message-input" placeholder="Type a message">
        <button id="send-button">Send</button>
    </div>
    <script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
    <script>
        var socket = io('http://localhost:3000');
        var currentChatId = null;

        function renderChats(conversations) {
            const chatsContainer = document.getElementById('chats');
            chatsContainer.innerHTML = ''; // Limpiar contenido previo
            conversations.forEach(conversation => {
                const chatElement = document.createElement('div');
                chatElement.classList.add('chat');
                chatElement.innerHTML = `<strong>${conversation.name}</strong>`;
                chatElement.addEventListener('click', () => {
                    console.log('Chat clicked:', conversation);
                    showConversation(conversation);
                });
                chatsContainer.appendChild(chatElement);
            });
        }

        function showConversation(conversation) {
            currentChatId = conversation.id; // Asegúrate de usar el campo correcto aquí
            const conversationContainer = document.getElementById('conversation');
            const messagesList = document.getElementById('messages-list');
            messagesList.innerHTML = ''; // Limpiar mensajes previos

            console.log('Displaying conversation:', conversation);

            if (conversation.messages && Array.isArray(conversation.messages)) {
                conversation.messages.forEach(message => {
                    const messageItem = document.createElement('li');
                    messageItem.innerHTML = `${message.fromMe ? 'Me' : message.senderName}: ${message.body}`;
                    messagesList.appendChild(messageItem);
                });
            } else {
                console.log('No messages found for this conversation.');
            }

            conversationContainer.style.display = 'block';
        }

        document.getElementById('send-button').addEventListener('click', () => {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value;

            if (message && currentChatId) {
                console.log('Sending message:', message);
                socket.emit('send-message', { chatId: currentChatId, message: message });
                messageInput.value = '';
            } else {
                console.log('Message or chat ID is missing');
            }
        });

        socket.on('qr', function (data) {
            document.getElementById('qrcode').src = data;
        });

        socket.on('ready', function (message) {
            document.getElementById('status').innerText = message;
            console.log('WhatsApp is ready. Requesting chats...');
            socket.emit('request-chats');
        });

        socket.on('chats', function (chats) {
            console.log('Chats received:', chats);
            renderChats(chats);
        });

        socket.on('message-sent', function (data) {
            if (data.chatId === currentChatId) {
                const messagesList = document.getElementById('messages-list');
                const messageItem = document.createElement('li');
                messageItem.innerHTML = `Me: ${data.message}`;
                messagesList.appendChild(messageItem);
            }
        });

        socket.on('error', function (errorMessage) {
            document.getElementById('status').innerText = errorMessage;
            console.error('Error from server:', errorMessage);
        });
    </script>
</body>
</html>
