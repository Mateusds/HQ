<?php
session_start();

// Inicializar a variável $isAdmin com um valor padrão
$isAdmin = false;

// Verificar se o usuário está autenticado e se é um admin
if (isset($_SESSION['usuario_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $isAdmin = true;
} else {
    header("Location: index.php"); // Redirecionar se não for admin
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Buscar a ROM no banco de dados
    try {
        require 'db/conexao.php';
        $stmt = $pdo->prepare("SELECT * FROM roms WHERE id = ?");
        $stmt->execute([$id]);
        $rom = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro ao buscar ROM: " . $e->getMessage();
        exit();
    }

    if (!$rom) {
        echo "ROM não encontrada.";
        exit();
    }
} else {
    echo "ID da ROM não especificado.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $link = $_POST['link'];

    // Processar o upload do arquivo
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['capa']['name']);

        if (move_uploaded_file($_FILES['capa']['tmp_name'], $uploadFile)) {
            $capa = $uploadFile;
        } else {
            $capa = $rom['capa']; // Manter a capa existente se o upload falhar
        }
    } else {
        $capa = $rom['capa']; // Manter a capa existente se nenhum arquivo for enviado
    }

    // Atualizar no banco de dados
    try {
        require 'db/conexao.php';
        $stmt = $pdo->prepare("UPDATE roms SET titulo = ?, descricao = ?, capa = ?, link = ? WHERE id = ?");
        $stmt->execute([$nome, $descricao, $capa, $link, $id]);

        // Redirecionar para index.php
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        echo "Erro ao atualizar ROM: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar ROM</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
    <link rel="stylesheet" href="./CSS/fontawesome-free-6.6.0-web/css/all.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="index.php">CENTRAL DO HQ</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Início</a>
                </li>
                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="adicionar_rom.php">Inserir Nova ROM</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <div class="dropdown">
                        <button class="btn btn-info" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Configurações
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="solicitar_redefinicao.php">Alterar senha</a>
                            <a class="dropdown-item" href="#">Mudar foto do perfil</a>
                            <a class="dropdown-item" href="#">Sobre</a>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-info">Sair</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Container -->
    <div class="container mt-5 pt-5">
        <h1>Editar ROM</h1>
        <form action="editar_rom.php?id=<?php echo htmlspecialchars($id); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($rom['titulo']); ?>" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea class="form-control" id="descricao" name="descricao"><?php echo htmlspecialchars($rom['descricao']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="capa">Capa:</label>
                <input type="file" class="form-control" id="capa" name="capa" accept="image/*">
                <?php if ($rom['capa']): ?>
                    <img src="<?php echo htmlspecialchars($rom['capa']); ?>" alt="Capa" style="max-width: 200px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="link">Link para o arquivo:</label>
                <input type="text" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($rom['link']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Atualizar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
