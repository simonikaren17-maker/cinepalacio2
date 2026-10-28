<?php
/**
 * Cine Palácio - Carga e Verificação do Banco de Dados
 */

require_once __DIR__ . '/../config/bd.php';

echo "=== Cine Palácio - Carga do Banco de Dados ===\n";

try {
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Arquivo schema.sql não encontrado!\n");
    }

    $sql = file_get_contents($schemaFile);

    // Extrai o bloco INSERT INTO filmes
    if (preg_match('/INSERT INTO filmes\s*\([^)]+\)\s*VALUES\s*(.+?);/is', $sql, $matches)) {
        // Limpar filmes antigos para atualizar com os 70 novos filmes
        $pdo->exec("DELETE FROM filmes");
        
        $insertSql = "INSERT INTO filmes (id, titulo, ano, diretor, generos, classificacao_indicativa, duracao_min, sinopse, capa_url, imdb_id, imdb_url, nota_imdb, popularidade) VALUES " . $matches[1];
        $pdo->exec($insertSql);

        $total = $pdo->query("SELECT COUNT(*) FROM filmes")->fetchColumn();
        echo "Sucesso! Total de filmes cadastrados no acervo: {$total}\n";

        $mais = $pdo->query("SELECT COUNT(*) FROM filmes WHERE popularidade = 'mais_conhecido'")->fetchColumn();
        $menos = $pdo->query("SELECT COUNT(*) FROM filmes WHERE popularidade = 'menos_conhecido'")->fetchColumn();
        echo "- Mais Conhecidos: {$mais}\n";
        echo "- Menos Conhecidos (Lado B / Cult / Cinema Marginal): {$menos}\n";
    } else {
        echo "Aviso: Bloco INSERT não encontrado no schema.sql\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
