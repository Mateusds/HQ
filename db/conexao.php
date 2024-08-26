<?php
$host = 'localhost';
$dbname = 'roms_db'; // Nome do banco de dados
$user = 'root'; // Usuário padrão do MySQL no WampServer
$password = ''; // Senha padrão (geralmente em branco no WampServer)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Definir o modo de erro do PDO para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
