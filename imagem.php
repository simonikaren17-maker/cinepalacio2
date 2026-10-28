<?php
/**
 * Cine Palácio - Servidor e Cache Local de Imagens / Cartazes
 * Garante que 100% dos cartazes de filmes carreguem com sucesso,
 * sem erros de 403 Forbidden, CORS ou bloqueios de hotlink de terceiros.
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/actions/buscar_capa.php';

$filmeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($filmeId <= 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$cacheDir = __DIR__ . '/assets/images/posters';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

$cacheFile = "{$cacheDir}/filme_{$filmeId}.jpg";

// 1. Se o arquivo já estiver em cache local no disco, serve diretamente
if (file_exists($cacheFile) && filesize($cacheFile) > 1000) {
    $mime = mime_content_type($cacheFile) ?: 'image/jpeg';
    header("Content-Type: {$mime}");
    header("Cache-Control: public, max-age=604800"); // Cache de 7 dias
    header("Content-Length: " . filesize($cacheFile));
    readfile($cacheFile);
    exit;
}

// 2. Buscar informações do filme no banco
$stmt = $pdo->prepare("SELECT id, titulo, diretor, ano, generos, capa_url FROM filmes WHERE id = :id");
$stmt->execute([':id' => $filmeId]);
$filme = $stmt->fetch();

if (!$filme) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$sucessoDownload = false;

// 3. Tentar baixar a imagem da URL remota com User-Agent adequado
if (!empty($filme['capa_url']) && str_starts_with($filme['capa_url'], 'http')) {
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: CinePalacioBot/1.0 (Mozilla/5.0; CinemaBrasileiroAcervo/1.0)\r\n" .
                        "Accept: image/webp,image/apng,image/*,*/*;q=0.8\r\n",
            "timeout" => 4
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false
        ]
    ];
    $ctx = stream_context_create($opts);

    try {
        $conteudo = @file_get_contents($filme['capa_url'], false, $ctx);
        if ($conteudo && strlen($conteudo) > 1000) {
            // Salvar no cache local
            @file_put_contents($cacheFile, $conteudo);
            $sucessoDownload = true;

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($conteudo) ?: 'image/jpeg';

            header("Content-Type: {$mime}");
            header("Cache-Control: public, max-age=604800");
            header("Content-Length: " . strlen($conteudo));
            echo $conteudo;
            exit;
        }
    } catch (Exception $e) {
        $sucessoDownload = false;
    }
}

// 4. Se não conseguiu baixar ou não tinha URL, busca na Wikipedia
if (!$sucessoDownload) {
    $novaCapa = buscarCapaNaWeb($filme['titulo'], $filme['diretor']);
    if ($novaCapa && str_starts_with($novaCapa, 'http')) {
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: CinePalacioBot/1.0\r\n",
                "timeout" => 4
            ]
        ];
        $ctx = stream_context_create($opts);
        $conteudo = @file_get_contents($novaCapa, false, $ctx);
        if ($conteudo && strlen($conteudo) > 1000) {
            @file_put_contents($cacheFile, $conteudo);
            // Atualiza o banco com a nova capa
            $pdo->prepare("UPDATE filmes SET capa_url = :capa WHERE id = :id")->execute([':capa' => $novaCapa, ':id' => $filmeId]);

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($conteudo) ?: 'image/jpeg';
            header("Content-Type: {$mime}");
            header("Cache-Control: public, max-age=604800");
            echo $conteudo;
            exit;
        }
    }
}

// 5. Fallback Garantido: Gera Cartaz Vetorial Anos 70
$tituloSeguro = htmlspecialchars(mb_strtoupper($filme['titulo']), ENT_QUOTES, 'UTF-8');
$diretorSeguro = htmlspecialchars(mb_strtoupper($filme['diretor']), ENT_QUOTES, 'UTF-8');
$generoSeguro = htmlspecialchars(mb_strtoupper($filme['generos']), ENT_QUOTES, 'UTF-8');
$ano = (int)$filme['ano'];

header("Content-Type: image/svg+xml; charset=utf-8");
header("Cache-Control: public, max-age=86400");

echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 600" width="400" height="600">
    <defs>
        <radialGradient id="sunburst_{$filmeId}" cx="50%" cy="38%" r="65%">
            <stop offset="0%" stop-color="#E5A93C" />
            <stop offset="40%" stop-color="#C2410C" />
            <stop offset="80%" stop-color="#3B1808" />
            <stop offset="100%" stop-color="#140E0A" />
        </radialGradient>
        <pattern id="grain_{$filmeId}" width="8" height="8" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1" fill="#000" opacity="0.16"/>
            <circle cx="6" cy="6" r="1" fill="#FFF" opacity="0.10"/>
        </pattern>
    </defs>

    <rect width="400" height="600" fill="url(#sunburst_{$filmeId})" />
    <rect width="400" height="600" fill="url(#grain_{$filmeId})" />

    <!-- Moldura Vintage 1970 -->
    <rect x="16" y="16" width="368" height="568" fill="none" stroke="#FAF0E6" stroke-width="3" stroke-opacity="0.8" />
    <rect x="22" y="22" width="356" height="556" fill="none" stroke="#E5A93C" stroke-width="1" stroke-opacity="0.5" />

    <text x="200" y="60" text-anchor="middle" font-family="'Courier New', monospace" font-size="12" font-weight="bold" fill="#FDE68A" letter-spacing="4">CINE PALÁCIO APRESENTA</text>
    
    <!-- Elemento Gráfico Central Cinema Anos 70 -->
    <circle cx="200" cy="225" r="95" fill="#140E0A" stroke="#D97706" stroke-width="3" stroke-dasharray="6,4" />
    <circle cx="200" cy="225" r="85" fill="#C2410C" opacity="0.35" />
    <polygon points="180,185 240,225 180,265" fill="#FAF0E6" />

    <!-- Selo de Ano -->
    <rect x="150" y="325" width="100" height="26" fill="#140E0A" stroke="#E5A93C" stroke-width="1.5" rx="3" />
    <text x="200" y="343" text-anchor="middle" font-family="'Courier New', monospace" font-weight="bold" font-size="14" fill="#FDE68A">ANO {$ano}</text>

    <!-- Título do Filme -->
    <text x="200" y="415" text-anchor="middle" font-family="Impact, Arial Black, sans-serif" font-size="26" fill="#FFFBEB" letter-spacing="1">
        {$tituloSeguro}
    </text>

    <!-- Linha Divisória -->
    <line x1="70" y1="445" x2="330" y2="445" stroke="#FAF0E6" stroke-width="2" stroke-opacity="0.6" />

    <!-- Diretor e Gênero -->
    <text x="200" y="480" text-anchor="middle" font-family="Georgia, serif" font-size="13" font-style="italic" fill="#FEF3C7">
        UM FILME DE {$diretorSeguro}
    </text>

    <text x="200" y="525" text-anchor="middle" font-family="'Courier New', monospace" font-size="11" font-weight="bold" fill="#FDE68A" letter-spacing="2">
        {$generoSeguro}
    </text>
    
    <text x="200" y="555" text-anchor="middle" font-family="'Courier New', monospace" font-size="9" fill="#E2E8F0" opacity="0.65">
        CINEMA BRASILEIRO • ACERVO HISTÓRICO
    </text>
</svg>
SVG;
