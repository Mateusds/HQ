<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db/conexao.php'; // Ajuste o caminho conforme necessário

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se os campos foram definidos
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Consultar o banco de dados
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Verificar senha
        if ($user && password_verify($password, $user['senha'])) {
            // Definir variáveis de sessão
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_success'] = true; // Define uma variável de sessão para indicar sucesso
            $_SESSION['user_name'] = $user['nome']; // Adiciona o nome do usuário na sessão
            header('Location: index.php'); // Redireciona para a página principal
            exit();
        } else {
            $error = "Credenciais inválidas.";
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
    <style>
        body {
            padding-bottom: 60px; /* Adiciona espaço no rodapé */
        }
        .container {
            max-width: 400px;
            margin-top: 100px;
            padding: 20px;
            margin-bottom: 60px; /* Adiciona espaço inferior para a caixa de login */
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-custom {
            width: 100%;
            margin-bottom: 10px; /* Espaçamento entre os botões */
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
    <div class="container">
        <h1 class="text-center mb-4">Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-custom">Acessar</button>
            <a href="solicitar_redefinicao.php" class="btn btn-danger btn-custom">Esqueceu sua senha?</a>
            <a href="registro.php" class="btn btn-success btn-custom">Cadastrar-se</a>
        </form>
    </div>

    <div class="footer">
        <p>© 2024 CENTRAL DO HQ. Todos os direitos reservados.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>
