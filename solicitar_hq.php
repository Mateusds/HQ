<?php
session_start();
require 'db/conexao.php';

// Verificar se o usuário está autenticado
$isAuthenticated = isset($_SESSION['usuario_id']);
if (!$isAuthenticated) {
    header('Location: login.php');
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$limitePorDia = 10;
$dataAtual = date('Y-m-d');

// Inicializar mensagens
$erro = '';
$sucesso = '';

// Verificar o número de solicitações realizadas pelo usuário no dia atual
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_solicitacoes
        FROM solicitacoes
        WHERE usuario_id = :usuario_id
        AND DATE(data_solicitacao) = :data_atual
    ");
    $stmt->execute([
        'usuario_id' => $usuarioId,
        'data_atual' => $dataAtual
    ]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalSolicitacoes = $resultado['total_solicitacoes'];

    if ($totalSolicitacoes >= $limitePorDia) {
        $erro = "Você já atingiu o limite de solicitações hoje, por favor volte amanhã!";
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomeHQ = $_POST['titulo']; // Altere para 'titulo' conforme o campo no formulário
            $idioma = $_POST['idioma'];

            // Validar dados do formulário
            if (!empty($nomeHQ)) {
                try {
                    // Gerar o próximo número da solicitação
                    $stmt = $pdo->query("SELECT COALESCE(MAX(CAST(SUBSTR(numero_solicitacao, 2) AS UNSIGNED)), 0) + 1 AS proximo_numero FROM solicitacoes");
                    $proximoNumero = $stmt->fetch(PDO::FETCH_ASSOC)['proximo_numero'];
                    $numeroSolicitacao = sprintf("#%03d", $proximoNumero);

                    // Inserir a solicitação
                    $stmt = $pdo->prepare("
                        INSERT INTO solicitacoes (usuario_id, nome_hq, idioma, data_solicitacao, numero_solicitacao)
                        VALUES (:usuario_id, :nome_hq, :idioma, NOW(), :numero_solicitacao)
                    ");
                    $stmt->execute([
                        'usuario_id' => $usuarioId,
                        'nome_hq' => $nomeHQ,
                        'idioma' => $idioma,
                        'numero_solicitacao' => $numeroSolicitacao
                    ]);
                    $sucesso = "Solicitação realizada com sucesso! Seu número de solicitação é $numeroSolicitacao.";
                } catch (PDOException $e) {
                    $erro = "Erro ao realizar a solicitação: " . $e->getMessage();
                }
            } else {
                $erro = "Por favor, preencha todos os campos.";
            }
        }
    }
} catch (PDOException $e) {
    $erro = "Erro ao verificar o número de solicitações: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar HQ</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="index.php">CENTRAL DO HQ</a>
        <a class="nav-link" href="index.php">Início</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="consultar_solicitacoes.php">Consultar Solicitações</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <div class="dropdown">
                        <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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

    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Solicitar HQ</h1>

        <!-- Exibir mensagem de erro, se houver -->
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- Exibir mensagem de sucesso, se houver -->
        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success mt-3" role="alert">
                <?php echo htmlspecialchars($sucesso); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="titulo">Nome do HQ:</label>
                <input type="text" id="titulo" name="titulo" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="idioma">Idioma:</label>
                <select id="idioma" name="idioma" class="form-control" required>
                    <option value="Portugues">Português</option>
                    <option value="Ingles">Inglês</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Solicitação</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>
