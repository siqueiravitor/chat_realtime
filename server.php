<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;
use mysqli;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $userConnections;
    protected $db;

    public function __construct() {
        $this->clients = [];
        $this->userConnections = [];
        // Configuração do banco de dados MySQL
        $this->db = new mysqli('localhost', 'root', '', 'teste');

        if ($this->db->connect_error) {
            die("Conexão falhou: " . $this->db->connect_error);
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients[$conn->resourceId] = $conn;
        echo "Nova conexão! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (!isset($data['action'])) {
            return;
        }

        switch($data['action']){
            case 'join_chat':
                if(isset($data['userId']) && isset($data['chatId'])){
                    $this->userConnections[$data['userId']] = $from->resourceId;
                    $this->joinChat($data['userId'], $data['chatId']);
                }
                break;
            case 'leave_chat':
                if(isset($data['userId']) && isset($data['chatId'])){
                    $this->leaveChat($data['userId'], $data['chatId']);
                }
                break;
            case 'send_message':
                if (isset($data['userId']) && isset($data['chatId']) && isset($data['message'])) {
                    $this->sendMessage($data['userId'], $data['chatId'], $data['message']);
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn){
        unset($this->clients[$conn->resourceId]);

        foreach ($this->userConnections as $userId => $resourceId) {
            if ($resourceId === $conn->resourceId) {
                unset($this->userConnections[$userId]);
                break;
            }
        }

        echo "Conexão {$conn->resourceId} desconectada.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }

    public function joinChat($userId, $chatId){
        $stmt = $this->db->prepare("INSERT INTO chat_participantes (chat_id, usuario_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $chatId, $userId);
        $stmt->execute();
        $stmt->close();

        if(isset($this->clients[$this->userConnections[$userId]])) {
            $this->clients[$this->userConnections[$userId]]->send(json_encode(['message' => 'Você entrou no chat.']));
        }
    }

    public function leaveChat($userId, $chatId){
        $stmt = $this->db->prepare("DELETE FROM chat_participantes WHERE chat_id = ? AND usuario_id = ?");
        $stmt->bind_param('ii', $chatId, $userId);
        $stmt->execute();
        $stmt->close();

        if(isset($this->clients[$this->userConnections[$userId]])) {
            $this->clients[$this->userConnections[$userId]]->send(json_encode(['message' => 'Você saiu do chat.']));
        }
    }

    public function sendMessage($userId, $chatId, $message){
        $stmt = $this->db->prepare("INSERT INTO mensagens (chat_id, usuario_id, mensagem) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $chatId, $userId, $message);
        $stmt->execute();
        $stmt->close();

        // Enviar mensagem para todos os participantes do chat
        $stmt = $this->db->prepare("SELECT usuario_id FROM chat_participantes WHERE chat_id = ?");
        $stmt->bind_param('i', $chatId);
        $stmt->execute();
        $result = $stmt->get_result();

        while($row = $result->fetch_assoc()){
            $participantUserId = $row['usuario_id'];
            if(isset($this->userConnections[$participantUserId]) && isset($this->clients[$this->userConnections[$participantUserId]])){
                $this->clients[$this->userConnections[$participantUserId]]->send(json_encode([
                    'message' => $message,
                    'sender' => $userId,
                    'sent_at' => date("d/m/Y H:i:s")
                ]));
            }
        }

        $stmt->close();
    }
}

$app = new App('localhost', 8080);
$app->route('/chats', new ChatServer, ['*']);
$app->run();
