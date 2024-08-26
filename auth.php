<?php
require 'vendor/autoload.php';

use Google\Client;

session_start();

$client = new Client();
$client->setClientId('206267141563-ei3bmq5ou2ogmd1s6veqe6g6lne26ub3.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/rombr/oauth2callback'); // Defina aqui sua URI de redirecionamento
$client->addScope('https://www.googleapis.com/auth/gmail.send');
$client->setAccessType('offline');
$client->setIncludeGrantedScopes(true);

// Gera a URL de autorização
$authUrl = $client->createAuthUrl();

header('Location: ' . $authUrl);
exit();
?>
