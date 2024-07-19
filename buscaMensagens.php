<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "teste";

// Cria conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$chatId = isset($_GET['chatId']) ? (int)$_GET['chatId'] : 0;

if ($chatId > 0) {
    $stmt = $conn->prepare("SELECT 
                                m.id, 
                                m.mensagem, 
                                m.enviado_em, 
                                u.nome AS usuario_nome
                            FROM mensagens m
                            JOIN usuarios u ON m.usuario_id = u.id
                            WHERE m.chat_id = $chatId
                            ORDER BY m.enviado_em;");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $mensagens = [];
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = $row;
    }
    
    echo json_encode($mensagens);
    
    $stmt->close();
}

$conn->close();
?>
