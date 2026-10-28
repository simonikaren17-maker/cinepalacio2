<?php
/**
 * Cine Palácio - Ação de Cadastro de Usuário
 * Requisitos:
 * - Cadastro simples com Email e Senha
 * - Tratar senhas de usuários com hash bcrypt
 * - Registrar logs de auditoria
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro.php');
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$senha = trim($_POST['senha'] ?? '');
$confirmarSenha = trim($_POST['confirmar_senha'] ?? '');

if (!$email) {
    definirToast('erro', 'Por favor, informe um endereço de e-mail válido.');
    header('Location: ../cadastro.php');
    exit;
}

if (mb_strlen($senha) < 6) {
    definirToast('erro', 'A senha deve conter no mínimo 6 caracteres.');
    header('Location: ../cadastro.php');
    exit;
}

if ($senha !== $confirmarSenha) {
    definirToast('erro', 'As senhas digitadas não coincidem. Digite novamente.');
    header('Location: ../cadastro.php');
    exit;
}

try {
    // Verificar se o e-mail já existe
    $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtCheck->execute([':email' => $email]);
    if ($stmtCheck->fetch()) {
        definirToast('erro', 'Este e-mail já está cadastrado. Tente entrar ou recuperar sua senha.');
        header('Location: ../cadastro.php');
        exit;
    }

    // Hash obrigatório da senha com BCRYPT
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    $sql = "INSERT INTO usuarios (email, senha, criado_em) VALUES (:email, :senha, :criado_em)";
    $stmt = $pdo->prepare($sql);
    $agora = date('Y-m-d H:i:s');
    $stmt->execute([
        ':email' => $email,
        ':senha' => $senhaHash,
        ':criado_em' => $agora
    ]);

    $novoUsuarioId = (int)$pdo->lastInsertId();

    // Iniciar sessão automaticamente
    $_SESSION['usuario'] = [
        'id' => $novoUsuarioId,
        'email' => $email
    ];

    // Registrar evento no histórico de auditoria
    registrarLog($pdo, $novoUsuarioId, 'CADASTRO', "Novo usuário cadastrado com o e-mail '{$email}'.");
    registrarLog($pdo, $novoUsuarioId, 'LOGIN', "Primeiro acesso do usuário logo após o cadastro.");

    definirToast('sucesso', 'Cadastro realizado com sucesso! Bem-vindo ao Cine Palácio.');
    header('Location: ../meu_espaco.php');
    exit;

} catch (Exception $e) {
    definirToast('erro', 'Ocorreu um erro no cadastro. Tente novamente mais tarde.');
    header('Location: ../cadastro.php');
    exit;
}
