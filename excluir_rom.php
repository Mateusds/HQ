<?php
session_start();
require 'db/conexao.php';

// Verificar se o usuário está autenticado e é um administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Verificar se o ID da ROM foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar a consulta de exclusão
    $stmt = $pdo->prepare("DELETE FROM roms WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // Redirecionar após a exclusão
    header('Location: index.php');
    exit();
} else {
    echo "ID da ROM não fornecido.";
}
?>
