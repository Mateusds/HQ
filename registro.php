<?php
require 'db/conexao.php';

$mensagem = '';
$registro_sucesso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
    $data_nascimento = $_POST['data_nascimento'];
    $termos_aceitos = isset($_POST['termos_aceitos']); // Verifica se os termos foram aceitos

    // Função para verificar se o usuário tem pelo menos 18 anos
    function verificarIdade($data_nascimento) {
        $dataAtual = new DateTime();
        $dataNascimento = new DateTime($data_nascimento);
        $idade = $dataAtual->diff($dataNascimento)->y;
        return $idade >= 18;
    }

    if (!verificarIdade($data_nascimento)) {
        $mensagem = "Você deve ter pelo menos 18 anos para se cadastrar.";
    } elseif (!$termos_aceitos) {
        $mensagem = "Você deve aceitar os termos e condições para se cadastrar.";
    } else {
        // Verificar se o nome de usuário ou email já está cadastrado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nome = :usuario OR email = :email");
        $stmt->execute(['usuario' => $usuario, 'email' => $email]);
        $existente = $stmt->fetchColumn();

        if ($existente) {
            $mensagem = "O nome de usuário ou email já está cadastrado.";
        } else {
            // Inserir novo usuário
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, data_nascimento) VALUES (:nome, :email, :senha, :data_nascimento)");
            $stmt->execute(['nome' => $usuario, 'email' => $email, 'senha' => $senha, 'data_nascimento' => $data_nascimento]);
            $registro_sucesso = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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
            margin-bottom: 60px; /* Adiciona espaço inferior para a caixa de registro */
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-control {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            height: 2.5rem; /* Ajusta a altura dos campos */
        }
        .form-control-sm {
            height: 2rem; /* Ajusta a altura dos campos menores */
        }
        .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            height: 2.5rem; /* Ajusta a altura do botão */
        }
        .btn-sm {
            height: 2rem; /* Ajusta a altura do botão menor */
            font-size: 0.75rem; /* Ajusta o tamanho da fonte do botão menor */
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
        .password-strength {
            height: 5px;
            width: 100%;
            background-color: #ddd;
            margin-top: 5px;
            border-radius: 3px;
        }
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s;
            border-radius: 3px;
        }
        .strength-weak { background-color: red; }
        .strength-medium { background-color: yellow; }
        .strength-strong { background-color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Registro</h1>
        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <form method="POST" action="registro.php">
            <div class="form-group">
                <label for="usuario">Usuário:</label>
                <input type="text" id="usuario" name="usuario" class="form-control form-control-sm" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control form-control-sm" required>
            </div>
            <div class="form-group">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" id="data_nascimento" name="data_nascimento" class="form-control form-control-sm" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" class="form-control form-control-sm" required oninput="checkPasswordStrength()">
                <div id="password-strength" class="password-strength">
                    <div id="password-strength-bar" class="password-strength-bar"></div>
                </div>
                <p id="password-strength-text"></p>
            </div>
            <div class="form-group form-check">
                <input type="checkbox" id="termos_aceitos" name="termos_aceitos" class="form-check-input" required>
                <label for="termos_aceitos" class="form-check-label">Eu aceito os <a href="termos.php">termos e condições</a></label>
            </div>
            <button type="submit" class="btn btn-primary btn-custom btn-sm">Registrar</button>
        </form>
        <div class="text-center mt-3">
            <p>Já possui uma conta? <a href="login.php">Faça login</a></p>
        </div>
    </div>

    <div class="footer">
        <p>© 2024 CENTRAL DO HQ. Todos os direitos reservados.</p>
    </div>

    <!-- Modal de sucesso -->
    <div class="modal fade" id="sucessoModal" tabindex="-1" role="dialog" aria-labelledby="sucessoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sucessoModalLabel">Cadastro realizado com sucesso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Seu cadastro foi realizado com sucesso! Agora você pode acessar o acervo utilizando seu login e senha.
                </div>
                <div class="modal-footer">
                    <a href="login.php" class="btn btn-primary">Ir para o login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('senha').value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            let strength = 0;

            // Check password strength
            if (password.length >= 8) {
                strength += 1;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
                if (/\d/.test(password)) strength += 1;
                if (/[^a-zA-Z\d]/.test(password)) strength += 1;
            }

            // Update strength bar and text
            switch (strength) {
                case 1:
                    strengthBar.style.width = '33%';
                    strengthBar.className = 'password-strength-bar strength-weak';
                    strengthText.innerText = 'Senha fraca';
                    break;
                case 2:
                    strengthBar.style.width = '66%';
                    strengthBar.className = 'password-strength-bar strength-medium';
                    strengthText.innerText = 'Senha média';
                    break;
                case 3:
                case 4:
                    strengthBar.style.width = '100%';
                    strengthBar.className = 'password-strength-bar strength-strong';
                    strengthText.innerText = 'Senha forte';
                    break;
                default:
                    strengthBar.style.width = '0';
                    strengthText.innerText = '';
                    break;
            }
        }

        // Mostrar o modal se o registro for bem-sucedido
        <?php if ($registro_sucesso): ?>
            $(document).ready(function() {
                $('#sucessoModal').modal('show');
            });
        <?php endif; ?>
    </script>
</body>
</html>
