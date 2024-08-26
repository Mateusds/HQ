<?php
session_start();
require 'db/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); // Redirecionar para login se o usuário não estiver autenticado
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    
    $_SESSION['success_message'] = "Todas as notificações foram marcadas como lidas.";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erro ao limpar notificações: " . $e->getMessage();
}

header('Location: index.php'); // Redirecionar de volta para a página principal
exit();
