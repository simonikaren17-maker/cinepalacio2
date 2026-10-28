<?php
/**
 * Cine Palácio - Módulo de Auditoria e Logs de Alterações
 * Regra CORE: Manter sempre os logs de qualquer alteração feita por qualquer usuário para auditorias futuras.
 */

require_once __DIR__ . '/../config/bd.php';

/**
 * Registra um evento no log de auditoria
 * 
 * @param PDO $pdo Instância ativa do PDO
 * @param int|null $usuarioId ID do usuário (ou null para ações anônimas)
 * @param string $acao Nome da ação executada
 * @param string|null $detalhes Descrição detalhada da alteração
 * @return bool True se registrado com sucesso
 */
function registrarLog(PDO $pdo, ?int $usuarioId, string $acao, ?string $detalhes = null): bool {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        // Tratar caso venha de proxy / reverse proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0]);
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
        // Limita o tamanho do user agent para evitar estouro
        $userAgent = mb_substr($userAgent, 0, 500);

        $sql = "INSERT INTO historico_logs (usuario_id, acao, detalhes, ip_address, user_agent, criado_em) 
                VALUES (:usuario_id, :acao, :detalhes, :ip_address, :user_agent, :criado_em)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':acao' => strtoupper($acao),
            ':detalhes' => $detalhes,
            ':ip_address' => $ip,
            ':user_agent' => $userAgent,
            ':criado_em' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Falha no log não deve travar a aplicação, mas pode ser registrada no log de erro do PHP
        error_log("Erro ao registrar log de auditoria: " . $e->getMessage());
        return false;
    }
}
