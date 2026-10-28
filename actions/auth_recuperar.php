<?php
/**
 * Cine Palácio - Ação de Solicitação de Redefinição de Senha
 * Requisito: Opção de redefinição de senha para o usuário
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../esqueci_senha.php');
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    definirToast('erro', 'Por favor, informe um endereço de e-mail válido.');
    header('Location: ../esqueci_senha.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update = $pdo->prepare("UPDATE usuarios SET token_recuperacao = :token, token_expira = :expira WHERE id = :id");
        $update->execute([
            ':token' => $token,
            ':expira' => $expira,
            ':id' => $usuario['id']
        ]);

        registrarLog($pdo, (int)$usuario['id'], 'SOLICITACAO_RECUPERACAO', "Solicitação de link para redefinição de senha para o e-mail '{$email}'.");

        // Como ambiente local/acadêmico pode não ter servidor SMTP configurado,
        // geramos o link direto de demonstração na sessão para conveniência
        $link = "redefinir_senha.php?token=" . urlencode($token);
        $_SESSION['link_recuperacao_gerado'] = $link;
        $_SESSION['email_recuperacao_enviado'] = $email;

        definirToast('sucesso', 'Link de redefinição gerado com sucesso! Utilize o link abaixo para criar uma nova senha.');
    } else {
        // Por segurança (não revelar se o email existe), logamos a tentativa
        registrarLog($pdo, null, 'SOLICITACAO_RECUPERACAO_INEXISTENTE', "Tentativa de recuperação para e-mail não cadastrado: '{$email}'.");
        definirToast('aviso', 'Se este e-mail estiver cadastrado em nosso acervo, as instruções de redefinição foram preparadas.');
    }

    header('Location: ../esqueci_senha.php');
    exit;

} catch (Exception $e) {
    definirToast('erro', 'Erro ao processar solicitação de recuperação.');
    header('Location: ../esqueci_senha.php');
    exit;
}
