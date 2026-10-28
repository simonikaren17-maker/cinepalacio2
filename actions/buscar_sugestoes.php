<?php
/**
 * Cine Palácio - Endpoint de Busca Dinâmica com Autocomplete
 * Requisito: "as buscas deve ser dinamica, na barra de busca deve aparecer as sugestões ao digitar."
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';

$termo = trim($_GET['q'] ?? '');

if (mb_strlen($termo) < 1) {
    echo json_encode([]);
    exit;
}

$usuarioId = obterUsuarioId();

try {
    $param = '%' . $termo . '%';

    if ($usuarioId) {
        $sql = "SELECT f.id, f.titulo, f.ano, f.diretor, f.generos, f.classificacao_indicativa, 
                       f.capa_url, f.nota_imdb, f.popularidade,
                       COALESCE(i.assistido, 0) AS assistido,
                       COALESCE(i.gostou, 0) AS gostou,
                       i.avaliacao_estrelas
                FROM filmes f
                LEFT JOIN interacoes_usuario i ON f.id = i.filme_id AND i.usuario_id = :usuario_id
                WHERE f.titulo LIKE :t1 
                   OR f.diretor LIKE :t2 
                   OR f.generos LIKE :t3
                ORDER BY 
                   CASE WHEN f.titulo LIKE :t_exact THEN 1 ELSE 2 END,
                   f.nota_imdb DESC
                LIMIT 8";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':t1' => $param,
            ':t2' => $param,
            ':t3' => $param,
            ':t_exact' => $termo . '%'
        ]);
    } else {
        $sql = "SELECT f.id, f.titulo, f.ano, f.diretor, f.generos, f.classificacao_indicativa, 
                       f.capa_url, f.nota_imdb, f.popularidade,
                       0 AS assistido, 0 AS gostou, NULL AS avaliacao_estrelas
                FROM filmes f
                WHERE f.titulo LIKE :t1 
                   OR f.diretor LIKE :t2 
                   OR f.generos LIKE :t3
                ORDER BY 
                   CASE WHEN f.titulo LIKE :t_exact THEN 1 ELSE 2 END,
                   f.nota_imdb DESC
                LIMIT 8";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':t1' => $param,
            ':t2' => $param,
            ':t3' => $param,
            ':t_exact' => $termo . '%'
        ]);
    }

    $resultados = $stmt->fetchAll();

    // Sanitiza e formata os dados para o frontend
    $formatados = array_map(function($f) {
        return [
            'id' => (int)$f['id'],
            'titulo' => $f['titulo'],
            'ano' => (int)$f['ano'],
            'diretor' => $f['diretor'],
            'generos' => $f['generos'],
            'classificacao' => $f['classificacao_indicativa'],
            'capa_url' => 'imagem.php?id=' . $f['id'],
            'nota_imdb' => (float)$f['nota_imdb'],
            'popularidade' => $f['popularidade'],
            'assistido' => (bool)$f['assistido'],
            'gostou' => (bool)$f['gostou'],
            'avaliacao_estrelas' => $f['avaliacao_estrelas'] !== null ? (int)$f['avaliacao_estrelas'] : null,
            'url' => 'filme.php?id=' . $f['id']
        ];
    }, $resultados);

    echo json_encode($formatados, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Falha na busca dinâmica']);
}
