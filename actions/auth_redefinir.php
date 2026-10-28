<?php
/**
 * Cine Palácio - Ação de Execução da Redefinição de Senha
 * Requisito: Tratar senhas de usuários com hash bcrypt e manter logs de auditoria
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$token = trim($_POST['token'] ?? '');
$senha = trim($_POST['senha'] ?? '');
$confirmarSenha = trim($_POST['confirmar_senha'] ?? '');

if (empty($token)) {
    definirToast('erro', 'Token de redefinição inválido ou ausente.');
    header('Location: ../esqueci_senha.php');
    exit;
}

if (mb_strlen($senha) < 6) {
    definirToast('erro', 'A nova senha deve ter no mínimo 6 caracteres.');
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}

if ($senha !== $confirmarSenha) {
    definirToast('erro', 'As senhas digitadas não conferem. Tente novamente.');
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}

try {
    $agora = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id, email FROM usuarios WHERE token_recuperacao = :token AND token_expira >= :agora");
    $stmt->execute([
        ':token' => $token,
        ':agora' => $agora
    ]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        definirToast('erro', 'Este link de recuperação expirou ou é inválido. Solicite um novo link.');
        header('Location: ../esqueci_senha.php');
        exit;
    }

    // Senha com hash BCRYPT obrigatório
    $novaSenhaHash = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

    $update = $pdo->prepare("UPDATE usuarios SET senha = :senha, token_recuperacao = NULL, token_expira = NULL WHERE id = :id");
    $update->execute([
        ':senha' => $novaSenhaHash,
        ':id' => $usuario['id']
    ]);

    // Registrar no histórico de auditoria
    registrarLog($pdo, (int)$usuario['id'], 'REDEFINICAO_SENHA', "Senha redefinida com sucesso pelo usuário através do token de segurança.");

    // Limpar tokens temporários da sessão
    unset($_SESSION['link_recuperacao_gerado'], $_SESSION['email_recuperacao_enviado']);

    definirToast('sucesso', 'Senha alterada com sucesso! Você já pode entrar com sua nova senha.');
    header('Location: ../login.php');
    exit;

} catch (Exception $e) {
    definirToast('erro', 'Erro ao salvar a nova senha. Tente novamente.');
    header('Location: ../redefinir_senha.php?token=' . urlencode($token));
    exit;
}
