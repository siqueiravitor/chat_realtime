<?php
include 'conn.php';

$email = $_POST['email'];
$senha = $_POST['senha'];

$sql = "SELECT id, nome FROM usuarios WHERE email = '$email' AND senha = '$senha'";
$query = mysqli_query($con, $sql);
if(mysqli_num_rows($query)){
    $dados = mysqli_fetch_assoc($query);
    $sql = "DELETE FROM chat_participantes WHERE usuario_id = {$dados['id']}";
    mysqli_query($con, $sql);

    session_start();
    $_SESSION['id_usuario'] = $dados['id'];
    $_SESSION['nome'] = $dados['nome'];

    exit(header('Location: ../chats.php'));
}
exit(header('Location: ../login.php?mensagem=Dados inválidos'));
