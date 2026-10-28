<?php
/**
 * Cine Palácio - Página Inicial
 * Requisitos:
 * - Filtros por mais ou menos conhecidos
 * - Filtros por gêneros
 * - Filmes marcados como assistido não devem aparecer na lista de novas sugestões (com toggle)
 * - Classificação em estrelas interativa (1 a 5)
 * - Estética dos anos 70 / O Agente Secreto (2025) / Cidade de Deus
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

$usuarioId = obterUsuarioId();
$tituloPagina = 'Catálogo Nacional';

// Parâmetros de Filtro
$filtroPopularidade = $_GET['filtro'] ?? 'todos'; // 'todos', 'mais_conhecido', 'menos_conhecido'
$filtroGenero = trim($_GET['genero'] ?? '');
$ocultarAssistidos = isset($_GET['ocultar_assistidos']) ? (int)$_GET['ocultar_assistidos'] : ($usuarioId ? 0 : 0);

// Construção da consulta segura com PDO
$where = [];
$params = [];

if ($filtroPopularidade === 'mais_conhecido' || $filtroPopularidade === 'menos_conhecido') {
    $where[] = "f.popularidade = :pop";
    $params[':pop'] = $filtroPopularidade;
}

if (!empty($filtroGenero)) {
    $where[] = "f.generos LIKE :genero";
    $params[':genero'] = '%' . $filtroGenero . '%';
}

if ($usuarioId && $ocultarAssistidos === 1) {
    $where[] = "(i.assistido IS NULL OR i.assistido = 0)";
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

if ($usuarioId) {
    $sql = "SELECT f.*, 
                   COALESCE(i.assistido, 0) AS assistido,
                   COALESCE(i.gostou, 0) AS gostou,
                   i.avaliacao_estrelas
            FROM filmes f
            LEFT JOIN interacoes_usuario i ON f.id = i.filme_id AND i.usuario_id = :usuario_id
            {$whereSql}
            ORDER BY 
               CASE WHEN f.popularidade = 'mais_conhecido' THEN 1 ELSE 2 END,
               f.ano DESC";
    $params[':usuario_id'] = $usuarioId;
} else {
    $sql = "SELECT f.*, 
                   0 AS assistido,
                   0 AS gostou,
                   NULL AS avaliacao_estrelas
            FROM filmes f
            {$whereSql}
            ORDER BY 
               CASE WHEN f.popularidade = 'mais_conhecido' THEN 1 ELSE 2 END,
               f.ano DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filmes = $stmt->fetchAll();

// Lista de gêneros para os botões de filtro
$generosDisponiveis = [
    'Drama', 'Crime', 'Comédia', 'Suspense', 'Ficção Científica', 
    'Documentário', 'Ação', 'Experimental', 'Faroeste', 'Terror', 'Romance'
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Destaque Estética Anos 70 (O Agente Secreto / Cidade de Deus) -->
<section class="relative rounded-2xl overflow-hidden mb-10 border border-cine-borda shadow-2xl bg-gradient-to-r from-[#1E140E] via-[#2A1B12] to-[#19100B]">
    <div class="absolute inset-0 bg-cover bg-center opacity-25 mix-blend-luminosity pointer-events-none" style="background-image: url('https://upload.wikimedia.org/wikipedia/pt/6/60/O_Agente_Secreto_%28Cartaz_brasileiro%29.jpg');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-cine-fundo via-cine-tabaco/80 to-transparent"></div>

    <div class="relative z-10 p-6 sm:p-10 lg:p-12 max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-mono uppercase tracking-widest mb-4">
            <i class="bi bi-camera-reels"></i> Estética 1977 • Cinema Autoral Brasileiro
        </div>
        
        <h1 class="font-cartaz text-4xl sm:text-6xl text-white tracking-wider leading-none mb-3 letreiro-neon">
            O CORAÇÃO DO CINEMA BRASILEIRO
        </h1>
        
        <p class="text-cine-cremesuave text-sm sm:text-base leading-relaxed mb-6">
            Explore as grandes obras-primas populares e as pérolas do Cinema Marginal. 
            Descubra novos títulos, avalie com estrelas e monte seu acervo pessoal de filmes assistidos e favoritos.
        </p>

        <div class="flex flex-wrap items-center gap-3">
            <a href="#catalogo" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-cine-ambar to-cine-laranja text-stone-950 font-bold text-sm tracking-wide shadow-lg hover:brightness-110 transition flex items-center gap-2">
                <i class="bi bi-play-circle-fill"></i> Explorar Catálogo
            </a>
            <?php if (!$usuarioId): ?>
            <a href="cadastro.php" class="px-5 py-2.5 rounded-lg bg-stone-900/90 border border-amber-600/50 text-amber-200 font-semibold text-sm hover:bg-stone-800 transition flex items-center gap-2">
                <i class="bi bi-person-plus-fill"></i> Criar Conta para Salvar
            </a>
            <?php else: ?>
            <a href="meu_espaco.php" class="px-5 py-2.5 rounded-lg bg-stone-900/90 border border-amber-600/50 text-amber-200 font-semibold text-sm hover:bg-stone-800 transition flex items-center gap-2">
                <i class="bi bi-bookmark-star-fill"></i> Ver Meu Espaço
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Barra de Filtros e Controles -->
<section id="catalogo" class="mb-8">
    <div class="bg-cine-card border border-cine-borda rounded-xl p-4 sm:p-5 shadow-lg space-y-4">
        
        <!-- Linha 1: Filtro de Popularidade (Mais Conhecidos vs Menos Conhecidos) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-vintage uppercase text-amber-400/90 tracking-wider mr-1">Filtrar Acervo:</span>
                
                <a href="index.php?filtro=todos<?= !empty($filtroGenero) ? '&genero=' . urlencode($filtroGenero) : '' ?><?= $ocultarAssistidos ? '&ocultar_assistidos=1' : '' ?>" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $filtroPopularidade === 'todos' ? 'bg-amber-500 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
                    Todos os Filmes (<?= count($filmes) ?>)
                </a>

                <a href="index.php?filtro=mais_conhecido<?= !empty($filtroGenero) ? '&genero=' . urlencode($filtroGenero) : '' ?><?= $ocultarAssistidos ? '&ocultar_assistidos=1' : '' ?>" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $filtroPopularidade === 'mais_conhecido' ? 'bg-amber-500 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
                    <i class="bi bi-fire mr-1 text-orange-400"></i> Mais Conhecidos
                </a>

                <a href="index.php?filtro=menos_conhecido<?= !empty($filtroGenero) ? '&genero=' . urlencode($filtroGenero) : '' ?><?= $ocultarAssistidos ? '&ocultar_assistidos=1' : '' ?>" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition <?= $filtroPopularidade === 'menos_conhecido' ? 'bg-amber-500 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
                    <i class="bi bi-disc mr-1 text-amber-300"></i> Menos Conhecidos (Lado B & Cult)
                </a>
            </div>

            <!-- Toggle Ocultar Assistidos (Requisito CORE) -->
            <?php if ($usuarioId): ?>
            <div class="flex items-center gap-2 bg-stone-950/70 border border-stone-800 px-3 py-1.5 rounded-lg">
                <input type="checkbox" 
                       id="filtro-ocultar-assistidos" 
                       <?= $ocultarAssistidos ? 'checked' : '' ?>
                       onchange="window.location.href='index.php?filtro=<?= urlencode($filtroPopularidade) ?><?= !empty($filtroGenero) ? '&genero=' . urlencode($filtroGenero) : '' ?>&ocultar_assistidos=' + (this.checked ? '1' : '0')"
                       class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 bg-stone-900 border-stone-700 cursor-pointer">
                <label for="filtro-ocultar-assistidos" class="text-xs text-stone-300 font-medium cursor-pointer select-none">
                    Ocultar filmes que já assisti <span class="text-amber-400 text-[10px]">(Novas Sugestões)</span>
                </label>
            </div>
            <?php endif; ?>
        </div>

        <!-- Linha 2: Filtro por Gêneros -->
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-stone-800/80">
            <span class="text-xs font-vintage uppercase text-stone-400 mr-2">Gênero:</span>
            
            <a href="index.php?filtro=<?= urlencode($filtroPopularidade) ?><?= $ocultarAssistidos ? '&ocultar_assistidos=1' : '' ?>"
               class="px-2.5 py-1 rounded text-xs transition <?= empty($filtroGenero) ? 'bg-amber-900/60 text-amber-200 border border-amber-700/60 font-semibold' : 'text-stone-400 hover:text-stone-200 hover:bg-stone-900' ?>">
                Qualquer Gênero
            </a>

            <?php foreach ($generosDisponiveis as $gen): ?>
                <a href="index.php?filtro=<?= urlencode($filtroPopularidade) ?>&genero=<?= urlencode($gen) ?><?= $ocultarAssistidos ? '&ocultar_assistidos=1' : '' ?>"
                   class="px-2.5 py-1 rounded text-xs transition <?= $filtroGenero === $gen ? 'bg-amber-900/80 text-amber-200 border border-amber-600 font-bold' : 'bg-stone-900/70 text-stone-400 hover:text-amber-300 hover:bg-stone-800' ?>">
                    <?= htmlspecialchars($gen) ?>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Grade de Filmes Brasileiros (MobileFirst) -->
<?php if (empty($filmes)): ?>
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-12 text-center my-8">
        <i class="bi bi-film text-5xl text-amber-500/50 mb-4 block"></i>
        <h3 class="font-cartaz text-2xl text-amber-200 mb-2">NENHUM FILME ENCONTRADO</h3>
        <p class="text-stone-400 text-sm max-w-md mx-auto mb-6">
            <?= $ocultarAssistidos ? 'Você já marcou todos os filmes deste filtro como assistidos! Desmarque o filtro de ocultar para revê-los.' : 'Nenhum filme corresponde aos filtros selecionados. Tente limpar os filtros.' ?>
        </p>
        <a href="index.php" class="px-5 py-2.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-bold text-sm inline-flex items-center gap-2 transition">
            <i class="bi bi-arrow-repeat"></i> Ver Todos os Filmes
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        <?php foreach ($filmes as $f): 
            $estrelas = $f['avaliacao_estrelas'] !== null ? (int)$f['avaliacao_estrelas'] : 0;
            $classif = strtoupper($f['classificacao_indicativa']);
            $classifClass = 'classif-' . $classif;
        ?>
        <div id="filme-card-<?= $f['id'] ?>" class="cartaz-card rounded-xl overflow-hidden flex flex-col group relative">
            
            <!-- Imagem do Cartaz com Links e Badges -->
            <div class="relative aspect-[2/3] w-full bg-stone-950 overflow-hidden">
                <a href="filme.php?id=<?= $f['id'] ?>" class="block w-full h-full">
                    <img src="imagem.php?id=<?= $f['id'] ?>" 
                         alt="<?= htmlspecialchars($f['titulo']) ?>"
                         data-filme-id="<?= $f['id'] ?>"
                         data-titulo="<?= htmlspecialchars($f['titulo']) ?>"
                         data-diretor="<?= htmlspecialchars($f['diretor']) ?>"
                         loading="lazy"
                         onerror="this.src='assets/images/placeholder.svg'; this.onerror=null;"
                         class="cartaz-img w-full h-full object-cover group-hover:scale-105 transition duration-500 ease-out">
                </a>

                <!-- Selo de Classificação Indicativa Oficial Brasileira -->
                <div class="absolute top-2.5 left-2.5 z-10">
                    <span class="classif-badge <?= $classifClass ?>" title="Classificação Indicativa: <?= $classif ?>">
                        <?= htmlspecialchars($classif) ?>
                    </span>
                </div>

                <!-- Selo de Popularidade (Mais Conhecido / Lado B) -->
                <div class="absolute top-2.5 right-2.5 z-10">
                    <?php if ($f['popularidade'] === 'mais_conhecido'): ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/90 text-stone-950 shadow">
                            Mais Conhecido
                        </span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-stone-900/90 text-amber-300 border border-amber-600/40 shadow">
                            Lado B / Cult
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Gradiente inferior com nota IMDb -->
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-stone-950 via-stone-950/80 to-transparent p-3 pt-6 flex items-end justify-between">
                    <span class="text-xs font-bold text-amber-400 flex items-center gap-1 drop-shadow">
                        <i class="bi bi-star-fill text-amber-400"></i> <?= number_format($f['nota_imdb'], 1, '.', '') ?>
                        <span class="text-[10px] text-stone-400 font-normal">IMDb</span>
                    </span>
                    <span class="text-xs text-stone-300 font-mono"><?= $f['ano'] ?></span>
                </div>
            </div>

            <!-- Informações e Detalhes -->
            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <div>
                    <h3 class="font-bold text-base text-stone-100 group-hover:text-amber-400 transition leading-snug line-clamp-1">
                        <a href="filme.php?id=<?= $f['id'] ?>">
                            <?= htmlspecialchars($f['titulo']) ?>
                        </a>
                    </h3>
                    <p class="text-xs text-stone-400 truncate mt-0.5" title="<?= htmlspecialchars($f['diretor']) ?>">
                        Dir. <?= htmlspecialchars($f['diretor']) ?>
                    </p>
                    <p class="text-[11px] text-amber-300/80 truncate mt-0.5">
                        <?= htmlspecialchars($f['generos']) ?>
                    </p>
                </div>

                <!-- Avaliação em Estrelas (1 a 5) Clicáveis -->
                <div class="pt-2 border-t border-stone-800/80 flex items-center justify-between">
                    <span class="text-[10px] uppercase font-vintage text-stone-400 tracking-wider">Avaliar:</span>
                    <div class="estrelas-container" data-filme-id="<?= $f['id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" 
                                    class="estrela-btn <?= $i <= $estrelas ? 'ativa' : '' ?>" 
                                    data-valor="<?= $i ?>" 
                                    title="Classificar com <?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>">
                                <i class="bi <?= $i <= $estrelas ? 'bi-star-fill text-amber-400' : 'bi-star text-stone-600' ?>"></i>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Botões de Ação Rápida (Assistido e Gostei) -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" 
                            data-filme-id="<?= $f['id'] ?>"
                            class="btn-assistido w-full py-1.5 px-2 rounded text-xs font-semibold border transition flex items-center justify-center <?= !empty($f['assistido']) ? 'bg-emerald-950 text-emerald-400 border-emerald-600' : 'bg-stone-900 text-stone-400 border-stone-700 hover:text-stone-200 hover:bg-stone-800' ?>">
                        <i class="bi <?= !empty($f['assistido']) ? 'bi-check2-circle' : 'bi-eye' ?> mr-1.5"></i>
                        <?= !empty($f['assistido']) ? 'Assistido' : 'Assistir' ?>
                    </button>

                    <button type="button" 
                            data-filme-id="<?= $f['id'] ?>"
                            class="btn-gostei w-full py-1.5 px-2 rounded text-xs font-semibold border transition flex items-center justify-center <?= !empty($f['gostou']) ? 'bg-rose-950 text-rose-400 border-rose-600' : 'bg-stone-900 text-stone-400 border-stone-700 hover:text-stone-200 hover:bg-stone-800' ?>">
                        <i class="bi <?= !empty($f['gostou']) ? 'bi-heart-fill text-rose-500' : 'bi-heart' ?> mr-1.5"></i>
                        <?= !empty($f['gostou']) ? 'Gostei' : 'Curtir' ?>
                    </button>
                </div>

            </div>

        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
