<?php
session_start();
require 'db/conexao.php';

// Verificar se o usuário é um administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php'); // Redirecionar não autorizado para a página de login
    exit();
}

// Inicializar variáveis
$errorMsg = '';
$successMsg = '';
$usuario = null;

// Verificar se o ID do usuário foi passado na URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Buscar os dados do usuário
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMsg = "Erro ao buscar os dados do usuário: " . $e->getMessage();
    }

    // Verificar se o formulário foi submetido
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $dataNascimento = $_POST['data_nascimento'] ?? '';

        // Atualizar os dados do usuário
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, data_nascimento = :data_nascimento WHERE id = :id");
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'data_nascimento' => $dataNascimento,
                'id' => $userId
            ]);
            $successMsg = "Usuário atualizado com sucesso!";
            
            // Redirecionar para a página admin_usuarios.php após sucesso
            header('Location: admin_usuarios.php');
            exit();
        } catch (PDOException $e) {
            $errorMsg = "Erro ao atualizar os dados do usuário: " . $e->getMessage();
        }
    }
} else {
    $errorMsg = "ID de usuário não fornecido.";
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Editar Usuário</h1>
        
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo htmlspecialchars($errorMsg ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (!empty($successMsg)): ?>
            <div class="alert alert-success mt-3" role="alert">
                <?php echo htmlspecialchars($successMsg ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if ($usuario): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="nome">Usuário</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($usuario['data_nascimento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="admin_usuarios.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
