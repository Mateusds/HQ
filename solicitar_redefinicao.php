<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Redefinição de Senha</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="../ROMBR/CSS/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #343a40;
            color: #ffffff;
            text-align: center;
            padding: 10px;
            font-size: 14px;
        }
        .footer p {
            margin: 0;
        }
        .container {
            max-width: 400px; /* Reduzi o tamanho máximo da largura */
            margin-top: 100px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .btn-block {
            width: auto; /* Reduzir a largura do botão */
            display: block;
            margin: 0 auto; /* Centraliza o botão */
        }
    </style>
</head>
<body>
   
    <!-- Formulário de Solicitação de Redefinição de Senha -->
    <div class="container mt-5 pt-5">
        <h1 class="text-center">Solicitar Redefinição de Senha</h1>
        <form action="enviar_redefinicao.php" method="post" class="mt-4">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enviar Link de Redefinição</button>
            <div class="text-center mt-3">
            <a href="login.php" class="btn btn-success btn-custom">Ir para a tela de Login</a>
        </div>
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
