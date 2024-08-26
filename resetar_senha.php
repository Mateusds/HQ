<?php
session_start();
require 'db/conexao.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT * FROM reset_tokens WHERE token = :token AND expires > NOW()");
    $stmt->execute(['token' => $token]);
    $resetToken = $stmt->fetch();

    if (!$resetToken) {
        die('Token inválido ou expirado.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['password']) && isset($_POST['password_confirm'])) {
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];

            if ($password !== $passwordConfirm) {
                $error = "As senhas não coincidem.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = :email");
                $stmt->execute(['senha' => $passwordHash, 'email' => $resetToken['email']]);

                $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE token = :token");
                $stmt->execute(['token' => $token]);

                $success = "Senha redefinida com sucesso. Você pode agora fazer login com a nova senha.";
            }
        } else {
            $error = "Por favor, preencha todos os campos.";
        }
    }
} else {
    die('Token não fornecido.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
</head>
<body>
    <h1>Redefinir Senha</h1>
    <?php if (isset($error)): ?>
        <p><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <p><?php echo htmlspecialchars($success); ?></p>
    <?php else: ?>
        <form action="resetar_senha.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
            <label for="password">Nova Senha:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <label for="password_confirm">Confirmar Nova Senha:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            <br>
            <button type="submit">Redefinir Senha</button>
        </form>
    <?php endif; ?>
</body>
</html>
