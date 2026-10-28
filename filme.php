<?php
/**
 * Cine Palácio - Detalhes do Filme
 * Requisitos:
 * - Exibir obrigatoriamente: classificação indicativa, a sinopse e a capa
 * - Se não tiver a capa no IMDb, procurar pelo nome do filme e o diretor para encontrá-la
 * - Classificação por estrelas (1 a 5)
 * - Botões para marcar se gostou ou assistiu
 * - Estética anos 70 / O Agente Secreto (2025) / Cidade de Deus
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/actions/buscar_capa.php';

$filmeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuarioId = obterUsuarioId();

if ($filmeId <= 0) {
    definirToast('erro', 'Filme não especificado.');
    header('Location: index.php');
    exit;
}

// Buscar dados do filme e interações do usuário atual
if ($usuarioId) {
    $stmt = $pdo->prepare("
        SELECT f.*, 
               COALESCE(i.assistido, 0) AS assistido,
               COALESCE(i.gostou, 0) AS gostou,
               i.avaliacao_estrelas
        FROM filmes f
        LEFT JOIN interacoes_usuario i ON f.id = i.filme_id AND i.usuario_id = :uid
        WHERE f.id = :id
    ");
    $stmt->execute([':uid' => $usuarioId, ':id' => $filmeId]);
} else {
    $stmt = $pdo->prepare("
        SELECT f.*, 
               0 AS assistido,
               0 AS gostou,
               NULL AS avaliacao_estrelas
        FROM filmes f
        WHERE f.id = :id
    ");
    $stmt->execute([':id' => $filmeId]);
}

$filme = $stmt->fetch();

if (!$filme) {
    definirToast('erro', 'Filme não encontrado no acervo do Cine Palácio.');
    header('Location: index.php');
    exit;
}

// Verificação de capa: Se não houver capa, busca automaticamente pelo nome e diretor
if (empty($filme['capa_url'])) {
    $novaCapa = buscarCapaNaWeb($filme['titulo'], $filme['diretor']);
    if ($novaCapa) {
        $update = $pdo->prepare("UPDATE filmes SET capa_url = :capa WHERE id = :id");
        $update->execute([':capa' => $novaCapa, ':id' => $filmeId]);
        $filme['capa_url'] = $novaCapa;
    } else {
        $filme['capa_url'] = gerarCartazPadrao70s($filme['titulo'], $filme['diretor'], (int)$filme['ano'], $filme['generos']);
    }
}

// Buscar filmes relacionados (mesmo gênero)
$stmtRel = $pdo->prepare("
    SELECT id, titulo, ano, diretor, capa_url, nota_imdb, classificacao_indicativa
    FROM filmes 
    WHERE id != :id AND generos LIKE :genero
    LIMIT 4
");
$primeiroGenero = trim(explode(',', $filme['generos'])[0]);
$stmtRel->execute([':id' => $filmeId, ':genero' => '%' . $primeiroGenero . '%']);
$relacionados = $stmtRel->fetchAll();

$tituloPagina = $filme['titulo'];
$classif = strtoupper($filme['classificacao_indicativa']);
$classifClass = 'classif-' . $classif;
$estrelas = $filme['avaliacao_estrelas'] !== null ? (int)$filme['avaliacao_estrelas'] : 0;

// Descrição oficial das classificações indicativas brasileiras
$descricoesClassif = [
    'L'  => 'Livre para todos os públicos (sem conteúdos inadequados).',
    '10' => 'Não recomendado para menores de 10 anos (conteúdo com violência leve ou angústia).',
    '12' => 'Não recomendado para menores de 12 anos (violência ou consumo de drogas lícitas).',
    '14' => 'Não recomendado para menores de 14 anos (conteúdo violento ou linguagem imprópria).',
    '16' => 'Não recomendado para menores de 16 anos (violência explícita ou temática adulta).',
    '18' => 'Não recomendado para menores de 18 anos (conteúdos adultos de extrema violência ou sensibilidade).'
];
$classifTexto = $descricoesClassif[$classif] ?? 'Classificação indicativa nacional';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Migalha de pão / Navegação retrô -->
<div class="mb-6 flex items-center gap-2 text-xs font-vintage uppercase text-stone-400">
    <a href="index.php" class="hover:text-amber-400 transition"><i class="bi bi-house-door mr-1"></i> Catálogo</a>
    <span>/</span>
    <a href="index.php?filtro=<?= urlencode($filme['popularidade']) ?>" class="hover:text-amber-400 transition">
        <?= $filme['popularidade'] === 'mais_conhecido' ? 'Mais Conhecidos' : 'Lado B & Cult' ?>
    </a>
    <span>/</span>
    <span class="text-amber-300 font-bold"><?= htmlspecialchars($filme['titulo']) ?></span>
</div>

<!-- Cartão Principal do Filme (Estética Anos 70 / O Agente Secreto) -->
<article class="bg-cine-card border border-cine-borda rounded-2xl overflow-hidden shadow-2xl mb-12">
    
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 p-6 sm:p-8 lg:p-10">
        
        <!-- Coluna do Cartaz (Capa) -->
        <div class="md:col-span-5 lg:col-span-4 flex flex-col items-center">
            <div class="w-full max-w-sm rounded-xl overflow-hidden border-2 border-amber-900/60 shadow-2xl relative group bg-stone-950 aspect-[2/3]">
                <img src="imagem.php?id=<?= $filme['id'] ?>" 
                     alt="<?= htmlspecialchars($filme['titulo']) ?>"
                     id="detalhe-cartaz-img"
                     data-filme-id="<?= $filme['id'] ?>"
                     data-titulo="<?= htmlspecialchars($filme['titulo']) ?>"
                     data-diretor="<?= htmlspecialchars($filme['diretor']) ?>"
                     onerror="this.src='assets/images/placeholder.svg'; this.onerror=null;"
                     class="cartaz-img w-full h-full object-cover">
                
                <!-- Selo de Classificação Indicativa sobre a capa -->
                <div class="absolute top-3 left-3 z-10 shadow-lg">
                    <span class="classif-badge <?= $classifClass ?> text-sm px-2.5 py-1">
                        <?= htmlspecialchars($classif) ?>
                    </span>
                </div>

                <!-- Selo de Estilo -->
                <div class="absolute bottom-3 right-3 z-10">
                    <span class="px-2.5 py-1 rounded text-xs font-bold bg-stone-950/90 text-amber-300 border border-amber-700/60 backdrop-blur">
                        <?= $filme['popularidade'] === 'mais_conhecido' ? 'Grande Bilheteria' : 'Cinema Lado B / Cult' ?>
                    </span>
                </div>
            </div>

            <!-- Botão de buscar/re-sincronizar capa na web se necessário -->
            <button type="button" 
                    id="btn-rebuscar-capa"
                    onclick="rebuscarCapaOnline(<?= $filme['id'] ?>)" 
                    class="mt-3 text-xs text-stone-400 hover:text-amber-300 flex items-center gap-1.5 transition">
                <i class="bi bi-arrow-repeat"></i> Procurar capa alternativa na web
            </button>
        </div>

        <!-- Coluna de Informações e Sinopse -->
        <div class="md:col-span-7 lg:col-span-8 flex flex-col justify-between space-y-6">
            
            <div class="space-y-4">
                
                <!-- Título e Ano -->
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold font-mono bg-amber-950/80 border border-amber-800 text-amber-300">
                            ANO <?= $filme['ano'] ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-stone-900 text-stone-300">
                            <i class="bi bi-clock mr-1 text-amber-400"></i> <?= $filme['duracao_min'] ?> min
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-stone-900 text-amber-400/90">
                            <i class="bi bi-tag-fill mr-1 text-xs"></i> <?= htmlspecialchars($filme['generos']) ?>
                        </span>
                    </div>

                    <h1 class="font-cartaz text-4xl sm:text-5xl lg:text-6xl text-white tracking-wide leading-none letreiro-neon">
                        <?= htmlspecialchars($filme['titulo']) ?>
                    </h1>

                    <p class="text-stone-300 text-base italic mt-1 font-serif">
                        Dirigido por <strong class="text-amber-300 font-sans not-italic"><?= htmlspecialchars($filme['diretor']) ?></strong>
                    </p>
                </div>

                <!-- Painel de Classificação Indicativa Oficial (Requisito CORE) -->
                <div class="bg-stone-950/80 border border-stone-800 rounded-xl p-4 flex items-start gap-3.5">
                    <span class="classif-badge <?= $classifClass ?> text-base px-3 py-1 flex-shrink-0 mt-0.5">
                        <?= htmlspecialchars($classif) ?>
                    </span>
                    <div class="text-xs">
                        <h4 class="font-bold text-stone-200 uppercase tracking-wider text-[11px] mb-0.5">
                            Classificação Indicativa: <?= htmlspecialchars($classif) ?>
                        </h4>
                        <p class="text-stone-400 leading-relaxed"><?= htmlspecialchars($classifTexto) ?></p>
                    </div>
                </div>

                <!-- Nota do IMDb com Link -->
                <div class="flex items-center gap-4 py-2 border-y border-stone-800/80">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded bg-amber-500 flex items-center justify-center font-bold text-stone-950 text-sm">
                            IMDb
                        </div>
                        <div>
                            <div class="text-amber-400 font-bold text-lg leading-none">
                                <?= number_format($filme['nota_imdb'], 1, '.', '') ?> <span class="text-xs text-stone-400">/ 10</span>
                            </div>
                            <span class="text-[10px] text-stone-500">Nota de referência</span>
                        </div>
                    </div>

                    <?php if (!empty($filme['imdb_url'])): ?>
                    <a href="<?= htmlspecialchars($filme['imdb_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-stone-400 hover:text-amber-400 transition flex items-center gap-1 border-l border-stone-800 pl-4">
                        <i class="bi bi-box-arrow-up-right"></i> Ver no IMDb Brasil
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Sinopse Obrigatória (Requisito CORE) -->
                <div class="space-y-2">
                    <h3 class="font-vintage text-xs uppercase tracking-widest text-amber-400 flex items-center gap-2">
                        <i class="bi bi-card-text text-base"></i> Sinopse da Obra
                    </h3>
                    <p class="text-cine-cremesuave text-sm sm:text-base leading-relaxed bg-stone-950/40 p-4 rounded-xl border border-stone-800/60 font-sans">
                        <?= nl2br(htmlspecialchars($filme['sinopse'])) ?>
                    </p>
                </div>

            </div>

            <!-- Caixa de Interação do Usuário (Estrelas, Assistido, Gostei) -->
            <div class="bg-gradient-to-r from-amber-950/40 via-stone-900 to-amber-950/40 border border-amber-800/50 rounded-xl p-5 space-y-4 shadow-inner">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <!-- Widget de Estrelas (1 a 5) -->
                    <div>
                        <span class="text-xs font-vintage uppercase text-amber-300 block mb-1">
                            Sua Avaliação (Estrelas):
                        </span>
                        <div class="flex items-center gap-3">
                            <div class="estrelas-container" data-filme-id="<?= $filme['id'] ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" 
                                            class="estrela-btn text-2xl <?= $i <= $estrelas ? 'ativa' : '' ?>" 
                                            data-valor="<?= $i ?>" 
                                            title="Avaliar com <?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>">
                                        <i class="bi <?= $i <= $estrelas ? 'bi-star-fill text-amber-400' : 'bi-star text-stone-600' ?>"></i>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <span id="label-estrelas-texto" class="text-xs font-semibold text-amber-400">
                                <?= $estrelas > 0 ? "{$estrelas} de 5 estrelas" : 'Clique para avaliar' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Botões de Ação do Meu Espaço -->
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                data-filme-id="<?= $filme['id'] ?>"
                                class="btn-assistido px-4 py-2.5 rounded-lg text-xs font-bold border transition flex items-center <?= !empty($filme['assistido']) ? 'bg-emerald-950 text-emerald-400 border-emerald-600' : 'bg-stone-900 text-stone-300 border-stone-700 hover:bg-stone-800' ?>">
                            <i class="bi <?= !empty($filme['assistido']) ? 'bi-check2-circle' : 'bi-eye' ?> mr-2 text-sm"></i>
                            <?= !empty($filme['assistido']) ? 'Já Assistido' : 'Marcar Assistido' ?>
                        </button>

                        <button type="button" 
                                data-filme-id="<?= $filme['id'] ?>"
                                class="btn-gostei px-4 py-2.5 rounded-lg text-xs font-bold border transition flex items-center <?= !empty($filme['gostou']) ? 'bg-rose-950 text-rose-400 border-rose-600' : 'bg-stone-900 text-stone-300 border-stone-700 hover:bg-stone-800' ?>">
                            <i class="bi <?= !empty($filme['gostou']) ? 'bi-heart-fill text-rose-500' : 'bi-heart' ?> mr-2 text-sm"></i>
                            <?= !empty($filme['gostou']) ? 'Gostei' : 'Curtir' ?>
                        </button>
                    </div>
                </div>

                <?php if (!$usuarioId): ?>
                <div class="text-[11px] text-amber-400/90 bg-amber-950/60 p-2 rounded border border-amber-900/60 flex items-center gap-2">
                    <i class="bi bi-info-circle-fill text-amber-400"></i>
                    <span>Cadastre-se ou entre na sua conta para salvar suas notas em estrelas e filmes na sua lista pessoal.</span>
                </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</article>

<!-- Filmes Relacionados -->
<?php if (!empty($relacionados)): ?>
<section class="mb-12">
    <div class="flex items-center justify-between mb-6 border-b border-stone-800 pb-3">
        <h2 class="font-cartaz text-2xl text-amber-300 tracking-wider">
            OUTROS FILMES DE <?= htmlspecialchars(mb_strtoupper($primeiroGenero)) ?>
        </h2>
        <a href="index.php?genero=<?= urlencode($primeiroGenero) ?>" class="text-xs text-amber-400 hover:text-amber-300 transition">
            Ver mais deste gênero &rarr;
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
        <?php foreach ($relacionados as $rel): 
            $relClassif = strtoupper($rel['classificacao_indicativa']);
            $relClassifClass = 'classif-' . $relClassif;
        ?>
        <a href="filme.php?id=<?= $rel['id'] ?>" class="cartaz-card rounded-lg overflow-hidden group">
            <div class="aspect-[2/3] w-full bg-stone-950 relative overflow-hidden">
                <img src="imagem.php?id=<?= $rel['id'] ?>" 
                     alt="<?= htmlspecialchars($rel['titulo']) ?>"
                     data-filme-id="<?= $rel['id'] ?>"
                     data-titulo="<?= htmlspecialchars($rel['titulo']) ?>"
                     data-diretor="<?= htmlspecialchars($rel['diretor']) ?>"
                     loading="lazy"
                     onerror="this.src='assets/images/placeholder.svg'; this.onerror=null;"
                     class="cartaz-img w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <div class="absolute top-2 left-2 z-10">
                    <span class="classif-badge <?= $relClassifClass ?> text-[10px] py-0 px-1">
                        <?= htmlspecialchars($relClassif) ?>
                    </span>
                </div>
            </div>
            <div class="p-3">
                <h4 class="font-bold text-xs text-stone-200 group-hover:text-amber-400 truncate">
                    <?= htmlspecialchars($rel['titulo']) ?>
                </h4>
                <p class="text-[11px] text-stone-400 truncate"><?= $rel['ano'] ?> • Dir. <?= htmlspecialchars($rel['diretor']) ?></p>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<script>
async function rebuscarCapaOnline(filmeId) {
    const btn = document.getElementById('btn-rebuscar-capa');
    btn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin"></i> Procurando na Wikipedia / Acervo...';
    btn.disabled = true;

    try {
        const fd = new FormData();
        fd.append('filme_id', filmeId);
        const res = await fetch('actions/buscar_capa.php', { method: 'POST', body: fd });
        const dados = await res.json();

        if (dados.sucesso && dados.capa_url) {
            document.getElementById('detalhe-cartaz-img').src = dados.capa_url;
            window.mostrarToast('sucesso', 'Cartaz atualizado com sucesso a partir da busca na web!');
        } else {
            window.mostrarToast('aviso', 'Nenhum cartaz adicional encontrado para este diretor e título.');
        }
    } catch (e) {
        window.mostrarToast('erro', 'Falha ao buscar capa na web.');
    } finally {
        btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Procurar capa alternativa na web';
        btn.disabled = false;
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
