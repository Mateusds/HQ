<?php
session_start();
require 'db/conexao.php';

// Inicializar a variável $isAdmin com um valor padrão
$isAdmin = false;

// Verificar se o usuário está autenticado e se é um admin
if (isset($_SESSION['usuario_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $isAdmin = true;
}

// Executar a consulta para buscar todas as ROMs
$roms = [];
try {
    $stmt = $pdo->query("SELECT * FROM roms");
    $roms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar as ROMs: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de ROMs</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
.carousel-item img {
    width: 100%; /* A imagem ocupa 100% da largura do carrossel */
    height: 380px; /* Altura fixa para a imagem */
    object-fit: cover; /* Ajusta a imagem para se ajustar dentro do contêiner sem cortar */
}
.footer {
    position: fixed;
    bottom: 0;
    width: 100%;
    background-color: #343a40; /* Cor de fundo escura */
    color: #ffffff; /* Cor do texto branca */
    text-align: center; /* Alinhamento centralizado */
    padding: 10px; /* Espaçamento interno */
    font-size: 14px; /* Tamanho da fonte */
}
.footer p {
    margin: 0; /* Remove a margem padrão do parágrafo */
}
</style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="#">CENTRAL DO HQ</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
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

    <div class="container mt-5">
        <h1>Categorias</h1>
        <ul>
            <?php if (!empty($categorias)): ?>
                <?php foreach ($categorias as $categoria): ?>
                    <li>
                        <a href="roms.php?categoria=<?php echo htmlspecialchars($categoria['id']); ?>">
                            <?php echo htmlspecialchars($categoria['nome']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma categoria encontrada.</p>
            <?php endif; ?>
        </ul>
    </div>

    <footer class="footer">
        <p>&copy; 2024 ROMBR. Todos os direitos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK3G9M0s4Yd1N0pt5n3qzq9VUbqXl2zj3Jf7CO6lSOYl2e6" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UOa1xlc5KPEj3o5j93xjW4T7aF77Xw0lZOhce7GNTlggtFZ5JOhgm8E91CQOtwxZ7" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfyyN2dX48j+UO8rKBLpZZ8AqvC7vxeTr4nEghWrM5a6h5XQ0JZbN3D3LxLrGz9" crossorigin="anonymous"></script>
</body>
</html>
