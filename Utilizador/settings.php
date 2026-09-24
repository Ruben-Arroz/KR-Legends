<?php
// Exibição de erros (desative em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sessão segura
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('KRSESSION');
session_start();

// 1) Conexão MySQLi (cria $mysqli)
require_once __DIR__ . '/../../databaseconnect.php';

// 3) PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../PHPMailer-6.10.0/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-6.10.0/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-6.10.0/src/SMTP.php';

// 4) Constantes SMTP
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_USER = 'kr.legends.suport@gmail.com';
const SMTP_PASS = 'oxhz jdqr shfo zqew';

// Função para validar força da palavra-passe
function validatePasswordStrength($password)
{
    $errors = [];

    if (strlen($password) < 6) {
        $errors[] = 'A palavra-passe deve ter pelo menos 6 caracteres.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'A palavra-passe deve conter pelo menos uma letra maiúscula.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'A palavra-passe deve conter pelo menos uma letra minúscula.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'A palavra-passe deve conter pelo menos um número.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'A palavra-passe deve conter pelo menos um carácter especial.';
    }

    return $errors;
}

// Função para calcular força da palavra-passe
function calculatePasswordStrength($password)
{
    $strength = 0;

    if (strlen($password) >= 6)
        $strength++;
    if (preg_match('/[A-Z]/', $password))
        $strength++;
    if (preg_match('/[a-z]/', $password))
        $strength++;
    if (preg_match('/[0-9]/', $password))
        $strength++;
    if (preg_match('/[^A-Za-z0-9]/', $password))
        $strength++;

    return $strength;
}

/**
 * Obtém os dados do token se for válido, não expirou e não foi usado.
 *
 * @param string $token   Valor em claro (bin2hex) do token recebido.
 * @param int    $userId  ID do utilizador logado.
 * @param string $type    Tipo de token (ex. 'password_change').
 * @return array|false    Array associativo com ['id'], ['token_hash'], ['expiry'], ['new_email'] ou false se inválido.
 */
