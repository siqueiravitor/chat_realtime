<?php
session_start();
if (!isset($_POST['chat'])) {
    header("Location: chats.php");
}
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat em Tempo Real</title>

    <style>
        /* styles.css */

        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .chat-container {
            width: 100%;
            max-width: 600px;
            height: 80vh;
            display: flex;
            flex-direction: column;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .chat-header {
            padding: 20px;
            background-color: #4CAF50;
            color: #fff;
            text-align: center;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }

        .chat-message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            max-width: 60%;
        }

        .message-sent {
            background-color: #DCF8C6;
            align-self: flex-end;
        }

        .message-received {
            background-color: #ECECEC;
            align-self: flex-start;
        }

        .chat-input {
            display: flex;
            padding: 20px;
            border-top: 1px solid #ddd;
        }

        #message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }

        #send-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #send-button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <!-- <form method="GET" onsubmit="enviaNot(event)">
        <input name='msg' id='msg'>
        <button>Enviar Mensagem</button>
    </form>
    <hr>
    <div id="chat"></div> -->

    <div class="chat-container">
        <div class="chat-header">
            <h2>Chat</h2>
        </div>
        <div class="chat-messages" id="chat-messages"></div>
        <form class="chat-input" onsubmit="enviaNot(event)">>
            <input type="text" id="message-input" placeholder="Digite sua mensagem...">
            <button id="send-button">Enviar</button>
        </form>
    </div>

    <script>
        var userId = <?= $_SESSION['id_usuario'] ?>;
        var chatId = <?= $_POST['chat'] ?>;
        var conn = new WebSocket('ws://localhost:8080/chats');

        conn.onopen = function(e) {
            console.log("Conectado ao servidor WebSocket");
            conn.send(JSON.stringify({
                action: 'join_chat',
                userId: userId,
                chatId: chatId
            }));
        };
        conn.onerror = function(e) {
            console.error("WebSocket error:", e);
        };
        conn.onmessage = function(e) {
            var data = JSON.parse(e.data);
            // var mensagens = document.getElementById('chat-messages');
            // mensagens.innerHTML += '<p>' + data.message + '</p>';
            if (data.message) {
                addMessageToChat(data.message, data.sender === userId ? 'message-sent' : 'message-received');
            }
        };
        conn.onclose = function(e) {
            console.log("Conexão WebSocket fechada:", e);
        };

        function sendMessage(message) {
            var payload = JSON.stringify({
                action: 'send_message',
                userId: userId,
                chatId: chatId,
                message: message
            });
            conn.send(payload);
        }

        function enviaNot(e) {
            e.preventDefault();
            var messageInput = document.getElementById('message-input');
            var message = messageInput.value;
            if (message.trim()) {
                sendMessage(message);
                messageInput.value = '';
            }
        }

        function addMessageToChat(message, messageType) {
            var chatMessages = document.getElementById('chat-messages');
            var messageElement = document.createElement('div');
            messageElement.className = 'chat-message ' + messageType;
            messageElement.textContent = message;
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to the bottom
        }
        // window.onload = function() {
        //     fetch('buscaMensagens.php?chatId=' + chatId)
        //         .then(response => response.json())
        //         .then(data => {
        //             console.log(data)
        //             var messages = document.getElementById('chat-messages');
        //             data.forEach(chat => {
        //                 messages.innerHTML += '<p>' + chat.mensagem + ' (' + chat.enviado_em + ' por ' + chat.usuario_nome + ' )</p>';
        //             });
        //         })
        //         .catch(error => console.error('Erro ao buscar mensagens:', error));
        // };
        window.addEventListener('beforeunload', function(event) {
            if (conn.readyState === WebSocket.OPEN) {
                conn.send(JSON.stringify({
                    action: 'leave_chat',
                    userId: userId,
                    chatId: chatId
                }));
            }
        });
    </script>

</body>

</html>