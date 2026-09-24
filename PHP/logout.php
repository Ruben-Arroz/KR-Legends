<?php
// 1. Inicia a sessão com configurações seguras para cookies
ini_set('session.cookie_secure', 1);  // Garante que os cookies de sessão só são enviados por HTTPS
ini_set('session.cookie_httponly', 1);  // Impede que os cookies de sessão sejam acessados via JavaScript
ini_set('session.use_strict_mode', 1);  // Impede que sessões sejam iniciadas com IDs antigos ou inválidos

session_name('KRSESSION');  // Define o nome da sessão
session_start();  // Inicia a sessão

// 2. Limpa todas as variáveis de sessão
$_SESSION = array();  // Limpa todos os dados armazenados na sessão atual

// 3. Se desejado, apaga o cookie de sessão para destruir completamente a sessão
if (ini_get("session.use_cookies")) {
    // Obtém os parâmetros do cookie da sessão
    $params = session_get_cookie_params();

    // Exclui o cookie da sessão
    setcookie(
        session_name(),  // Nome do cookie
        '',  // Valor vazio, o que efetivamente o exclui
        time() - 42000,  // Define um tempo no passado para expirar o cookie
        $params["path"],  // Caminho onde o cookie é válido
        $params["domain"],  // Domínio onde o cookie é válido
        $params["secure"],  // Se o cookie deve ser enviado apenas por HTTPS
        $params["httponly"]  // Se o cookie deve ser acessível apenas via HTTP(S)
    );
}

// 4. Destroi a sessão
session_destroy();  // Destroi todos os dados da sessão no servidor

// 5. Redireciona o usuário para a página inicial ou login após o logout
$url = "https://alpha.soaresbasto.pt/~a29621/KRLegends/Index.php";  // URL de redirecionamento
header('Location: ' . $url);  // Redireciona para a página de login ou inicial
die();  // Encerra a execução do script, garantindo que o redirecionamento ocorra imediatamente
?>