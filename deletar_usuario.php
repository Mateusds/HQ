<?php
session_start();
require 'db/conexao.php';

// Verificar se o usuário é um administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // Redirecionar não autorizado para a página de login
    exit();
}

// Verificar se o ID do usuário foi passado na URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Excluir o usuário
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $userId]);

        // Redirecionar de volta para a lista de usuários com uma mensagem de sucesso
        header("Location: admin_usuarios.php?delete_success=1");
        exit();
    } catch (PDOException $e) {
        // Redirecionar com uma mensagem de erro
        header("Location: admin_usuarios.php?delete_error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirecionar se o ID não foi fornecido
    header("Location: admin_usuarios.php?delete_error=ID de usuário não fornecido.");
    exit();
}
