<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
require 'db/conexao.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Client;
use Google\Service\Gmail;

require 'vendor/autoload.php'; // Inclua o autoload do Composer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];

        // Verificar se o e-mail está registrado
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Gerar token de redefinição
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Armazenar token e data de expiração no banco de dados
            $stmt = $pdo->prepare("INSERT INTO reset_tokens (email, token, expires) VALUES (:email, :token, :expires)");
            $stmt->execute(['email' => $email, 'token' => $token, 'expires' => $expires]);

            // Gerar o link de redefinição
            $resetLink = "http://localhost/rombr/resetar_senha.php?token=" . $token;

            // Configurar PHPMailer com OAuth 2.0
            $mail = new PHPMailer(true);
            try {
                // Configurações do servidor
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->OAuth      = true;
                $mail->AuthType   = 'XOAUTH2';
                $mail->SMTPDebug  = 2; // Ativar modo de depuração para ver mais detalhes

                // Verificar se o token de acesso está presente
                if (!isset($_SESSION['access_token'])) {
                    throw new Exception('Token de acesso não encontrado na sessão.');
                }

                // Obter o token de acesso
                $accessToken = $_SESSION['access_token'];
                if (is_array($accessToken)) {
                    $token = json_encode($accessToken);
                } else {
                    throw new Exception('Token de acesso inválido.');
                }

                $client = new Client();
                $client->setClientId('206267141563-ei3bmq5ou2ogmd1s6veqe6g6lne26ub3.apps.googleusercontent.com');
                $client->setClientSecret('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');
                $client->setAccessToken($token);

                $service = new Gmail($client);
                $mail->setOAuth(new PHPMailer\PHPMailer\OAuth([
                    'provider' => $service,
                    'clientId' => '206267141563-ei3bmq5ou2ogmd1s6veqe6g6lne26ub3.apps.googleusercontent.com',
                    'clientSecret' => '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',
                    'refreshToken' => $accessToken['refresh_token']
                ]));

                $mail->setFrom('classicsromsbr@gmail.com', 'ROM BR');
                $mail->addAddress($email);

                // Conteúdo do e-mail
                $mail->isHTML(true);
                $mail->Subject = 'Redefinição de Senha';
                $mail->Body    = 'Clique <a href="' . $resetLink . '">aqui</a> para redefinir sua senha.';
                $mail->AltBody = 'Clique no link para redefinir sua senha: ' . $resetLink;

                $mail->send();
                echo 'Um link de redefinição de senha foi enviado para o seu e-mail.';
            } catch (Exception $e) {
                echo "Ocorreu um erro ao enviar o e-mail. Detalhes: " . $e->getMessage();
            }
        } else {
            echo 'E-mail não registrado.';
        }
    }
}
?>
