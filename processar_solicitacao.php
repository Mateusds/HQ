<?php
session_start();
require 'db/conexao.php';

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$titulo = $_POST['titulo'];
$idioma = $_POST['idioma'];

// Inserir a solicitação no banco de dados
try {
    $stmt = $pdo->prepare("INSERT INTO solicitacoes (usuario_id, titulo, idioma, status, data_solicitacao) VALUES (?, ?, ?, 'pendente', NOW())");
    $stmt->execute([$_SESSION['usuario_id'], $titulo, $idioma]);
    
    // Redirecionar ou mostrar mensagem de sucesso
    $_SESSION['success_message'] = "Sua solicitação foi enviada com sucesso!";
    header('Location: solicitar_hq.php');
} catch (PDOException $e) {
    // Tratar erro
    $_SESSION['error_message'] = "Erro ao processar a solicitação: " . $e->getMessage();
    header('Location: solicitar_hq.php');
}
?>
