<?php
/**
 * Cine Palácio - Busca Automática de Capa de Filmes
 * Requisito: Se não tiver a capa no IMDb, procurar pelo nome do filme e o diretor para encontrá-la.
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/logger.php';

/**
 * Busca a URL do cartaz via API pública da Wikipedia/Wikimedia baseada no título e diretor
 */
function buscarCapaNaWeb(string $titulo, string $diretor): ?string {
    // 1. Tentar busca na Wikipedia em português
    $busca = urlencode("{$titulo} filme {$diretor}");
    $url = "https://pt.wikipedia.org/w/api.php?action=query&format=json&generator=search&gsrsearch={$busca}&gsrlimit=3&prop=pageimages&piprop=thumbnail&pithumbsize=600";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: CinePalacioBot/1.0 (cinepalacio@cinema.br)\r\n",
            "timeout" => 4
        ]
    ];
    $context = stream_context_create($opts);

    try {
        $json = @file_get_contents($url, false, $context);
        if ($json) {
            $data = json_decode($json, true);
            if (!empty($data['query']['pages'])) {
                foreach ($data['query']['pages'] as $page) {
                    if (!empty($page['thumbnail']['source'])) {
                        return $page['thumbnail']['source'];
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Silêncio em caso de indisponibilidade de rede
    }

    // 2. Tentar busca alternativa apenas com o título
    $buscaTitulo = urlencode("{$titulo} filme");
    $urlTitulo = "https://pt.wikipedia.org/w/api.php?action=query&format=json&generator=search&gsrsearch={$buscaTitulo}&gsrlimit=2&prop=pageimages&piprop=thumbnail&pithumbsize=600";

    try {
        $json = @file_get_contents($urlTitulo, false, $context);
        if ($json) {
            $data = json_decode($json, true);
            if (!empty($data['query']['pages'])) {
                foreach ($data['query']['pages'] as $page) {
                    if (!empty($page['thumbnail']['source'])) {
                        return $page['thumbnail']['source'];
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Silêncio
    }

    return null;
}

/**
 * Gera um cartaz estilizado anos 70 em formato SVG inline caso nenhuma imagem esteja disponível
 */
function gerarCartazPadrao70s(string $titulo, string $diretor, int $ano, string $genero): string {
    $tituloSeguro = htmlspecialchars(mb_strtoupper($titulo), ENT_QUOTES, 'UTF-8');
    $diretorSeguro = htmlspecialchars(mb_strtoupper($diretor), ENT_QUOTES, 'UTF-8');
    $generoSeguro = htmlspecialchars(mb_strtoupper($genero), ENT_QUOTES, 'UTF-8');

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 600" width="100%" height="100%">
    <defs>
        <radialGradient id="sunburst" cx="50%" cy="40%" r="65%">
            <stop offset="0%" stop-color="#E5A93C" />
            <stop offset="45%" stop-color="#C2410C" />
            <stop offset="85%" stop-color="#451A03" />
            <stop offset="100%" stop-color="#18110D" />
        </radialGradient>
        <pattern id="grain" width="10" height="10" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1" fill="#000" opacity="0.18"/>
            <circle cx="7" cy="8" r="1" fill="#FFF" opacity="0.12"/>
        </pattern>
        <filter id="noise">
            <feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="3" result="turb" />
            <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 0.15 0" />
            <feBlend in="SourceGraphic" in2="turb" mode="multiply" />
        </filter>
    </defs>

    <!-- Fundo pôster anos 70 -->
    <rect width="400" height="600" fill="url(#sunburst)" />
    <rect width="400" height="600" fill="url(#grain)" />

    <!-- Moldura Vintage -->
    <rect x="18" y="18" width="364" height="564" fill="none" stroke="#FAF0E6" stroke-width="3" stroke-opacity="0.85" />
    <rect x="24" y="24" width="352" height="552" fill="none" stroke="#FAF0E6" stroke-width="1" stroke-opacity="0.5" />

    <!-- Cabeçalho Retrô -->
    <text x="200" y="65" text-anchor="middle" font-family="'Courier New', monospace" font-size="12" font-weight="bold" fill="#FDE68A" letter-spacing="4">CINEMA BRASILEIRO • APRESENTA</text>
    
    <!-- Círculo Estilo Cidade de Deus / Agente Secreto -->
    <circle cx="200" cy="230" r="105" fill="#18110D" stroke="#D97706" stroke-width="4" stroke-dasharray="6,4" opacity="0.85" />
    <circle cx="200" cy="230" r="95" fill="#C2410C" opacity="0.4" />
    
    <path d="M 130 230 Q 200 160 270 230 Q 200 300 130 230" fill="none" stroke="#FAF0E6" stroke-width="3" opacity="0.75" />
    <circle cx="200" cy="230" r="22" fill="#FAF0E6" />
    <circle cx="200" cy="230" r="10" fill="#18110D" />

    <!-- Selo Ano -->
    <rect x="150" y="325" width="100" height="26" fill="#18110D" stroke="#FAF0E6" stroke-width="1.5" rx="3" />
    <text x="200" y="343" text-anchor="middle" font-family="Impact, sans-serif" font-size="14" fill="#FDE68A" letter-spacing="2">ANO {$ano}</text>

    <!-- Título do Filme -->
    <text x="200" y="415" text-anchor="middle" font-family="'Impact', 'Arial Black', sans-serif" font-size="28" fill="#FFFBEB" letter-spacing="1">
        {$tituloSeguro}
    </text>

    <!-- Linha Divisória Vintage -->
    <line x1="80" y1="445" x2="320" y2="445" stroke="#FAF0E6" stroke-width="2" stroke-opacity="0.6" />

    <!-- Diretor e Gênero -->
    <text x="200" y="480" text-anchor="middle" font-family="Georgia, serif" font-size="13" font-style="italic" fill="#FEF3C7">
        UM FILME DE {$diretorSeguro}
    </text>

    <text x="200" y="525" text-anchor="middle" font-family="'Courier New', monospace" font-size="11" font-weight="bold" fill="#FDE68A" letter-spacing="2">
        {$generoSeguro}
    </text>
    
    <text x="200" y="555" text-anchor="middle" font-family="'Courier New', monospace" font-size="9" fill="#E2E8F0" opacity="0.65">
        CINE PALÁCIO • ACERVO HISTÓRICO
    </text>
</svg>
SVG;

    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}

// Se for chamado diretamente via AJAX para buscar e salvar a capa no banco
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filme_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $filmeId = (int)$_POST['filme_id'];

    $stmt = $pdo->prepare("SELECT * FROM filmes WHERE id = :id");
    $stmt->execute([':id' => $filmeId]);
    $filme = $stmt->fetch();

    if (!$filme) {
        echo json_encode(['sucesso' => false, 'erro' => 'Filme não encontrado.']);
        exit;
    }

    $novaCapa = buscarCapaNaWeb($filme['titulo'], $filme['diretor']);

    if ($novaCapa) {
        $update = $pdo->prepare("UPDATE filmes SET capa_url = :capa WHERE id = :id");
        $update->execute([':capa' => $novaCapa, ':id' => $filmeId]);

        $usuarioId = obterUsuarioId();
        registrarLog($pdo, $usuarioId, 'BUSCA_CAPA_ATUALIZADA', "Capa do filme '{$filme['titulo']}' encontrada e atualizada via busca web.");

        echo json_encode(['sucesso' => true, 'capa_url' => $novaCapa]);
    } else {
        $fallback = gerarCartazPadrao70s($filme['titulo'], $filme['diretor'], (int)$filme['ano'], $filme['generos']);
        echo json_encode(['sucesso' => true, 'capa_url' => $fallback, 'fallback' => true]);
    }
    exit;
}
