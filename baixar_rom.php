<?php
session_start();
require 'db/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$rom_id = $_GET['id'];

// Definir o limite diário de downloads
$limite_diario = 3; // Exemplo: máximo de 3 downloads por dia

// Verificar quantos downloads o usuário já fez hoje
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE usuario_id = ? AND DATE(data_download) = CURDATE()");
    $stmt->execute([$usuario_id]);
    $downloads_hoje = $stmt->fetchColumn();

    if ($downloads_hoje >= $limite_diario) {
        $_SESSION['erro_download'] = 'Você atingiu o limite de downloads diários.';
        header('Location: index.php');
        exit();
    }

    // Registrar o download
    $stmt = $pdo->prepare("INSERT INTO downloads (usuario_id, rom_id, data_download) VALUES (?, ?, CURDATE())");
    $stmt->execute([$usuario_id, $rom_id]);

    // Obter o link da ROM
    $stmt = $pdo->prepare("SELECT link FROM roms WHERE id = ?");
    $stmt->execute([$rom_id]);
    $rom = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rom && !empty($rom['link'])) {
        header('Location: ' . $rom['link']);
        exit();
    } else {
        $_SESSION['erro_download'] = 'Link para download não encontrado.';
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
