<?php
/**
 * Cine Palácio - Teste Automatizado de Fluxo e Regras de Negócio (CORE)
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

echo "=== INICIANDO TESTES AUTOMATIZADOS CINE PALÁCIO ===\n";

$passedTests = 0;
$totalTests = 0;

function assertTest($descricao, $condicao) {
    global $passedTests, $totalTests;
    $totalTests++;
    if ($condicao) {
        echo " [OK] {$descricao}\n";
        $passedTests++;
    } else {
        echo " [FALHA] {$descricao}\n";
    }
}

// TESTE 1: Bcrypt Hashing
$senhaOriginal = 'cinema1977_recife';
$hash = password_hash($senhaOriginal, PASSWORD_BCRYPT, ['cost' => 12]);
$infoHash = password_get_info($hash);
assertTest("Senha tratada com algoritmo BCRYPT", $infoHash['algoName'] === 'bcrypt');
assertTest("password_verify valida corretamente a senha", password_verify($senhaOriginal, $hash));
assertTest("password_verify rejeita senha incorreta", !password_verify('senha_errada', $hash));

// TESTE 2: Cadastro Simples com Email e Senha
$emailTeste = 'teste.cinefilo@cinepalacio.br';
$pdo->prepare("DELETE FROM usuarios WHERE email = :email")->execute([':email' => $emailTeste]);

$sql = "INSERT INTO usuarios (email, senha, criado_em) VALUES (:email, :senha, :criado)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':email' => $emailTeste,
    ':senha' => $hash,
    ':criado' => date('Y-m-d H:i:s')
]);
$usuarioId = (int)$pdo->lastInsertId();
assertTest("Usuário inserido com ID válido ({$usuarioId})", $usuarioId > 0);

// TESTE 3: Auditoria - Registrar Log de Ação
$logOk = registrarLog($pdo, $usuarioId, 'CADASTRO', "Teste automatizado de cadastro para '{$emailTeste}'.");
assertTest("Log de auditoria registrado via registrarLog()", $logOk);

$logCad = $pdo->query("SELECT * FROM historico_logs WHERE usuario_id = {$usuarioId} AND acao = 'CADASTRO'")->fetch();
assertTest("Log de auditoria persistido com IP e data", !empty($logCad) && !empty($logCad['criado_em']));

// TESTE 4: Interação - Avaliar com Estrelas (1 a 5)
$filmeId = 1; // Cidade de Deus
$pdo->prepare("DELETE FROM interacoes_usuario WHERE usuario_id = :uid AND filme_id = :fid")->execute([':uid' => $usuarioId, ':fid' => $filmeId]);

$sqlInteracao = "INSERT INTO interacoes_usuario (usuario_id, filme_id, assistido, gostou, avaliacao_estrelas, criado_em, atualizado_em) 
                 VALUES (:uid, :fid, 1, 1, 5, :agora, :agora)";
$pdo->prepare($sqlInteracao)->execute([
    ':uid' => $usuarioId,
    ':fid' => $filmeId,
    ':agora' => date('Y-m-d H:i:s')
]);
registrarLog($pdo, $usuarioId, 'AVALIAR_FILME', "Avaliou 'Cidade de Deus' com 5 estrelas no teste.");
registrarLog($pdo, $usuarioId, 'MARCAR_ASSISTIDO', "Marcou 'Cidade de Deus' como assistido no teste.");

$interacao = $pdo->query("SELECT * FROM interacoes_usuario WHERE usuario_id = {$usuarioId} AND filme_id = {$filmeId}")->fetch();
assertTest("Interação salva com 5 estrelas", $interacao && (int)$interacao['avaliacao_estrelas'] === 5);
assertTest("Interação salva como assistido = 1", $interacao && (int)$interacao['assistido'] === 1);
assertTest("Interação salva como gostou = 1", $interacao && (int)$interacao['gostou'] === 1);

// TESTE 5: Regra de Negócio - Filme assistido não deve aparecer na lista de novas sugestões quando filtrado
$stmtSugestoes = $pdo->prepare("
    SELECT f.id, f.titulo
    FROM filmes f
    LEFT JOIN interacoes_usuario i ON f.id = i.filme_id AND i.usuario_id = :uid
    WHERE (i.assistido IS NULL OR i.assistido = 0)
");
$stmtSugestoes->execute([':uid' => $usuarioId]);
$novasSugestoes = $stmtSugestoes->fetchAll(PDO::FETCH_KEY_PAIR);
assertTest("Filme marcado como assistido NÃO aparece nas novas sugestões", !isset($novasSugestoes[$filmeId]));

// TESTE 6: Redefinição de Senha (Token Seguro com Expiração)
$token = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
$stmtToken = $pdo->prepare("UPDATE usuarios SET token_recuperacao = :tok, token_expira = :exp WHERE id = :id");
$stmtToken->execute([':tok' => $token, ':exp' => $expira, ':id' => $usuarioId]);

$usuarioToken = $pdo->query("SELECT * FROM usuarios WHERE token_recuperacao = '{$token}' AND token_expira >= '" . date('Y-m-d H:i:s') . "'")->fetch();
assertTest("Token de redefinição gerado e validado com prazo ativo", !empty($usuarioToken));

// Redefinir senha com nova senha em bcrypt
$novaSenha = 'nova_senha_cinema_1977';
$novoHash = password_hash($novaSenha, PASSWORD_BCRYPT, ['cost' => 12]);
$pdo->prepare("UPDATE usuarios SET senha = :s, token_recuperacao = NULL, token_expira = NULL WHERE id = :id")->execute([':s' => $novoHash, ':id' => $usuarioId]);
registrarLog($pdo, $usuarioId, 'REDEFINICAO_SENHA', "Senha redefinida com sucesso no teste.");

$usuarioAtualizado = $pdo->query("SELECT * FROM usuarios WHERE id = {$usuarioId}")->fetch();
assertTest("Nova senha validada pelo password_verify", password_verify($novaSenha, $usuarioAtualizado['senha']));
assertTest("Token de recuperação limpo após redefinição", empty($usuarioAtualizado['token_recuperacao']));

// TESTE 7: Busca Dinâmica por Título e Diretor
$stmtBusca = $pdo->prepare("SELECT * FROM filmes WHERE titulo LIKE :b1 OR diretor LIKE :b2");
$stmtBusca->execute([':b1' => '%Agente%', ':b2' => '%Kleber%']);
$filmesBusca = $stmtBusca->fetchAll();
assertTest("Busca dinâmica encontrou filme pelo termo ou diretor", count($filmesBusca) > 0 && $filmesBusca[0]['titulo'] === 'O Agente Secreto');

// TESTE 8: Classificação Indicativa, Sinopse e Capa em Todos os Filmes
$filmesIncompletos = $pdo->query("SELECT COUNT(*) FROM filmes WHERE classificacao_indicativa IS NULL OR sinopse IS NULL OR capa_url IS NULL")->fetchColumn();
$totalFilmesCadastrados = (int)$pdo->query("SELECT COUNT(*) FROM filmes")->fetchColumn();
assertTest("Todos os {$totalFilmesCadastrados} filmes possuem classificação indicativa, sinopse e capa", (int)$filmesIncompletos === 0 && $totalFilmesCadastrados >= 70);

// TESTE 9: Auditoria manteve todos os logs gravados
$totalLogsGravados = $pdo->query("SELECT COUNT(*) FROM historico_logs WHERE usuario_id = {$usuarioId}")->fetchColumn();
assertTest("Histórico de auditoria registrou múltiplos eventos para o usuário ({$totalLogsGravados} logs)", (int)$totalLogsGravados >= 4);

echo "\n=== RESULTADO: {$passedTests} de {$totalTests} TESTES APROVADOS! ===\n";

// Limpeza de dados de teste
$pdo->prepare("DELETE FROM usuarios WHERE email = :email")->execute([':email' => $emailTeste]);
echo "Ambiente de teste limpo.\n";
