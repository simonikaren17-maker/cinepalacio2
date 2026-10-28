<?php
/**
 * Cine Palácio - Endpoint de Interações com Filmes
 * Requisitos:
 * - Classificar filmes clicando em estrelas (1 a 5)
 * - Marcar filmes como 'assistido' ou 'gostou'
 * - Manter auditoria de todas as alterações feitas pelo usuário
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'precisa_login' => true,
        'erro' => 'Cadastre-se ou entre na sua conta para avaliar, marcar como assistido ou salvar em sua lista!'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido.']);
    exit;
}

$usuarioId = obterUsuarioId();
$filmeId = isset($_POST['filme_id']) ? (int)$_POST['filme_id'] : 0;
$tipo = trim($_POST['tipo'] ?? ''); // 'estrelas', 'assistido', 'gostou'

if ($filmeId <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Identificador do filme inválido.']);
    exit;
}

// Verificar se o filme existe
$stmtFilme = $pdo->prepare("SELECT id, titulo FROM filmes WHERE id = :id");
$stmtFilme->execute([':id' => $filmeId]);
$filme = $stmtFilme->fetch();

if (!$filme) {
    echo json_encode(['sucesso' => false, 'erro' => 'Filme não encontrado no acervo.']);
    exit;
}

try {
    // Buscar interação atual do usuário com este filme
    $stmtInteracao = $pdo->prepare("SELECT * FROM interacoes_usuario WHERE usuario_id = :uid AND filme_id = :fid");
    $stmtInteracao->execute([':uid' => $usuarioId, ':fid' => $filmeId]);
    $interacao = $stmtInteracao->fetch();

    $assistido = $interacao ? (int)$interacao['assistido'] : 0;
    $gostou = $interacao ? (int)$interacao['gostou'] : 0;
    $avaliacaoEstrelas = $interacao ? ($interacao['avaliacao_estrelas'] !== null ? (int)$interacao['avaliacao_estrelas'] : null) : null;
    $acaoLog = '';
    $detalhesLog = '';
    $mensagemRetorno = '';

    if ($tipo === 'estrelas') {
        $estrelas = isset($_POST['estrelas']) ? (int)$_POST['estrelas'] : 0;
        if ($estrelas < 0 || $estrelas > 5) {
            echo json_encode(['sucesso' => false, 'erro' => 'A nota em estrelas deve ser entre 1 e 5.']);
            exit;
        }

        // Se clicou na mesma nota, remove a avaliação
        if ($estrelas === $avaliacaoEstrelas) {
            $avaliacaoEstrelas = null;
            $acaoLog = 'REMOVER_AVALIACAO';
            $detalhesLog = "Removeu a avaliação em estrelas do filme '{$filme['titulo']}'";
            $mensagemRetorno = "Avaliação de '{$filme['titulo']}' removida.";
        } else {
            $avaliacaoEstrelas = $estrelas;
            $acaoLog = 'AVALIAR_FILME';
            $detalhesLog = "Avaliou o filme '{$filme['titulo']}' com {$estrelas} estrela(s).";
            $mensagemRetorno = "Você avaliou '{$filme['titulo']}' com {$estrelas} estrela(s)!";
        }
    } elseif ($tipo === 'assistido') {
        $assistido = $assistido ? 0 : 1; // Alterna estado (toggle)
        if ($assistido) {
            $acaoLog = 'MARCAR_ASSISTIDO';
            $detalhesLog = "Marcou o filme '{$filme['titulo']}' como assistido.";
            $mensagemRetorno = "'{$filme['titulo']}' marcado como assistido!";
        } else {
            $acaoLog = 'DESMARCAR_ASSISTIDO';
            $detalhesLog = "Desmarcou o filme '{$filme['titulo']}' de assistidos.";
            $mensagemRetorno = "'{$filme['titulo']}' desmarcado de assistidos.";
        }
    } elseif ($tipo === 'gostou') {
        $gostou = $gostou ? 0 : 1; // Alterna estado (toggle)
        if ($gostou) {
            $acaoLog = 'MARCAR_GOSTEI';
            $detalhesLog = "Marcou o filme '{$filme['titulo']}' como 'Gostei' (favorito).";
            $mensagemRetorno = "Adicionado à sua lista de filmes favoritos!";
        } else {
            $acaoLog = 'DESMARCAR_GOSTEI';
            $detalhesLog = "Removeu o filme '{$filme['titulo']}' dos favoritos.";
            $mensagemRetorno = "Removido da sua lista de filmes favoritos.";
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Ação de interação não reconhecida.']);
        exit;
    }

    // Salvar ou atualizar interação no banco usando PDO Prepared Statements
    if ($interacao) {
        $sqlUpdate = "UPDATE interacoes_usuario 
                      SET assistido = :assistido, 
                          gostou = :gostou, 
                          avaliacao_estrelas = :estrelas, 
                          atualizado_em = :atualizado 
                      WHERE id = :id";
        $stmtUp = $pdo->prepare($sqlUpdate);
        $stmtUp->execute([
            ':assistido' => $assistido,
            ':gostou' => $gostou,
            ':estrelas' => $avaliacaoEstrelas,
            ':atualizado' => date('Y-m-d H:i:s'),
            ':id' => $interacao['id']
        ]);
    } else {
        $sqlInsert = "INSERT INTO interacoes_usuario (usuario_id, filme_id, assistido, gostou, avaliacao_estrelas, criado_em, atualizado_em) 
                      VALUES (:uid, :fid, :assistido, :gostou, :estrelas, :criado, :atualizado)";
        $stmtIn = $pdo->prepare($sqlInsert);
        $agora = date('Y-m-d H:i:s');
        $stmtIn->execute([
            ':uid' => $usuarioId,
            ':fid' => $filmeId,
            ':assistido' => $assistido,
            ':gostou' => $gostou,
            ':estrelas' => $avaliacaoEstrelas,
            ':criado' => $agora,
            ':atualizado' => $agora
        ]);
    }

    // Registrar log obrigatório no histórico de auditoria
    registrarLog($pdo, $usuarioId, $acaoLog, $detalhesLog);

    echo json_encode([
        'sucesso' => true,
        'filme_id' => $filmeId,
        'assistido' => (bool)$assistido,
        'gostou' => (bool)$gostou,
        'avaliacao_estrelas' => $avaliacaoEstrelas,
        'mensagem' => $mensagemRetorno
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao processar interação: ' . $e->getMessage()]);
}