function getTokenRecord(string $token, int $userId, string $type)
{
    global $mysqli;
    $stmt = $mysqli->prepare("
        SELECT ID, TOKEN_HASH, EXPIRY, NEW_EMAIL
          FROM KR_USER_TOKENS
         WHERE TOKEN_TYPE   = ?
           AND TOKEN_USER_ID = ?
           AND EXPIRY       > NOW()
           AND USED         = 0
    ");
    $stmt->bind_param('si', $type, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        if (password_verify($token, $row['TOKEN_HASH'])) {
            $stmt->close();
            return $row;
        }
    }
    $stmt->close();
    return false;
}

// Função melhorada para envio de emails
function sendActionEmail(string $to, string $token, string $action, string $extra = ''): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setFrom(SMTP_USER, 'KR Legends Support');
        $mail->addAddress($to);
        $mail->isHTML(true);

        $linkBase = sprintf(
            'https://%s/~a29621/KRLegends/Utilizador/settings.php?token=%s&action=%s',
            $_SERVER['HTTP_HOST'],
            $token,
            $action
        );

        // Templates de email melhorados
        if ($action === 'email_change') {
            $mail->Subject = '🔐 KR Legends - Confirmação de Alteração de Email';
            $link = $linkBase . '&newEmail=' . urlencode($extra);
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #000; color: #fff; border-radius: 10px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #ffc107, #ffca2c); padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; color: #000; font-size: 28px; font-weight: bold;'>KR Legends</h1>
                    <p style='margin: 10px 0 0 0; color: #333; font-size: 16px;'>Confirmação de Alteração de Email</p>
                </div>
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #ffc107; margin-bottom: 20px;'>Olá, Piloto!</h2>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 20px;'>
                        Recebemos um pedido para alterar o seu endereço de email para:
                    </p>
                    <div style='background: #1f1f1f; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <strong style='color: #ffc107; font-size: 18px;'>{$extra}</strong>
                    </div>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 30px;'>
                        Para confirmar esta alteração, clique no botão abaixo:
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$link}' style='background: #ffc107; color: #000; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block; transition: all 0.3s ease;'>
                            ✅ Confirmar Alteração
                        </a>
                    </div>
                    <div style='background: #2d2d2d; padding: 20px; border-radius: 8px; margin-top: 30px;'>
                        <p style='margin: 0; font-size: 14px; color: #ccc;'>
                            <strong>⚠️ Importante:</strong> Se não solicitou esta alteração, ignore este email. 
                            O seu email atual permanecerá inalterado.
                        </p>
                    </div>
                </div>
                <div style='background: #1f1f1f; padding: 20px; text-align: center; border-top: 1px solid #333;'>
                    <p style='margin: 0; font-size: 12px; color: #888;'>
                        © 2025 KR Legends. Todos os direitos reservados.<br>
                        Este email foi enviado automaticamente. Não responda a este email.
                    </p>
                </div>
            </div>";
        } elseif ($action === 'email_change_new') {
            $mail->Subject = '🔐 KR Legends - Confirmação de Novo Email';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #000; color: #fff; border-radius: 10px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #ffc107, #ffca2c); padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; color: #000; font-size: 28px; font-weight: bold;'>KR Legends</h1>
                    <p style='margin: 10px 0 0 0; color: #333; font-size: 16px;'>Confirmação de Novo Email</p>
                </div>
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #ffc107; margin-bottom: 20px;'>Bem-vindo, Piloto!</h2>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 20px;'>
                        Este email foi adicionado como novo endereço da sua conta KR Legends.
                    </p>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 30px;'>
                        Para confirmar que este email lhe pertence, clique no botão abaixo:
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$linkBase}' style='background: #ffc107; color: #000; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                            ✅ Confirmar Novo Email
                        </a>
                    </div>
                    <div style='background: #2d2d2d; padding: 20px; border-radius: 8px; margin-top: 30px;'>
                        <p style='margin: 0; font-size: 14px; color: #ccc;'>
                            <strong>⚠️ Importante:</strong> Se não reconhece esta ação, contacte imediatamente o suporte.
                        </p>
                    </div>
                </div>
                <div style='background: #1f1f1f; padding: 20px; text-align: center; border-top: 1px solid #333;'>
                    <p style='margin: 0; font-size: 12px; color: #888;'>
                        © 2025 KR Legends. Todos os direitos reservados.<br>
                        Este email foi enviado automaticamente. Não responda a este email.
                    </p>
                </div>
            </div>";
        } elseif ($action === 'account_delete') {
            $mail->Subject = '⚠️ KR Legends - Confirmação de Eliminação de Conta';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #000; color: #fff; border-radius: 10px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #dc3545, #c82333); padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; color: #fff; font-size: 28px; font-weight: bold;'>KR Legends</h1>
                    <p style='margin: 10px 0 0 0; color: #ffebee; font-size: 16px;'>Confirmação de Eliminação de Conta</p>
                </div>
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #dc3545; margin-bottom: 20px;'>⚠️ Atenção, Piloto!</h2>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 20px;'>
                        Recebemos um pedido para <strong style='color: #dc3545;'>eliminar permanentemente</strong> a sua conta KR Legends.
                    </p>
                    <div style='background: #2d1b1b; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                        <p style='margin: 0; color: #ffcdd2; font-size: 14px;'>
                            <strong>Esta ação é irreversível!</strong><br>
                            • Todos os seus dados serão eliminados<br>
                            • O seu progresso será perdido<br>
                            • Não será possível recuperar a conta
                        </p>
                    </div>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 30px;'>
                        Se tem a certeza de que deseja continuar, clique no botão abaixo:
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$linkBase}' style='background: #dc3545; color: #fff; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                            🗑️ Eliminar Conta Permanentemente
                        </a>
                    </div>
                    <div style='background: #2d2d2d; padding: 20px; border-radius: 8px; margin-top: 30px;'>
                        <p style='margin: 0; font-size: 14px; color: #ccc;'>
                            <strong>💡 Mudou de ideias?</strong> Se não solicitou esta ação, ignore este email. 
                            A sua conta permanecerá ativa e segura.
                        </p>
                    </div>
                </div>
                <div style='background: #1f1f1f; padding: 20px; text-align: center; border-top: 1px solid #333;'>
                    <p style='margin: 0; font-size: 12px; color: #888;'>
                        © 2025 KR Legends. Todos os direitos reservados.<br>
                        Este email foi enviado automaticamente. Não responda a este email.
                    </p>
                </div>
            </div>";
        } else {
            // password_change
            $mail->Subject = '🔑 KR Legends - Confirmação de Alteração de Palavra-passe';
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #000; color: #fff; border-radius: 10px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #ffc107, #ffca2c); padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; color: #000; font-size: 28px; font-weight: bold;'>KR Legends</h1>
                    <p style='margin: 10px 0 0 0; color: #333; font-size: 16px;'>Confirmação de Alteração de Palavra-passe</p>
                </div>
                <div style='padding: 40px 30px;'>
                    <h2 style='color: #ffc107; margin-bottom: 20px;'>🔑 Olá, Piloto!</h2>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 20px;'>
                        Recebemos um pedido para alterar a palavra-passe da sua conta KR Legends.
                    </p>
                    <div style='background: #1f1f1f; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                        <p style='margin: 0; color: #ffc107; font-size: 14px;'>
                            <strong>🛡️ Dicas de Segurança:</strong><br>
                            • Use pelo menos 6 caracteres<br>
                            • Inclua letras maiúsculas e minúsculas<br>
                            • Adicione números e símbolos<br>
                            • Evite informações pessoais
                        </p>
                    </div>
                    <p style='font-size: 16px; line-height: 1.6; margin-bottom: 30px;'>
                        Para continuar com a alteração, clique no botão abaixo:
                    </p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$linkBase}' style='background: #ffc107; color: #000; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                            🔐 Alterar Palavra-passe
                        </a>
                    </div>
                    <div style='background: #2d2d2d; padding: 20px; border-radius: 8px; margin-top: 30px;'>
                        <p style='margin: 0; font-size: 14px; color: #ccc;'>
                            <strong>⚠️ Importante:</strong> Se não solicitou esta alteração, ignore este email. 
                            A sua palavra-passe atual permanecerá inalterada.
                        </p>
                    </div>
                </div>
                <div style='background: #1f1f1f; padding: 20px; text-align: center; border-top: 1px solid #333;'>
                    <p style='margin: 0; font-size: 12px; color: #888;'>
                        © 2025 KR Legends. Todos os direitos reservados.<br>
                        Este email foi enviado automaticamente. Não responda a este email.
                    </p>
                </div>
            </div>";
        }

        error_log("[EMAIL][{$action}] → Iniciando envio para {$to}; Token={$token}");
        if (!$mail->send()) {
            error_log("[EMAIL][{$action}] ✖ Falha no envio para {$to}: " . $mail->ErrorInfo);
            return false;
        }
        error_log("[EMAIL][{$action}] ✔ Enviado com sucesso para {$to}");
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

