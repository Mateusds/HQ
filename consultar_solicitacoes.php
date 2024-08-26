<?php
session_start();
require 'db/conexao.php';

// Inicializar variáveis
$solicitacoes = [];
$errorMsg = '';

// Verificar se o usuário está autenticado
$isAuthenticated = isset($_SESSION['usuario_id']);
if (!$isAuthenticated) {
    header('Location: login.php');
    exit();
}

// Executar a consulta para buscar todas as solicitações
try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.nome AS usuario_nome, u.email AS usuario_email
        FROM solicitacoes s
        JOIN usuarios u ON s.usuario_id = u.id
        WHERE s.usuario_id = :usuario_id
        ORDER BY s.data_solicitacao DESC
    ");
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errorMsg = "Erro ao consultar as solicitações: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Solicitações</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
    <style>
        .expired {
            color: red;
        }
        .nearing-expiry {
            color: orange;
        }
        .safe {
            color: orangered;
        }
        .accepted {
            background-color: lightgreen; /* Verde claro */
        }
        .rejected {
            background-color: lightcoral; /* Vermelho claro */
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="#">CENTRAL DO HQ</a>
        <a class="nav-link" href="index.php">Início</a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="solicitar_hq.php">Solicitar HQ</a>
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
        <h1 class="mb-4">Consultar Solicitações</h1>

        <!-- Exibir mensagem de erro, se houver -->
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número da Solicitação</th>
                        <th>Nome do HQ</th>
                        <th>Idioma</th>
                        <th>Data da Solicitação</th>
                        <th>Nome do Usuário</th>
                        <th>Email do Usuário</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitacoes)): ?>
                        <?php foreach ($solicitacoes as $solicitacao): ?>
                            <?php
                            $dataSolicitacao = new DateTime($solicitacao['data_solicitacao']);
                            $dataAtual = new DateTime();
                            $intervalo = $dataAtual->diff($dataSolicitacao);
                            $diasRestantes = 10 - $intervalo->days;
                            $statusClass = '';

                            // Determina a classe CSS com base no status da solicitação
                            if ($solicitacao['status'] === 'atendida') {
                                $statusClass = 'accepted';
                            } elseif ($solicitacao['status'] === 'recusada') {
                                $statusClass = 'rejected';
                            } else {
                                $statusClass = $diasRestantes <= 0 ? 'expired' : ($diasRestantes <= 2 ? 'nearing-expiry' : 'safe');
                            }
                            ?>
                            <tr class="<?php echo htmlspecialchars($statusClass); ?>">
                                <td><?php echo htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'Não disponível'); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['nome_hq'] ?? 'Não disponível'); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['idioma'] ?? 'Não disponível'); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['data_solicitacao'] ?? 'Não disponível'); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['usuario_nome'] ?? 'Não disponível'); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['usuario_email'] ?? 'Não disponível'); ?></td>
                                <td><?php echo htmlspecialchars($solicitacao['status'] ?? 'Não disponível'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhuma solicitação encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>
