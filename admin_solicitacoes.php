<?php
session_start();
require 'db/conexao.php'; // Ajuste o caminho conforme necessário

// Desativar cache da página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Inicializar a variável $isAdmin
$isAdmin = false;

// Verificar se o usuário é administrador
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $isAdmin = true;
} else {
    header('Location: index.php');
    exit();
}

// Inicializar variáveis
$solicitacoes = [];

// Executar a consulta para buscar todas as solicitações com status diferente de 'Recusada' e 'Atendida'
try {
    $stmt = $pdo->query("
        SELECT s.*
        FROM solicitacoes s
        WHERE s.status NOT IN ('Recusada', 'Atendida')
        ORDER BY s.data_solicitacao DESC
    ");
    $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar as solicitações: " . $e->getMessage();
}

// Processar ações de atender e recusar solicitações
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'atender') {
        // Atualizar o status da solicitação para "Atendida"
        try {
            // Atualizar o status da solicitação
            $stmt = $pdo->prepare("UPDATE solicitacoes SET status = 'Atendida' WHERE id = ?");
            $stmt->execute([$id]);

            // Puxar o e-mail do usuário que fez a solicitação
            $stmt = $pdo->prepare("
                SELECT u.email
                FROM solicitacoes s
                JOIN usuarios u ON s.usuario_id = u.id
                WHERE s.id = ?
            ");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if ($user) {
                // Adicionar notificação
                $stmt = $pdo->prepare("
                    INSERT INTO notificacoes (usuario_id, mensagem, lida)
                    VALUES ((SELECT id FROM usuarios WHERE email = ?), ?, 0)
                ");
                $stmt->execute([$user['email'], 'Sua solicitação foi atendida.']);
            }

            // Redirecionar para a tela de adicionar ROM
            header('Location: adicionar_rom.php?id=' . $id);
            exit(); // Certifique-se de sair do script após o redirecionamento
        } catch (PDOException $e) {
            echo "Erro ao atender a solicitação: " . $e->getMessage();
        }
    } elseif ($action === 'recusar') {
        // Atualizar o status da solicitação para "Recusada"
        try {
            $stmt = $pdo->prepare("UPDATE solicitacoes SET status = 'Recusada' WHERE id = ?");
            $stmt->execute([$id]);

            // Redirecionar para o painel de solicitações
            header('Location: admin_solicitacoes.php');
            exit(); // Certifique-se de sair do script após o redirecionamento
        } catch (PDOException $e) {
            echo "Erro ao recusar a solicitação: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Solicitações</title>
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
            color: orange;
        }
        .action-buttons {
            display: flex;
            gap: 10px; /* Espaço entre os botões */
        }
        .table-container {
            overflow-x: auto; /* Permite rolagem horizontal */
        }
        table {
            width: 100%; /* Garante que a tabela se ajuste ao contêiner */
        }
    </style>
    <script>
        function confirmarRecusa(event) {
            if (!confirm("Você tem certeza de que deseja recusar esta solicitação?")) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="#">CENTRAL DO HQ</a>
        <a class="nav-link" href="index.php">Início</a><!-- Link para a tela inicial -->

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <?php if ($isAdmin): ?>
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
    <div class="container mt-5">
        <h1 class="mb-4">Painel de Solicitações</h1>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número da Solicitação</th>
                        <th>Nome do HQ</th>
                        <th>Idioma</th>
                        <th>Data da Solicitação</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitacoes as $solicitacao): ?>
                        <?php
                        $dataSolicitacao = new DateTime($solicitacao['data_solicitacao']);
                        $dataAtual = new DateTime();
                        $intervalo = $dataAtual->diff($dataSolicitacao);
                        $diasRestantes = 10 - $intervalo->days;
                        $statusClass = $diasRestantes <= 0 ? 'expired' : ($diasRestantes <= 2 ? 'nearing-expiry' : 'safe');
                        ?>
                        <tr class="<?php echo $statusClass; ?>">
                            <td><?php echo htmlspecialchars($solicitacao['numero_solicitacao'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($solicitacao['nome_hq'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($solicitacao['idioma'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($solicitacao['data_solicitacao'] ?? 'N/A'); ?></td>
                            <td><?php echo $diasRestantes <= 0 ? 'Prazo expirado' : ($diasRestantes <= 2 ? 'Próximo do fim' : 'Dentro do prazo'); ?></td>
                            <td class="action-buttons">
                                <a href="admin_solicitacoes.php?action=atender&id=<?php echo $solicitacao['id']; ?>" class="btn btn-info">Atender</a>
                                <a href="admin_solicitacoes.php?action=recusar&id=<?php echo $solicitacao['id']; ?>" class="btn btn-danger" onclick="confirmarRecusa(event)">Recusar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>
</html>
