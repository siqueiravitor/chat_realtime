<?php
include 'include/conn.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats</title>
</head>

<body>
    <form method="POST" action="conversa.php">
        <select name='chat'>
            <option value=''>Selecione o chat</option>
            <?php
            $sqlChats = "SELECT id, nome FROM chats";
            $queryChats = mysqli_query($con, $sqlChats);
            while ($chats = mysqli_fetch_assoc($queryChats)) {
                echo "<option value='{$chats['id']}'>{$chats['nome']}</option>";
            }
            ?>
        </select>
        <button>Selecionar</button>
    </form>
    <hr>
</body>

</html>