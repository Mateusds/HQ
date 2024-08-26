<?php
session_start();
require 'db/conexao.php';

// Inicializar a variável $isAdmin com um valor padrão
$isAdmin = false;

// Inicializar a variável $isUser com um valor padrão
$isUser = false;

// Inicializar a variável de busca
$searchQuery = '';

// Inicializar a variável para o alerta de erro
$noResults = false;
$errorMsg = '';

// Inicializar a variável de notificações
$notificacoes = [];

// Verificar se o usuário está autenticado e se é um admin ou usuário
$isAuthenticated = isset($_SESSION['usuario_id']);
if ($isAuthenticated) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            $isAdmin = true;
        } elseif ($_SESSION['role'] === 'user') {
            $isUser = true;
        }
    }

    // Buscar notificações não lidas para o usuário atual
    if ($isUser) {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM notificacoes 
                WHERE usuario_id = ? AND lida = 0
                ORDER BY data_criacao DESC
            ");
            $stmt->execute([$_SESSION['usuario_id']]);
            $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorMsg = "Erro ao consultar notificações: " . $e->getMessage();
        }
    }
}

// Verificar se há mensagem de erro na sessão
if (isset($_SESSION['error_message'])) {
    $errorMsg = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Limpar a mensagem de erro após a exibição
}

// Verificar a busca
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
}

// Executar a consulta para buscar todas as ROMs
$roms = [];
try {
    if ($searchQuery) {
        // Buscar ROMs com o nome que contém o termo de busca
        $stmt = $pdo->prepare("SELECT * FROM roms WHERE titulo LIKE ?");
        $stmt->execute(["%$searchQuery%"]);
        $roms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Definir a variável de alerta se não houver resultados
        if (empty($roms)) {
            $noResults = true;
        }
    } else {
        // Buscar todas as ROMs
        $stmt = $pdo->query("SELECT * FROM roms");
        $roms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Erro ao consultar as ROMs: " . $e->getMessage();
}

// Verifica se há uma mensagem de sucesso de login
$showModal = isset($_SESSION['login_success']) && $_SESSION['login_success'];
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Limpar a variável após exibi-la
unset($_SESSION['login_success']);
unset($_SESSION['user_name']);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

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
        .notification-icon {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 0.2em 0.6em;
            font-size: 0.75em;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .notification-list {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 250px;
        }
        .notification-list.active {
            display: block;
        }
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item a {
            color: #007bff;
            text-decoration: none;
        }
        .notification-item a:hover {
            text-decoration: underline;
        }
    </style>
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
                        <a class="nav-link" href="admin_solicitacoes.php">Administração de Solicitações</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adicionar_rom.php">Inserir Nova ROM</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_usuarios.php">Usuários</a>
                    </li>
                <?php endif; ?>
                <?php if ($isUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="consultar_solicitacoes.php">Consultar Solicitações</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="solicitar_hq.php">Solicitar HQ</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ml-auto">
            <li class="nav-item notification-icon">
    <i class="fas fa-bell"></i>
    <?php if (!empty($notificacoes)): ?>
        <div class="notification-badge"><?php echo count($notificacoes); ?></div>
    <?php endif; ?>
    <div class="notification-list">
        <?php if (!empty($notificacoes)): ?>
            <?php foreach ($notificacoes as $notificacao): ?>
                <div class="notification-item">
                    <a href="#"><?php echo htmlspecialchars($notificacao['mensagem']); ?></a>
                </div>
            <?php endforeach; ?>
            <div class="notification-item text-center">
                <form method="post" action="limpar_notificacoes.php">
                    <button type="submit" class="btn btn-link btn-sm">Limpar tudo</button>
                </form>
            </div>
        <?php else: ?>
            <div class="notification-item">
                Nenhuma notificação nova.
            </div>
        <?php endif; ?>
    </div>
</li>



                </li>
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

    <!-- Formulário de Busca -->
    <div class="container mt-5 pt-5">
        <form method="get" action="index.php" class="form-inline my-2 my-lg-0">
            <div class="input-group">
                <input class="form-control" type="search" name="search" placeholder="Buscar ROM" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-outline-success" type="submit">Buscar</button>
                </div>
            </div>
        </form>

        <h1 class="mt-4">Lista de ROMS</h1>

        <!-- Exibir mensagem de erro, se houver -->
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <!-- Mensagem para visitantes não autenticados -->
        <?php if (!$isAuthenticated): ?>
            <div class="alert alert-warning mt-3" role="alert">
                Para baixar ROMs, por favor, <a href="cadastrar.php">cadastre-se</a> ou <a href="login.php">faça login</a>.
            </div>
        <?php endif; ?>

        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Capa</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roms)): ?>
                    <?php foreach ($roms as $rom): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rom['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($rom['descricao']); ?></td>
                            <td>
                                <?php if (!empty($rom['capa'])): ?>
                                    <img src="<?php echo htmlspecialchars($rom['capa']); ?>" alt="Capa" style="width: 100px; height: auto;">
                                <?php else: ?>
                                    Sem Capa
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isAuthenticated): ?>
                                    <?php if (!empty($rom['link'])): ?>
                                        <a href="<?php echo htmlspecialchars($rom['link']); ?>" class="btn btn-primary">Baixar</a>
                                    <?php else: ?>
                                        <span class="text-muted">Sem link para download</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Faça login para baixar</span>
                                <?php endif; ?>

                                <?php if ($isAdmin): ?>
                                    <a href="editar_rom.php?id=<?php echo htmlspecialchars($rom['id']); ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="excluir_rom.php?id=<?php echo htmlspecialchars($rom['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta ROM?');">Excluir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Nenhuma ROM encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal de sucesso de login -->
    <?php if ($showModal): ?>
        <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Bem-vindo!</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Olá, <?php echo htmlspecialchars($userName); ?>! Seu login foi um sucesso.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>© 2024 CENTRAL DO HQ. Todos os direitos reservados.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    
    <script>
        // Exibir o modal quando a página for carregada
        $(document).ready(function() {
            <?php if ($showModal): ?>
                $('#successModal').modal('show');
            <?php endif; ?>

            // Exibir/ocultar notificações ao clicar no ícone
            $('.notification-icon').click(function() {
                $('.notification-list').toggleClass('active');
            });

            // Fechar notificações quando clicar fora
            $(document).click(function(event) {
                if (!$(event.target).closest('.notification-icon').length) {
                    $('.notification-list').removeClass('active');
                }
            });
        });
    </script>
</body>
</html>
