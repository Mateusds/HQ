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
            $capa = null;
        }
    } else {
        $capa = null;
    }

    // Inserir no banco de dados
    try {
        require 'db/conexao.php';
        $stmt = $pdo->prepare("INSERT INTO roms (titulo, descricao, capa, link) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $capa, $link]);

        // Redirecionar para index.php
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        echo "Erro ao inserir ROM: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserir Nova ROM</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
    <link rel="stylesheet" href="./CSS/fontawesome-free-6.6.0-web/css/all.css">
    <style>
        .carousel-item img { width: 100%; height: 380px; object-fit: cover; }
        .footer { position: fixed; bottom: 0; width: 100%; background-color: #343a40; color: #ffffff; text-align: center; padding: 10px; font-size: 12px; }
        .footer p { margin: 0; }
        .popover { max-width: 300px; }
        .swing { animation: swing 1s ease-in-out infinite; }
        @keyframes swing {
            0% { transform: rotate(-10deg); }
            50% { transform: rotate(10deg); }
            100% { transform: rotate(-10deg); }
        }
        .expired {
            color: red;
        }
        .nearing-expiry {
            color: orange;
        }
        .safe {
            color: green;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
        }
        .container {
            margin-bottom: 60px; /* Adiciona espaço para o footer */
        }
    </style>
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
                        <a class="nav-link" href="admin_solicitacoes.php">Administração de Solicitações</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adicionar_rom.php">Inserir Nova ROM</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_usuarios.php">Usuários</a>
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
        <h1>Inserir Nova ROM</h1>
        <form action="adicionar_rom.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" class="form-control" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <textarea class="form-control" id="descricao" name="descricao"></textarea> <!-- Campo não obrigatório -->
            </div>
            <div class="form-group">
                <label for="capa">Capa:</label>
                <input type="file" class="form-control" id="capa" name="capa" accept="image/*">
            </div>
            <div class="form-group">
                <label for="link">Link para o arquivo:</label>
                <input type="text" class="form-control" id="link" name="link" required>
            </div>
            <button type="submit" class="btn btn-primary">Inserir</button>
        </form>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>&copy; 2024 CENTRAL DO HQ - Todos os direitos reservados.</p>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script src="../JS/main.js"></script>
</body>
</html>
