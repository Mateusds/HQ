<?php
require 'vendor/autoload.php';

use Google\Client;

session_start();

$client = new Client();
$client->setClientId('206267141563-ei3bmq5ou2ogmd1s6veqe6g6lne26ub3.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/rombr/oauth2callback'); // A mesma URI de redirecionamento
$client->addScope('https://www.googleapis.com/auth/gmail.send');
$client->setAccessType('offline');
$client->setIncludeGrantedScopes(true);

if (isset($_GET['code'])) {
    // Troca o código de autorização pelo token de acesso
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $accessToken = $client->getAccessToken();

    // Armazena o token na sessão ou em um local seguro
    $_SESSION['access_token'] = $accessToken;

    // Redireciona para a página principal ou qualquer outra página desejada
    header('Location: index.php'); // Ou outra página de sucesso
    exit();
} else {
    die('Código de autorização não recebido.');
}
?>
