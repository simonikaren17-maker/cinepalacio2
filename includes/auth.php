<?php
/**
 * Cine Palácio - Gerenciamento de Sessão e Autenticação
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Retorna true se o usuário estiver autenticado
 */
function usuarioLogado(): bool {
    return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id']);
}

/**
 * Retorna os dados do usuário autenticado ou null
 */
function obterUsuarioLogado(): ?array {
    return usuarioLogado() ? $_SESSION['usuario'] : null;
}

/**
 * Retorna o ID do usuário logado ou null
 */
function obterUsuarioId(): ?int {
    return usuarioLogado() ? (int)$_SESSION['usuario']['id'] : null;
}

/**
 * Exige que o usuário esteja autenticado para acessar a página
 */
function exigirLogin(string $redirecionarPara = 'login.php'): void {
    if (!usuarioLogado()) {
        definirToast('aviso', 'Você precisa estar logado para acessar esta página.');
        header("Location: {$redirecionarPara}");
        exit;
    }
}

/**
 * Registra uma mensagem flash Toast na sessão
 */
function definirToast(string $tipo, string $mensagem): void {
    $_SESSION['toast'] = [
        'tipo' => $tipo, // 'sucesso', 'erro', 'aviso', 'info'
        'mensagem' => $mensagem
    ];
}

/**
 * Recupera e consome o Toast da sessão
 */
function obterToast(): ?array {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * Gera ou recupera o token CSRF da sessão
 */
function gerarCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF recebido
 */
function validarCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
