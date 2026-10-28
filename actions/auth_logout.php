<?php
/**
 * Cine Palácio - Ação de Logout
 * Requisito: Auditoria ao encerrar sessão
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

$usuarioId = obterUsuarioId();
if ($usuarioId) {
    registrarLog($pdo, $usuarioId, 'LOGOUT', "Usuário encerrou a sessão.");
}

unset($_SESSION['usuario']);
session_regenerate_id(true);

definirToast('info', 'Você saiu da sua conta com segurança. Até breve!');
header('Location: ../login.php');
exit;
