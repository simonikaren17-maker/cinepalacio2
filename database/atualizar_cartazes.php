<?php
/**
 * Cine Palácio - Atualizador de Cartazes via Wikipedia e Wikimedia Commons
 */

require_once __DIR__ . '/../config/bd.php';

$dir = __DIR__ . '/../assets/images/posters';
if (!is_dir($dir)) {
    @mkdir($dir, 0777, true);
}

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: CinePalacioBot/2.0 (CinemaBrasileiro/1.0; contact@cinepalacio.br)\r\n",
        "timeout" => 4
    ],
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false
    ]
];
$ctx = stream_context_create($opts);

$filmes = $pdo->query("SELECT id, titulo, diretor, ano, generos, capa_url FROM filmes ORDER BY id ASC")->fetchAll();

echo "Buscando e salvando capas...\n";

foreach ($filmes as $f) {
    $id = $f['id'];
    $arquivo = "{$dir}/filme_{$id}.jpg";

    if (file_exists($arquivo) && filesize($arquivo) > 2000) {
        continue;
    }

    $sucesso = false;

    // Tentativa 1: Download direto da capa_url se existir
    if (!empty($f['capa_url']) && str_starts_with($f['capa_url'], 'http')) {
        try {
            $dados = @file_get_contents($f['capa_url'], false, $ctx);
            if ($dados && strlen($dados) > 2000) {
                file_put_contents($arquivo, $dados);
                echo "[ID {$id}] '{$f['titulo']}' salvo via URL direta (" . round(strlen($dados)/1024) . " KB)\n";
                $sucesso = true;
            }
        } catch (Exception $e) {}
    }

    // Tentativa 2: Busca por Wikipedia API
    if (!$sucesso) {
        $termos = [
            $f['titulo'] . " (filme)",
            $f['titulo'] . " filme " . $f['diretor'],
            $f['titulo'] . " " . $f['ano']
        ];

        foreach ($termos as $t) {
            $url = "https://pt.wikipedia.org/w/api.php?action=query&format=json&generator=search&gsrsearch=" . urlencode($t) . "&gsrlimit=1&prop=pageimages&piprop=thumbnail&pithumbsize=600";
            try {
                $resp = @file_get_contents($url, false, $ctx);
                if ($resp) {
                    $json = json_decode($resp, true);
                    if (!empty($json['query']['pages'])) {
                        foreach ($json['query']['pages'] as $page) {
                            if (!empty($page['thumbnail']['source'])) {
                                $thumbUrl = $page['thumbnail']['source'];
                                $imgData = @file_get_contents($thumbUrl, false, $ctx);
                                if ($imgData && strlen($imgData) > 2000) {
                                    file_put_contents($arquivo, $imgData);
                                    // Atualiza a URL no banco de dados também
                                    $pdo->prepare("UPDATE filmes SET capa_url = :capa WHERE id = :id")->execute([':capa' => $thumbUrl, ':id' => $id]);
                                    echo "[ID {$id}] '{$f['titulo']}' salvo via Wikipedia API (" . round(strlen($imgData)/1024) . " KB)\n";
                                    $sucesso = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {}
        }
    }

    if (!$sucesso) {
        echo "[ID {$id}] '{$f['titulo']}' usará cartaz vetorial estilizado anos 70\n";
    }
}

$totalNoDisco = count(glob("{$dir}/filme_*.jpg"));
echo "Total de cartazes armazenados localmente: {$totalNoDisco} de " . count($filmes) . "\n";
