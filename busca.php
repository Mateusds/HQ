<?php
require 'db/conexao.php';

$search = $_GET['search'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM roms WHERE titulo LIKE :search");
$stmt->execute(['search' => "%$search%"]);
$roms = $stmt->fetchAll();
?>

<h1>Resultados da Busca</h1>
<form method="GET" action="busca.php">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" required>
    <button type="submit">Buscar</button>
</form>

<?php foreach ($roms as $rom): ?>
    <div>
        <h3><?php echo htmlspecialchars($rom['titulo']); ?></h3>
        <p><?php echo htmlspecialchars($rom['descricao']); ?></p>
        <a href="<?php echo htmlspecialchars($rom['url_arquivo']); ?>" download>Download</a>
    </div>
<?php endforeach; ?>
