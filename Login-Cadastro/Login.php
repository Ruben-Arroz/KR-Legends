<?php
// Ativa definições de sessão seguras antes de session_start()
ini_set('session.cookie_secure', 1);     // apenas HTTPS
ini_set('session.cookie_httponly', 1);   // JavaScript não lê
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();
require_once __DIR__ . '/../../databaseconnect.php';

$erro = "";
$login = "";
$email = $login;

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['email'] ?? '');  // Pode ser e-mail ou nome de utilizador
    $password = $_POST['password'] ?? '';

    if ($login !== '' && $password !== '') {
        // 1) Verifica se o login é um e-mail ou nome de utilizador
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $stmt = $mysqli->prepare("
        SELECT
            KR_EMAIL_USER,
            KR_USER_NAME,
            KR_PASSWORD,
            KR_NAME,
            KR_AVATAR,
            KR_LAST_LOGIN,
            KR_PREVIOUS_LOGIN,
            KR_BIO,
            KR_COUNTRY,
            KR_CITY,
            KR_DATE_OF_BIRTH,
            KR_GENERO,
            KR_BANNER,
            KR_CREATED_AT,
            KR_ROLE,
            ID,
            ROBLOX_USERNAME
        FROM KR_USERS
       WHERE KR_EMAIL_USER = ?
    ");
        } else {
            $stmt = $mysqli->prepare("
        SELECT
            KR_EMAIL_USER,
            KR_USER_NAME,
            KR_PASSWORD,
            KR_NAME,
            KR_AVATAR,
            KR_LAST_LOGIN,
            KR_PREVIOUS_LOGIN,
            KR_BIO,
            KR_COUNTRY,
            KR_CITY,
            KR_DATE_OF_BIRTH,
            KR_GENERO,
            KR_BANNER,
            KR_CREATED_AT,
            KR_ROLE,
            ID,
            ROBLOX_USERNAME
        FROM KR_USERS
       WHERE KR_USER_NAME = ?
    ");
        }


        $stmt->bind_param("s", $login);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // 2) Verifica se existe utilizador e senha está correta
        if ($user && password_verify($password, $user['KR_PASSWORD'])) {
            // Login bem-sucedido: protege a sessão
            session_regenerate_id(true);
            // Atualiza a coluna KR_LAST_LOGIN
            // Guarda o valor atual do último login (antes de atualizar)
            $previous_login = $user['KR_LAST_LOGIN'];

            // Atualiza o novo login e guarda o anterior
            $upd = $mysqli->prepare("
    UPDATE KR_USERS
       SET KR_PREVIOUS_LOGIN = ?, KR_LAST_LOGIN = NOW()
     WHERE KR_EMAIL_USER = ?
");
            $upd->bind_param('ss', $previous_login, $user['KR_EMAIL_USER']);
            $upd->execute();
            $upd->close();



            // Popula a sessão com dados do utilizador
            // regista o timestamp do login atual
            $_SESSION['login_time'] = time();

            // dados já existentes
            $_SESSION['id'] = $user['ID'];
            $_SESSION['user_email'] = $user['KR_EMAIL_USER'];
            $_SESSION['user_name'] = $user['KR_USER_NAME'];
            $_SESSION['name'] = $user['KR_NAME'];
            $_SESSION['bio'] = $user['KR_BIO'];
            $_SESSION['country'] = $user['KR_COUNTRY'];
            $_SESSION['city'] = $user['KR_CITY'];
            $_SESSION['date_of_birth'] = $user['KR_DATE_OF_BIRTH'];
            $_SESSION['genero'] = $user['KR_GENERO'];
            $_SESSION['banner'] = $user['KR_BANNER'];
            $_SESSION['user_avatar'] = $user['KR_AVATAR'] ?: 'default.png';

            $_SESSION['created_at'] = $user['KR_CREATED_AT'];  // data de criação da conta
            $_SESSION['role'] = $user['KR_ROLE'];        // papel (USER, ADM, SUPERADM)

            $_SESSION['roblox_username'] = $user['ROBLOX_USERNAME'];

            // O previous_login já foi salvo antes de atualizar
            $_SESSION['previous_login'] = $previous_login;

            // Atualizamos last_login com NOW() — então capturamos isso agora com uma nova query
            $fetchLogin = $mysqli->prepare("SELECT KR_LAST_LOGIN FROM KR_USERS WHERE KR_EMAIL_USER = ?");
            $fetchLogin->bind_param('s', $user['KR_EMAIL_USER']);
            $fetchLogin->execute();
            $resultLogin = $fetchLogin->get_result();
            if ($row = $resultLogin->fetch_assoc()) {
                $_SESSION['last_login'] = $row['KR_LAST_LOGIN'];
            }
            $fetchLogin->close();

            // Buscar redes sociais do usuário
            $socialStmt = $mysqli->prepare("
                SELECT * FROM KR_USER_SOCIALS WHERE SOCIAL_ID = ?
            ");
            $socialStmt->bind_param("i", $user['ID']);
            $socialStmt->execute();
            $socialResult = $socialStmt->get_result();

            if ($socialResult->num_rows > 0) {
                $_SESSION['socials'] = $socialResult->fetch_assoc();
            } else {
                $_SESSION['socials'] = [
                    'SOCIAL_ID' => $user['ID'],
                    'EMAIL_SOCIAL' => null,
                    'WHATSAPP' => null,
                    'YOUTUBE' => null,
                    'INSTAGRAM' => null,
                    'TIKTOK' => null,
                    'FACEBOOK' => null,
                    'LINKEDIN' => null,
                    'GITHUB' => null,
                    'TWITTER' => null
                ];
            }
            $socialStmt->close();

            // 3) Se marcou "Lembrar-me", gera token e guarda na tabela KR_USER_TOKENS
            if (!empty($_POST['remember'])) {
                // 3.1. Gera token aleatório (32 bytes em hexadecimal)
                $token = bin2hex(random_bytes(16));
                $expiry_dt = new DateTime('+30 days');
                $expiry_str = $expiry_dt->format('Y-m-d H:i:s');
                $token_hash = password_hash($token, PASSWORD_DEFAULT);

                // 3.2. Insere na tabela KR_USER_TOKENS
                $stmt2 = $mysqli->prepare("
    INSERT INTO KR_USER_TOKENS
      (TOKEN_USER_ID, TOKEN_HASH, EXPIRY, TOKEN_TYPE)
    VALUES
      (?, ?, ?, 'remember_me')
");
                $stmt2->bind_param(
                    'iss',
                    $user['ID'],      // agora usamos o ID do utilizador
                    $token_hash,      // hash do token
                    $expiry_str       // data de expiração
                );
                $stmt2->execute();
                $stmt2->close();

                // 3.3. Cria o cookie "remember_me" no browser (token puro)
                setcookie(
                    'remember_me',
                    $token,
                    time() + 60 * 60 * 24 * 30, // 30 dias
                    '/',
                    '',    // domínio (opcional; deixamos vazio para o domínio atual)
                    true,  // Secure (HTTPS apenas)
                    true   // HttpOnly (JavaScript não consegue ler)
                );
            }

            // 4) Redireciona para a página principal (Index.php)
            header("Location: https://alpha.soaresbasto.pt/~a29621/KRLegends/Index.php");
            exit;
        }

        // Credenciais inválidas
        $erro = "Credenciais inválidas.";
        sleep(1);  // Retarda resposta para dificultar ataques de força bruta
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="description" content="Acesse sua conta no KR Legends para participar da comunidade de pilotos." />

    <title>Login - KR Legends</title>

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
    <link rel="stylesheet" href="Login.css" />
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

                    <h2 class="text-center mb-4 card-title">Bem-vindo de volta</h2>

                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= htmlspecialchars($erro) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                    <?php endif; ?>

                    <form id="loginForm" action="Login.php" method="POST" class="needs-validation" novalidate>
                        <div class="form-floating mb-3">
                            <!-- Mude de tipo="email" para tipo="text" -->
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="nome@exemplo.com" value="<?= htmlspecialchars($email ?? '') ?>" required>

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
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Lembrar-me
                                </label>
                            </div>
                            <a href="#" class="forgot-password">Esqueci a senha</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3 pulse">
                            <span>Entrar</span>
                        </button>

                        <div class="separator">
                            <span>ou entre com</span>
                        </div>

                        <button type="button" class="btn btn-outline-secondary w-100 social-login">
                            <i class="fab fa-google me-2"></i>Google
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p class="mb-0">
                            Não tem uma conta? <a href="Cadastro.php">Cadastre-se</a>
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
    <script src="Login.js"></script>
</body>

</html>