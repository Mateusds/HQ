<?php
// Supondo que você já tenha feito a conexão com o banco de dados

$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$capa = $_FILES['capa']['name']; // Ou outro tratamento para a capa
$avaliacao = $_POST['avaliacao'];
$link = $_POST['link']; // Link do arquivo

$sql = "INSERT INTO roms (titulo, descricao, capa, avaliacao, link) VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$titulo, $descricao, $capa, $avaliacao, $link]);

// Redirecionar ou exibir mensagem de sucesso
?>
