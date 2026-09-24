<?php
//-------------------------------------------
// Cadastro.php
// Formulário de registo (separa CSS/JS em ficheiros externos)
//-------------------------------------------

// 1) Inclui o ficheiro que instancia $mysqli (sem fechar conexão)
require_once __DIR__ . '/../../databaseconnect.php';

// 2) Inicializa as variáveis que o formulário vai usar
$name = '';
$email = '';
$password = '';
$confirmPassword = '';
$errors = [];

// 3) Se o método for POST, faz o processamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // 3.1) Validações básicas
    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = "Todos os campos são obrigatórios.";
    }

    // Validação do nome de usuário
    if (!preg_match('/^[a-zA-Z0-9_.]+$/', $name)) {
        $errors[] = "O nome de usuário não pode conter espaços ou caracteres especiais.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "E-mail inválido.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "As senhas não coincidem.";
    }
    if (strlen($password) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres.";
    }

    // Validação de força da senha
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars) {
        $errors[] = "A senha deve incluir pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial.";
    }

    // 3.2) Verificar duplicidade de e‐mail e username se não houver erros

    // Verificar se o nome de usuário já existe no banco
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT KR_USER_NAME FROM KR_USERS WHERE KR_USER_NAME = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Este nome de usuário já está em uso.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("
            SELECT KR_EMAIL_USER
              FROM KR_USERS
             WHERE KR_EMAIL_USER = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Este e-mail já está registado.";
        }
        $stmt->close();
    }

    // 3.3) Inserir novo utilizador se não houver erros
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("
            INSERT INTO KR_USERS (KR_EMAIL_USER, KR_USER_NAME, KR_PASSWORD)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $email, $name, $hashedPassword);

        if ($stmt->execute()) {
            // Registo bem‐sucedido: redireciona para /KRLegends/Index.php
            header("Location: https://alpha.soaresbasto.pt/~a29621/KRLegends/Index.php");
            $stmt->close();
            $mysqli->close();
            exit;
        } else {
            $errors[] = "Erro ao criar conta: " . htmlspecialchars($stmt->error);
            $stmt->close();
        }
    }

    // Fecha conexão para recarregar o formulário com erros
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="description" content="Crie sua conta no KR Legends para participar da comunidade de pilotos." />

    <title>Cadastro - KR Legends</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Fontes e ícones -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Manifesto para apps web em Android -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">
    
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="Geral_cl.css" />
    <link rel="stylesheet" href="Cadastro.css" />
    <link rel="stylesheet" href="responsive.css" />
</head>

<body>
    <!-- Particles background -->
    <div id="particles-js"></div>

    <!-- Toggle theme button -->
    <button class="theme-toggle" aria-label="Alternar tema" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="logo-container">
                        <img src="" alt="KR Legends" class="login-logo" id="brandLogo">
                    </div>

                    <h2 class="text-center mb-4 card-title">Crie sua conta</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form id="registerForm" action="Cadastro.php" method="POST" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="name" name="name" placeholder="Nome completo"
                                value="<?= htmlspecialchars($name) ?>" required>
                            <label for="name"><i class="fas fa-user me-2"></i>Nome de utilizador</label>
                            <div class="invalid-feedback">
                                Por favor, insira seu nome completo.
                            </div>
                            <!-- Exibir a mensagem de erro do nome de usuário -->
                            <div id="usernameError" class="text-danger"></div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="nome@exemplo.com" value="<?= htmlspecialchars($email) ?>" required>
                            <label for="email"><i class="fas fa-envelope me-2"></i>E-mail</label>
                            <div class="invalid-feedback" id="emailError">
                                Por favor, insira um e-mail válido.
                            </div>
                        </div>

                        <div class="form-floating mb-3 password-field">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Senha" required>
                            <label for="password"><i class="fas fa-lock me-2"></i>Senha</label>
                            <button type="button" class="btn password-toggle" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>

                            <!-- Password strength indicator -->
                            <div class="password-strength-container">
                                <div class="password-strength-wrapper">
                                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                </div>
                                <small class="form-text text-muted strength-text" id="passwordStrengthText">
                                    Força da senha: <span>Não definida</span>
                                </small>
                            </div>
                        </div>

                        <div class="form-floating mb-3 password-field">
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                placeholder="Confirmar Senha" required>
                            <label for="confirmPassword"><i class="fas fa-lock me-2"></i>Confirmar Senha</label>
                            <button type="button" class="btn password-toggle" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                            <div class="invalid-feedback" id="passwordError">
                                As senhas não coincidem.
                            </div>
                        </div>

                        <div class="password-requirements mb-4">
                            <p class="text-muted mb-1 small">A senha deve conter:</p>
                            <ul class="small text-muted ps-3 mb-0">
                                <li id="req-length"><span class="check-icon"><i class="far fa-circle"></i></span> Pelo
                                    menos 6 caracteres</li>
                                <li id="req-uppercase"><span class="check-icon"><i class="far fa-circle"></i></span> Uma
                                    letra maiúscula</li>
                                <li id="req-lowercase"><span class="check-icon"><i class="far fa-circle"></i></span> Uma
                                    letra minúscula</li>
                                <li id="req-number"><span class="check-icon"><i class="far fa-circle"></i></span> Um
                                    número</li>
                                <li id="req-special"><span class="check-icon"><i class="far fa-circle"></i></span> Um
                                    caractere especial</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3 pulse">
                            <span>Cadastrar</span>
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p class="mb-0">
                            Já tem uma conta? <a href="Login.php">Faça login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Particles.js para o fundo animado -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

    <!-- JavaScript personalizado -->
    <script src="Geral_cl.js"></script>
    <script src="Cadastro.js"></script>
</body>

</html>