// 5) Processamento de formulários
$status = '';
$statusType = '';
$userId = $_SESSION['id'];
$confirmedPassword = false;
$accountDeleted = false;
$showEmailModal = false;
$showDeleteModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação CSRF (adicionar token se necessário)

    // Alteração de palavra-passe com confirmação por email
    if (isset($_POST['request_password_change'])) {
        $action = 'password_change';
        $token = bin2hex(random_bytes(32));
        $expiry = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $tokenHash = password_hash($token, PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("
            INSERT INTO KR_USER_TOKENS (TOKEN_HASH, EXPIRY, TOKEN_TYPE, TOKEN_USER_ID)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('sssi', $tokenHash, $expiry, $action, $userId);
        if ($stmt->execute()) {
            $sent = sendActionEmail($_SESSION['user_email'], $token, $action);
            if ($sent) {
                $status = 'Email de confirmação enviado com sucesso! Verifique a sua caixa de entrada.';
                $statusType = 'success';
            } else {
                $status = 'Erro ao enviar email. Tente novamente mais tarde.';
                $statusType = 'error';
            }
        } else {
            $status = 'Erro interno. Contacte o suporte.';
            $statusType = 'error';
        }
        $stmt->close();
    }

    // Alteração de palavra-passe após confirmação
    elseif (
        isset($_POST['change_password'], $_POST['new_password'], $_POST['confirm_password'], $_POST['token'])
    ) {
        $token = $_POST['token'];
        $newPass = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        // 1) Validar token
        $tokenData = getTokenRecord($token, $userId, 'password_change');
        if (!$tokenData) {
            $status = 'Link inválido ou expirado. Solicite um novo link.';
            $statusType = 'error';
            // saiu do if/elseif, não há break
        }
        // apenas prossegue se não tivermos erro
        elseif (true) {
            // 2) Validar força e matching
            $errors = validatePasswordStrength($newPass);
            if ($newPass !== $confirm) {
                $errors[] = 'As palavras‑passe não coincidem.';
            }
            if (!empty($errors)) {
                $status = implode('<br>', $errors);
                $statusType = 'error';
            }
            // só prossegue se tudo válido
            elseif (true) {
                // 3) Atualizar password
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $upd = $mysqli->prepare("UPDATE KR_USERS SET KR_PASSWORD = ? WHERE ID = ?");
                $upd->bind_param('si', $hashed, $userId);
                if (!$upd->execute()) {
                    $status = 'Erro ao alterar palavra‑passe. Tente novamente.';
                    $statusType = 'error';
                } else {
                    // 4) Marcar token como usado
                    $mark = $mysqli->prepare("UPDATE KR_USER_TOKENS SET USED = 1 WHERE ID = ?");
                    $mark->bind_param('i', $tokenData['ID']);
                    $mark->execute();
                    $mark->close();

                    $status = 'Palavra‑passe alterada com sucesso!';
                    $statusType = 'success';
                    $confirmedPassword = false;
                }
                $upd->close();
            }
        }
    }

    // Alteração de email - Passo 1: Validar palavra-passe atual
    elseif (isset($_POST['validate_email_change'], $_POST['current_password'], $_POST['new_email'])) {
        $currentPassword = $_POST['current_password'];
        $newEmail = trim($_POST['new_email']);

        // Verificar palavra-passe atual
        $stmt = $mysqli->prepare("SELECT KR_PASSWORD FROM KR_USERS WHERE ID = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($currentPassword, $user['KR_PASSWORD'])) {
            $status = 'Palavra-passe atual incorreta.';
            $statusType = 'error';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $status = 'Por favor, insira um email válido.';
            $statusType = 'error';
        } elseif ($newEmail === $_SESSION['user_email']) {
            $status = 'O novo email deve ser diferente do atual.';
            $statusType = 'error';
        } else {
            // Verificar se email já existe
            $checkStmt = $mysqli->prepare("SELECT ID FROM KR_USERS WHERE KR_EMAIL_USER = ? AND ID != ?");
            $checkStmt->bind_param('si', $newEmail, $userId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $status = 'Este email já está em uso por outra conta.';
                $statusType = 'error';
            } else {
                // Criar tokens para ambos os emails
                $action1 = 'email_change';
                $action2 = 'email_change_new';
                $token1 = bin2hex(random_bytes(32));
                $token2 = bin2hex(random_bytes(32));
                $expiry = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
                $tokenHash1 = password_hash($token1, PASSWORD_DEFAULT);
                $tokenHash2 = password_hash($token2, PASSWORD_DEFAULT);

                // Inserir tokens na base de dados
                $stmt1 = $mysqli->prepare("
                    INSERT INTO KR_USER_TOKENS (TOKEN_HASH, EXPIRY, TOKEN_TYPE, TOKEN_USER_ID, NEW_EMAIL)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt1->bind_param('sssis', $tokenHash1, $expiry, $action1, $userId, $newEmail);

                $stmt2 = $mysqli->prepare("
                    INSERT INTO KR_USER_TOKENS (TOKEN_HASH, EXPIRY, TOKEN_TYPE, TOKEN_USER_ID, NEW_EMAIL)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt2->bind_param('sssis', $tokenHash2, $expiry, $action2, $userId, $newEmail);

                if ($stmt1->execute() && $stmt2->execute()) {
                    // Enviar emails para ambos os endereços
                    $sent1 = sendActionEmail($_SESSION['user_email'], $token1, $action1, $newEmail);
                    $sent2 = sendActionEmail($newEmail, $token2, $action2);

                    if ($sent1 && $sent2) {
                        $status = 'Emails de confirmação enviados para ambos os endereços. Confirme em ambos para completar a alteração.';
                        $statusType = 'success';
                    } else {
                        $status = 'Erro ao enviar emails. Tente novamente mais tarde.';
                        $statusType = 'error';
                    }
                } else {
                    $status = 'Erro interno. Contacte o suporte.';
                    $statusType = 'error';
                }
                $stmt1->close();
                $stmt2->close();
            }
            $checkStmt->close();
        }
    }

    // Eliminação de conta - Passo 1: Validar palavra-passe
    elseif (isset($_POST['validate_account_delete'], $_POST['current_password_delete'])) {
        $currentPassword = $_POST['current_password_delete'];

        // Verificar palavra-passe atual
        $stmt = $mysqli->prepare("SELECT KR_PASSWORD FROM KR_USERS WHERE ID = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($currentPassword, $user['KR_PASSWORD'])) {
            $status = 'Palavra-passe incorreta.';
            $statusType = 'error';
        } else {
            $action = 'account_delete';
            $token = bin2hex(random_bytes(32));
            $expiry = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("
                INSERT INTO KR_USER_TOKENS (TOKEN_HASH, EXPIRY, TOKEN_TYPE, TOKEN_USER_ID)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param('sssi', $tokenHash, $expiry, $action, $userId);
            if ($stmt->execute()) {
                $sent = sendActionEmail($_SESSION['user_email'], $token, $action);
                if ($sent) {
                    $status = 'Email de confirmação enviado. Verifique a sua caixa de entrada para confirmar a eliminação da conta.';
                    $statusType = 'warning';
                } else {
                    $status = 'Erro ao enviar email. Tente novamente mais tarde.';
                    $statusType = 'error';
                }
            } else {
                $status = 'Erro interno. Contacte o suporte.';
                $statusType = 'error';
            }
            $stmt->close();
        }
    }
}

// 6) Validação de token e execução de ação (GET handlers)
$confirmToken = $_GET['token'] ?? null;
$action = $_GET['action'] ?? null;

if ($confirmToken && in_array($action, ['password_change', 'account_delete', 'email_change', 'email_change_new'], true)) {
    $stmt = $mysqli->prepare("
        SELECT TOKEN_HASH, EXPIRY, NEW_EMAIL
          FROM KR_USER_TOKENS
         WHERE TOKEN_TYPE = ?
           AND TOKEN_USER_ID = ?
           AND EXPIRY > NOW()
           AND USED = 0
    ");
    $stmt->bind_param('si', $action, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $valid = false;
    $tokenData = null;

    while ($row = $res->fetch_assoc()) {
        if (password_verify($confirmToken, $row['TOKEN_HASH'])) {
            $valid = true;
            $tokenData = $row;
            break;
        }
    }
    $stmt->close();

    if ($valid) {
        if ($action === 'password_change') {
            $confirmedPassword = true;
            $status = 'Email confirmado! Agora pode definir a sua nova palavra-passe.';
            $statusType = 'success';
        } elseif ($action === 'email_change') {
            $newEmail = $tokenData['NEW_EMAIL'];
            if ($newEmail) {
                // Verificar se ambos os tokens foram confirmados
                $checkBothTokens = $mysqli->prepare("
                    SELECT COUNT(*) as confirmed_count 
                    FROM KR_USER_TOKENS 
                    WHERE TOKEN_USER_ID = ? 
                    AND TOKEN_TYPE IN ('email_change', 'email_change_new')
                    AND NEW_EMAIL = ?
                    AND EXPIRY > NOW()
                    AND USED = 0
                ");
                $checkBothTokens->bind_param('is', $userId, $newEmail);
                $checkBothTokens->execute();
                $tokenResult = $checkBothTokens->get_result()->fetch_assoc();
                $checkBothTokens->close();

                if ($tokenResult['confirmed_count'] >= 2) {
                    $upd = $mysqli->prepare("UPDATE KR_USERS SET KR_EMAIL_USER = ? WHERE ID = ?");
                    $upd->bind_param('si', $newEmail, $userId);
                    if ($upd->execute()) {
                        $_SESSION['user_email'] = $newEmail;

                        // Marcar tokens como usados
                        $markUsed = $mysqli->prepare("UPDATE KR_USER_TOKENS SET USED = 1 WHERE TOKEN_USER_ID = ? AND TOKEN_TYPE IN ('email_change', 'email_change_new') AND NEW_EMAIL = ?");
                        $markUsed->bind_param('is', $userId, $newEmail);
                        $markUsed->execute();
                        $markUsed->close();

                        $status = 'Email alterado com sucesso!';
                        $statusType = 'success';
                    } else {
                        $status = 'Erro ao alterar email.';
                        $statusType = 'error';
                    }
                    $upd->close();
                } else {
                    $status = 'Aguardando confirmação em ambos os emails.';
                    $statusType = 'warning';
                }
            } else {
                $status = 'Email inválido.';
                $statusType = 'error';
            }
        } elseif ($action === 'email_change_new') {
            $status = 'Novo email confirmado! Aguardando confirmação no email atual.';
            $statusType = 'success';
        } elseif ($action === 'account_delete') {
            // Apagar ficheiros
            $avatar = __DIR__ . "/../Imagens/avatares/avatar_{$userId}.*";
            $banner = __DIR__ . "/../Imagens/banners/banner_{$userId}.*";
            foreach (glob($avatar) as $f)
                unlink($f);
            foreach (glob($banner) as $f)
                unlink($f);

            // Apagar conta e tokens
            $del1 = $mysqli->prepare("DELETE FROM KR_USER_TOKENS WHERE TOKEN_USER_ID = ?");
            $del1->bind_param('i', $userId);
            $del1->execute();
            $del1->close();

            $del2 = $mysqli->prepare("DELETE FROM KR_USERS WHERE ID = ?");
            $del2->bind_param('i', $userId);
            $del2->execute();
            $del2->close();

            $accountDeleted = true;
            $status = 'Conta eliminada com sucesso. Será redirecionado em 20 segundos...';
            $statusType = 'success';
        }

        // Marcar token como usado (exceto para account_delete que já foi eliminado)
        if ($action !== 'account_delete') {
            $markUsed = $mysqli->prepare("UPDATE KR_USER_TOKENS SET USED = 1 WHERE TOKEN_HASH = ?");
            $markUsed->bind_param('s', $tokenData['TOKEN_HASH']);
            $markUsed->execute();
            $markUsed->close();
        }
    } else {
        $status = 'Link inválido ou expirado. Solicite um novo link.';
        $statusType = 'error';
    }
}

// Autenticação
if (!isset($_SESSION['id'])) {
    header("Location: /~a29621/KRLegends/Utilizador/perfil.php");
    exit();
}

// Se a conta foi eliminada, não mostrar o resto da página
if ($accountDeleted) {
    session_destroy();
    ?>
    <!DOCTYPE html>
    <html lang="pt-pt">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>KR Legends - Conta Eliminada</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../css/Geral.css" rel="stylesheet">
        <style>
            body {
                background: #000;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                font-family: Arial, sans-serif;
            }

            .deletion-container {
                text-align: center;
                max-width: 500px;
                padding: 2rem;
                background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
                border-radius: 15px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
                border: 1px solid #333;
            }

            .deletion-icon {
                font-size: 4rem;
                color: #dc3545;
                margin-bottom: 1rem;
            }

            .deletion-title {
                color: #ffc107;
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 1rem;
            }

            .deletion-message {
                color: #ccc;
                font-size: 1.1rem;
                line-height: 1.6;
                margin-bottom: 2rem;
            }

            .countdown {
                font-size: 1.5rem;
                color: #ffc107;
                font-weight: bold;
            }

            .progress-bar {
                width: 100%;
                height: 8px;
                background: #333;
                border-radius: 4px;
                overflow: hidden;
                margin-top: 1rem;
            }

            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #dc3545, #ffc107);
                width: 100%;
                animation: countdown 20s linear forwards;
            }

            @keyframes countdown {
                from {
                    width: 100%;
                }

                to {
                    width: 0%;
                }
            }
        </style>
    </head>

    <body>
        <div class="deletion-container">
            <div class="deletion-icon">🗑️</div>
            <h1 class="deletion-title">Conta Eliminada</h1>
            <p class="deletion-message">
                A sua conta KR Legends foi eliminada com sucesso.<br>
                Obrigado por ter feito parte da nossa comunidade.
            </p>
            <div class="countdown" id="countdown">20</div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p style="margin-top: 1rem; color: #888; font-size: 0.9rem;">
                Será redirecionado automaticamente...
            </p>
        </div>

        <script>
            let timeLeft = 20;
            const countdownElement = document.getElementById('countdown');

            const timer = setInterval(() => {
                timeLeft--;
                countdownElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(timer);
                    window.location.href = '../PHP/logout.php';
                }
            }, 1000);
        </script>
    </body>

    </html>
    <?php
    exit();
}

// Dados de sessão
$userEmail = $_SESSION['user_email'];
$userRole = $_SESSION['role'];
$userName = $_SESSION['name'] ?? $_SESSION['user_name'];
$userAvatar = $_SESSION['user_avatar'] ?? 'default.png';
$lastLogin = $_SESSION['last_login'] ?? 'Nunca';
$memberSince = $_SESSION['created_at'] ?? 'Desconhecido';

// Buscar estatísticas do utilizador
$statsStmt = $mysqli->prepare("
    SELECT 
        (SELECT COUNT(*) FROM KR_USER_TOKENS WHERE TOKEN_USER_ID = ? AND USED = 0) as active_tokens,
        (SELECT KR_LAST_LOGIN FROM KR_USERS WHERE ID = ?) as last_login_db
    FROM DUAL
");
$statsStmt->bind_param('ii', $userId, $userId);
$statsStmt->execute();
$stats = $statsStmt->get_result()->fetch_assoc();
$statsStmt->close();
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Favicon padrão (navegadores desktop) -->
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="shortcut icon" href="favicon_io/favicon.ico">

    <!-- Favicon para Android -->
    <link rel="icon" type="image/png" sizes="192x192" href="../favicon_io/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../favicon_io/android-chrome-512x512.png">

    <!-- Favicon para iOS (iPhone, iPad) -->
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">

    <!-- Manifesto para apps web em Android -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">

    <title>KR Legends - Definições</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="../css/Geral.css" rel="stylesheet">
    <link href="../css/cookieConsent.css" rel="stylesheet">
    <link href="../css/includes/profileMenu.css" rel="stylesheet">
    <link href="../css/includes/modal-logout.css" rel="stylesheet">
    <link href="CSS/settings.css" rel="stylesheet">
    <link href="CSS/settings-responsive.css" rel="stylesheet">
</head>

<body>

    <!-- ========== MINI CARRINHO (SLIDE) ========== -->
    <div class="mini-cart" id="miniCart">
        <div class="mini-cart-header">
            <h3>Carrinho de Compras</h3>
            <button class="mini-cart-close" id="closeMiniCart">
                <!-- Ícone de fechar (SVG) -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="mini-cart-items" id="miniCartItems">
            <!-- Produtos adicionados serão injetados dinamicamente aqui -->
        </div>
        <div class="mini-cart-footer">
            <div class="mini-cart-subtotal">
                <span>Subtotal:</span>
                <span id="cartSubtotal">€0.00</span>
            </div>
            <button class="btn btn-primary btn-block">Finalizar Compra</button>
        </div>
    </div>

    <!-- Modal Confirmar Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog logout-modal-dialog">
            <div class="modal-content logout-modal-content logout-modal-border">
                <div class="modal-header logout-modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmar Logout</h5>
                    <button type="button" class="btn-close btn-close-white logout-btn-close-white"
                        data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body logout-modal-body">
                    Tem certeza de que deseja terminar a sessão?
                </div>
                <div class="modal-footer logout-modal-footer">
                    <!-- Botão Cancelar -->
                    <button type="button" class="btn btn-outline-light logout-btn-cancel"
                        data-bs-dismiss="modal">Cancelar</button>

                    <!-- Formulário para confirmar o logout -->
                    <form action="../PHP/logout.php" method="POST" style="display:inline-block;">
                        <button type="submit" class="btn logout-btn-confirm-logout">Terminar Sessão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alteração de Email -->
    <div class="modal fade" id="emailChangeModal" tabindex="-1" aria-labelledby="emailChangeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailChangeModalLabel">
                        <i class="bi bi-envelope-check me-2"></i>
                        Alterar Email
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>
                <form method="POST" id="emailChangeModalForm">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Importante:</strong> Será necessário confirmar em ambos os emails (atual e novo)
                            para completar a alteração.
                        </div>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Palavra-passe Atual</label>
                            <input type="password" class="form-control" id="current_password" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="new_email_modal" class="form-label">Novo Email</label>
                            <input type="email" class="form-control" id="new_email_modal" name="new_email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="validate_email_change" class="btn btn-primary">
                            <i class="bi bi-envelope-check me-2"></i>
                            Enviar Confirmação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminação de Conta -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="deleteAccountModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Eliminar Conta Permanentemente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>
                <form method="POST" id="deleteAccountModalForm">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ATENÇÃO: Esta ação é irreversível!
                            </h6>
                            <hr>
                            <ul class="mb-0">
                                <li>Todos os seus dados serão eliminados permanentemente</li>
                                <li>O seu progresso será perdido</li>
                                <li>Não será possível recuperar a conta</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <label for="current_password_delete" class="form-label">
                                Confirme a sua palavra-passe para continuar:
                            </label>
                            <input type="password" class="form-control" id="current_password_delete"
                                name="current_password_delete" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label text-danger" for="confirmDelete">
                                Compreendo que esta ação é irreversível e elimina permanentemente a minha conta.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="validate_account_delete" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>
                            Enviar Confirmação por Email
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sobreposição visual quando o mini carrinho estiver ativo -->
    <div class="overlay" id="overlay"></div>
    <!-- Header -->
    <header class="fixed-top bg-black bg-opacity-90 border-bottom border-warning border-opacity-25">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" class="logo" href="../Index.php">
                    <img src="../Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends"
                        height="70">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">

                    <ul class="navbar-nav me-auto">

                        <!-- Sobre Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="sobreDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i> Sobre
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="sobreDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Index.php">
                                        <i class="bi bi-house-door me-2" aria-hidden="true"></i> Início
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../PAP.php">
                                        <i class="bi bi-journal-text me-2" aria-hidden="true"></i> PAP
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Equipa.php">
                                        <i class="bi bi-people me-2" aria-hidden="true"></i> Equipa
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Creditos.php">
                                        <i class="bi bi-award me-2" aria-hidden="true"></i> Créditos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Politica.php">
                                        <i class="fas fa-vote-yea me-2" aria-hidden="true"></i> Politica
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Comunidade & Social Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                                id="comSocialDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-people-fill me-1" aria-hidden="true"></i> Comunidade & Social
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="comSocialDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Comunidade.php">
                                        <i class="bi bi-people me-2" aria-hidden="true"></i> Comunidade
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Suporte.php">
                                        <i class="bi bi-life-preserver me-2" aria-hidden="true"></i> Suporte
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider bg-secondary" />
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.instagram.com/kr_legends.oficial/" target="_blank">
                                        <i class="bi bi-instagram me-2" aria-hidden="true"></i> Instagram
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="#" target="_blank">
                                        <i class="bi bi-discord me-2" aria-hidden="true"></i> Discord
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.tiktok.com/@kr.legends?lang=pt-BR" target="_blank">
                                        <i class="bi bi-tiktok me-2" aria-hidden="true"></i> TikTok
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center"
                                        href="https://www.youtube.com/@KRLegends-media" target="_blank">
                                        <i class="bi bi-youtube me-2" aria-hidden="true"></i> YouTube
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Jogo Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="jogoDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-controller me-1" aria-hidden="true"></i> Jogo
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="jogoDropdown">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Atualizacoes.php">
                                        <i class="bi bi-bell me-2" aria-hidden="true"></i> Atualizações
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../modos-de-jogo.php">
                                        <i class="bi bi-joystick me-2" aria-hidden="true"></i> Modos de Jogo
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Rankings.php">
                                        <i class="bi bi-bar-chart-line me-2" aria-hidden="true"></i> Rankings
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="../Galeria.php">
                                        <i class="bi bi-images me-2" aria-hidden="true"></i> Galeria
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Loja -->
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="../Store.php">
                                <i class="bi bi-shop me-1" aria-hidden="true"></i> Loja Online
                            </a>
                        </li>
                    </ul>

                    <div class="language-selector">
                        <select id="languageSelector" class="language-select" onchange="setLanguage(this.value)">
                            <option value="pt">🇵🇹 Português</option>
                            <option value="en">🇬🇧 English</option>
                            <option value="es">🇪🇸 Español</option>
                            <option value="fr">🇫🇷 Français</option>
                        </select>
                    </div>

                    <!-- Botões de autenticação e carrinho -->
                    <div class="auth-buttons">
                        <div class="profile-menu-container">
                            <!-- User profile name with link to profile page -->
                            <a href="perfil.php" class="profile-name" title="Ver perfil">
                                <?php
                                // Verifique se o nome completo existe, caso contrário mostre o nome de usuário
                                $displayName = !empty($_SESSION['name']) ? $_SESSION['name'] : $_SESSION['user_name'];
                                echo htmlspecialchars($displayName);
                                ?>
                            </a>

                            <!-- Avatar dropdown trigger -->
                            <div class="dropdown">
                                <a class="profile-avatar-trigger" href="#" id="userMenu" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false" title="Menu do utilizador">
                                    <img src="../Imagens/avatares/<?= htmlspecialchars($_SESSION['user_avatar']) ?>"
                                        alt="Avatar do utilizador" class="rounded-circle">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end"
                                    aria-labelledby="userMenu">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="perfil.php">
                                            <i class="bi bi-person-circle me-2"></i> Perfil
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="stats.php">
                                            <i class="bi bi-bar-chart-line me-2"></i> Estatísticas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="#">
                                            <i class="bi bi-gear me-2"></i> Definições
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="../Suporte.php">
                                            <i class="bi bi-question-circle me-2"></i> Ajuda & FAQ
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center" href="../Politica.php">
                                            <i class="bi bi-file-earmark-text me-2"></i> Termos & Políticas
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button class="dropdown-item d-flex align-items-center text-danger"
                                            data-bs-toggle="modal" data-bs-target="#logoutModal" id="terminar">
                                            <i class="bi bi-box-arrow-right me-2"></i> Terminar Sessão
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <!-- Ícone Administrativo (ADM ou SUPERADM) -->
                            <?php
                            if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['ADM', 'SUPERADM'], true)):
                                if ($_SESSION['role'] === 'SUPERADM') {
                                    // Usa ícone de “gema” para SUPERADM (fa-solid fa-gem)
                                    $iconClass = 'fa-solid fa-gem';
                                    $ariaLabel = 'Área Super Admin';
                                } else {
                                    // Usa ícone de “pessoa com gravata” para ADM (fa-solid fa-user-tie)
                                    $iconClass = 'fa-solid fa-user-tie';
                                    $ariaLabel = 'Área Admin';
                                }
                                // Link placeholder para a página administrativa
                                $adminUrl = '../admin/modoADM.php';
                                ?>
                                <a href="<?= htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8') ?>" class="profile-admin-icon"
                                    title="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>"
                                    aria-label="<?= htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') ?>">
                                    <i class="<?= $iconClass ?>" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <button class="cart-button" id="openMiniCart">
                            <div class="cart-icon-wrapper">
                                <i class="bi bi-cart3"></i>
                                <span class="cart-count" id="cartCount">0</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="sttgs-main">
        <div class="container-fluid">
            <!-- Header da página -->
            <div class="sttgs-page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="sttgs-page-title">
                            <i class="bi bi-gear-fill me-3"></i>
                            Definições da Conta
                        </h1>
                        <p class="sttgs-page-subtitle">
                            Gerencie as suas preferências e configurações de segurança
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="sttgs-btn-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Terminar Sessão
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informações do utilizador -->
            <div class="sttgs-user-info">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        <div class="sttgs-avatar-section">
                            <img src="../Imagens/avatares/<?= htmlspecialchars($userAvatar) ?>" alt="Avatar"
                                class="sttgs-avatar">
                            <div class="sttgs-user-badge sttgs-badge-<?= strtolower($userRole) ?>">
                                <?= htmlspecialchars($userRole) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="sttgs-user-details">
                            <h2 class="sttgs-user-name"><?= htmlspecialchars($userName) ?></h2>
                            <div class="sttgs-user-meta">
                                <div class="sttgs-meta-item">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span><?= htmlspecialchars($userEmail) ?></span>
                                </div>
                                <div class="sttgs-meta-item">
                                    <i class="bi bi-calendar-check"></i>
                                    <span>Membro desde: <?= date('d/m/Y', strtotime($memberSince)) ?></span>
                                </div>
                                <div class="sttgs-meta-item">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Último acesso:
                                        <?= $lastLogin !== 'Nunca' ? date('d/m/Y H:i', strtotime($lastLogin)) : 'Nunca' ?></span>
                                </div>
                                <div class="sttgs-meta-item">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Tokens ativos: <?= $stats['active_tokens'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas de status -->
            <?php if (!empty($status)): ?>
                <div class="sttgs-alert sttgs-alert-<?= $statusType ?>">
                    <div class="sttgs-alert-content">
                        <i
                            class="bi bi-<?= $statusType === 'success' ? 'check-circle' : ($statusType === 'error' ? 'exclamation-triangle' : 'info-circle') ?>"></i>
                        <div class="sttgs-alert-text"><?= $status ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Coluna principal -->
                <div class="col-lg-8">
                    <!-- Segurança da Conta -->
                    <div class="sttgs-section">
                        <div class="sttgs-section-header">
                            <h3><i class="bi bi-shield-lock me-2"></i>Segurança da Conta</h3>
                            <p>Mantenha a sua conta segura com palavras-passe fortes e verificação em duas etapas</p>
                        </div>

                        <!-- Alteração de palavra-passe -->
                        <?php if ($confirmedPassword): ?>
                            <div class="sttgs-card sttgs-card-highlighted">
                                <div class="sttgs-card-header">
                                    <i class="bi bi-key-fill me-2"></i>
                                    Definir Nova Palavra-passe
                                </div>
                                <div class="sttgs-card-body">
                                    <form method="POST" id="passwordChangeForm">
                                        <input type="hidden" name="action" value="password_change">
                                        <input type="hidden" name="token"
                                            value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="sttgs-form-group">
                                                    <label for="new_password" class="sttgs-label">Nova Palavra-passe</label>
                                                    <div class="sttgs-input-group">
                                                        <input type="password" id="new_password" name="new_password"
                                                            class="sttgs-input" required>
                                                        <button type="button" class="sttgs-input-toggle"
                                                            onclick="togglePassword('new_password')">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="sttgs-password-strength">
                                                        <div class="sttgs-strength-bar">
                                                            <div class="sttgs-strength-fill" id="strengthBar"></div>
                                                        </div>
                                                        <div class="sttgs-strength-text" id="strengthText">Força da senha:
                                                            Não definida</div>
                                                    </div>
                                                    <div class="sttgs-password-requirements">
                                                        <div class="sttgs-requirement" id="req-length">
                                                            <i class="bi bi-circle"></i> Pelo menos 6 caracteres
                                                        </div>
                                                        <div class="sttgs-requirement" id="req-uppercase">
                                                            <i class="bi bi-circle"></i> Uma letra maiúscula
                                                        </div>
                                                        <div class="sttgs-requirement" id="req-lowercase">
                                                            <i class="bi bi-circle"></i> Uma letra minúscula
                                                        </div>
                                                        <div class="sttgs-requirement" id="req-number">
                                                            <i class="bi bi-circle"></i> Um número
                                                        </div>
                                                        <div class="sttgs-requirement" id="req-special">
                                                            <i class="bi bi-circle"></i> Um carácter especial
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="sttgs-form-group">
                                                    <label for="confirm_password" class="sttgs-label">Confirmar
                                                        Palavra-passe</label>
                                                    <div class="sttgs-input-group">
                                                        <input type="password" id="confirm_password" name="confirm_password"
                                                            class="sttgs-input" required>
                                                        <button type="button" class="sttgs-input-toggle"
                                                            onclick="togglePassword('confirm_password')">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div class="sttgs-password-match" id="passwordMatch"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sttgs-form-actions">
                                            <button type="submit" name="change_password" class="sttgs-btn-primary"
                                                id="submitPasswordBtn" disabled>
                                                <i class="bi bi-check-circle me-2"></i>
                                                Alterar Palavra-passe
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="sttgs-card">
                                <div class="sttgs-card-header">
                                    <i class="bi bi-key me-2"></i>
                                    Palavra-passe
                                </div>
                                <div class="sttgs-card-body">
                                    <p class="sttgs-card-description">
                                        Altere a sua palavra-passe regularmente para manter a conta segura.
                                    </p>
                                    <form method="POST">
                                        <button type="submit" name="request_password_change" class="sttgs-btn-primary">
                                            <i class="bi bi-envelope me-2"></i>
                                            Solicitar Alteração por Email
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Alteração de email -->
                        <div class="sttgs-card">
                            <div class="sttgs-card-header">
                                <i class="bi bi-envelope me-2"></i>
                                Endereço de Email
                            </div>
                            <div class="sttgs-card-body">
                                <p class="sttgs-card-description">
                                    O seu email atual é usado para login e comunicações importantes. Para alterar o
                                    email,
                                    será necessário confirmar em ambos os endereços (atual e novo).
                                </p>
                                <div class="sttgs-current-email">
                                    <strong>Email atual:</strong> <?= htmlspecialchars($userEmail) ?>
                                </div>
                                <div class="sttgs-form-actions">
                                    <button type="button" class="sttgs-btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#emailChangeModal">
                                        <i class="bi bi-envelope-check me-2"></i>
                                        Alterar Email
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestão de Conta -->
                    <div class="sttgs-section">
                        <div class="sttgs-section-header">
                            <h3><i class="bi bi-person-gear me-2"></i>Gestão de Conta</h3>
                            <p>Opções avançadas para gestão da sua conta</p>
                        </div>

                        <!-- Eliminação de conta -->
                        <div class="sttgs-card sttgs-card-danger">
                            <div class="sttgs-card-header">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Zona de Perigo
                            </div>
                            <div class="sttgs-card-body">
                                <h4 class="sttgs-danger-title">Eliminar Conta</h4>
                                <p class="sttgs-card-description">
                                    Esta ação é <strong>irreversível</strong>. Todos os seus dados, progresso e
                                    configurações serão permanentemente eliminados.
                                </p>
                                <div class="sttgs-danger-list">
                                    <div class="sttgs-danger-item">
                                        <i class="bi bi-x-circle"></i>
                                        <span>Todos os dados da conta serão eliminados</span>
                                    </div>
                                    <div class="sttgs-danger-item">
                                        <i class="bi bi-x-circle"></i>
                                        <span>O progresso será perdido</span>
                                    </div>
                                    <div class="sttgs-danger-item">
                                        <i class="bi bi-x-circle"></i>
                                        <span>Não será possível recuperar a conta</span>
                                    </div>
                                </div>
                                <button type="button" class="sttgs-btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteAccountModal">
                                    <i class="bi bi-trash me-2"></i>
                                    Eliminar Conta Permanentemente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Dicas de Segurança -->
                    <div class="sttgs-sidebar-card">
                        <div class="sttgs-sidebar-header">
                            <i class="bi bi-lightbulb me-2"></i>
                            Dicas de Segurança
                        </div>
                        <div class="sttgs-sidebar-body">
                            <div class="sttgs-tip">
                                <div class="sttgs-tip-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="sttgs-tip-content">
                                    <h5>Palavra-passe Forte</h5>
                                    <p>Use uma combinação de letras, números e símbolos para criar uma palavra-passe
                                        segura.</p>
                                </div>
                            </div>
                            <div class="sttgs-tip">
                                <div class="sttgs-tip-icon">
                                    <i class="bi bi-envelope-check"></i>
                                </div>
                                <div class="sttgs-tip-content">
                                    <h5>Email Seguro</h5>
                                    <p>Mantenha o seu email atualizado para receber notificações de segurança
                                        importantes.</p>
                                </div>
                            </div>
                            <div class="sttgs-tip">
                                <div class="sttgs-tip-icon">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div class="sttgs-tip-content">
                                    <h5>Sessões Ativas</h5>
                                    <p>Termine a sessão em dispositivos públicos ou partilhados para manter a conta
                                        segura.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Atividade Recente -->
                    <div class="sttgs-sidebar-card">
                        <div class="sttgs-sidebar-header">
                            <i class="bi bi-activity me-2"></i>
                            Atividade da Conta
                        </div>
                        <div class="sttgs-sidebar-body">
                            <div class="sttgs-activity-item">
                                <div class="sttgs-activity-icon sttgs-activity-login">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </div>
                                <div class="sttgs-activity-content">
                                    <div class="sttgs-activity-title">Último Login</div>
                                    <div class="sttgs-activity-time">
                                        <?= $lastLogin !== 'Nunca' ? date('d/m/Y H:i', strtotime($lastLogin)) : 'Nunca' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="sttgs-activity-item">
                                <div class="sttgs-activity-icon sttgs-activity-security">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="sttgs-activity-content">
                                    <div class="sttgs-activity-title">Tokens de Segurança</div>
                                    <div class="sttgs-activity-time"><?= $stats['active_tokens'] ?> ativo(s)</div>
                                </div>
                            </div>
                            <div class="sttgs-activity-item">
                                <div class="sttgs-activity-icon sttgs-activity-member">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="sttgs-activity-content">
                                    <div class="sttgs-activity-title">Membro desde</div>
                                    <div class="sttgs-activity-time"><?= date('d/m/Y', strtotime($memberSince)) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Links Rápidos -->
                    <div class="sttgs-sidebar-card">
                        <div class="sttgs-sidebar-header">
                            <i class="bi bi-link-45deg me-2"></i>
                            Links Rápidos
                        </div>
                        <div class="sttgs-sidebar-body">
                            <a href="perfil.php" class="sttgs-quick-link">
                                <i class="bi bi-person-circle"></i>
                                <span>Ver Perfil</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="stats.php" class="sttgs-quick-link">
                                <i class="bi bi-bar-chart-line"></i>
                                <span>Estatísticas</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="../Suporte.php" class="sttgs-quick-link">
                                <i class="bi bi-question-circle"></i>
                                <span>Suporte</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a href="../Politica.php" class="sttgs-quick-link">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Políticas</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-black py-5 border-top border-warning border-opacity-25">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-6">
                    <img src="../Imagens/Logotipos/Logo KR Legends - soft trans recortado.png" alt="KR Legends"
                        height="65" class="mb-3">
                    <p class="text-white-50" data-translate="footer.joinCommunity">
                        Junte-se à comunidade KR Legends e faça parte desta revolução no mundo das corridas virtuais.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h3 class="text-warning mb-3" data-translate="footer.followUs">Siga-nos nas Redes Sociais</h3>
                    <div class="social-links">
                        <a href="https://www.instagram.com/kr_legends.oficial/" target="_blank" class="social-link">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/@KRLegends-media" target="_blank" class="social-link">
                            <i class="bi bi-youtube"></i>
                        </a>
                        <a href="https://www.tiktok.com/@kr.legends?lang=pt-BR" target="_blank" class="social-link">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        <a href="https://www.roblox.com/pt/games/113586179382037/KR-Legends-Beta" target="_blank"
                            class="social-link">
                            <i class="bi bi-controller"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center border-top border-warning border-opacity-25 pt-4">
                <p class="text-white-50 mb-2" data-translate="footer.rights">©2026 KR Legends. Todos os direitos
                    reservados.</p>
                <p class="text-white-50 small" data-translate="footer.developed">
                    Desenvolvido pela equipa de KR Legends com paixão pelo automobilismo e programação.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/includes/profileMenu.js"></script>
    <script src="../js/Info_produtos.js"></script>
    <script src="../js/Gerais/Geral.js"></script>
    <script src="JS/settings.js" defer></script>
    <script src="../js/Gerais/cookieConsent.js"></script>
    <script src="../js/Gerais/notification_API.js" defer></script>
</body>

</html>