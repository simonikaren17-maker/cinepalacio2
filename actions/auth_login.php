<?php
/**
 * Cine Palácio - Ação de Login de Usuário
 * Requisitos:
 * - Validação de senha com hash bcrypt (password_verify)
 * - Auditoria completa em historico_logs
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if (empty($email) || empty($senha)) {
    definirToast('erro', 'Preencha o e-mail e a senha para entrar.');
    header('Location: ../login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, email, senha FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        // Log de tentativa frustrada para auditoria de segurança
        registrarLog($pdo, $usuario ? (int)$usuario['id'] : null, 'FALHA_LOGIN', "Tentativa inválida de login para o e-mail '{$email}'.");
        definirToast('erro', 'E-mail ou senha incorretos. Verifique suas credenciais.');
        header('Location: ../login.php');
        exit;
    }

    // Login efetuado com sucesso
    $_SESSION['usuario'] = [
        'id' => (int)$usuario['id'],
        'email' => $usuario['email']
    ];

    registrarLog($pdo, (int)$usuario['id'], 'LOGIN', "Usuário autenticado com sucesso.");
    definirToast('sucesso', 'Login realizado com sucesso! Bem-vindo de volta.');

    $redirecionar = $_SESSION['redirecionar_apos_login'] ?? '../index.php';
    unset($_SESSION['redirecionar_apos_login']);
    header("Location: {$redirecionar}");
    exit;

} catch (Exception $e) {
    definirToast('erro', 'Ocorreu um erro ao processar o login. Tente novamente.');
    header('Location: ../login.php');
    exit;
}
