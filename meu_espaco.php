<?php
/**
 * Cine Palácio - Meu Espaço Personalizado
 * Requisito: "cada usuário tem seu espaço onde ele marca os filmes que ele gostou ou assistiu.
 *             todo usuário que quiser ter essa lista personalizada precisa de se cadastrar."
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

// Exige autenticação
exigirLogin('login.php');

$usuario = obterUsuarioLogado();
$usuarioId = $usuario['id'];
$tituloPagina = 'Meu Espaço';

// Aba ativa: 'todos', 'gostou', 'assistido', 'avaliados'
$aba = $_GET['aba'] ?? 'todos';

// Estatísticas do usuário
$stmtStats = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN assistido = 1 THEN 1 END) AS total_assistidos,
        COUNT(CASE WHEN gostou = 1 THEN 1 END) AS total_gostou,
        COUNT(CASE WHEN avaliacao_estrelas IS NOT NULL THEN 1 END) AS total_avaliados,
        AVG(CASE WHEN avaliacao_estrelas IS NOT NULL THEN avaliacao_estrelas END) AS media_estrelas
    FROM interacoes_usuario
    WHERE usuario_id = :uid
");
$stmtStats->execute([':uid' => $usuarioId]);
$stats = $stmtStats->fetch();

// Consulta dos filmes do espaço do usuário
$condicaoAba = "";
if ($aba === 'gostou') {
    $condicaoAba = "AND i.gostou = 1";
} elseif ($aba === 'assistido') {
    $condicaoAba = "AND i.assistido = 1";
} elseif ($aba === 'avaliados') {
    $condicaoAba = "AND i.avaliacao_estrelas IS NOT NULL";
} else {
    $condicaoAba = "AND (i.assistido = 1 OR i.gostou = 1 OR i.avaliacao_estrelas IS NOT NULL)";
}

$sqlFilmes = "
    SELECT f.*, 
           i.assistido, 
           i.gostou, 
           i.avaliacao_estrelas,
           i.atualizado_em AS interacao_em
    FROM filmes f
    INNER JOIN interacoes_usuario i ON f.id = i.filme_id
    WHERE i.usuario_id = :uid {$condicaoAba}
    ORDER BY i.atualizado_em DESC
";
$stmtFilmes = $pdo->prepare($sqlFilmes);
$stmtFilmes->execute([':uid' => $usuarioId]);
$meusFilmes = $stmtFilmes->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Cabeçalho do Espaço do Usuário -->
<div class="mb-8">
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-amber-900/20 to-transparent pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <span class="text-xs font-vintage uppercase tracking-widest text-amber-400 block mb-1">
                    <i class="bi bi-person-badge mr-1"></i> Espaço Pessoal do Cinéfilo
                </span>
                <h1 class="font-cartaz text-4xl sm:text-5xl text-white tracking-wide letreiro-neon leading-none">
                    ACERVO DE <?= htmlspecialchars(strtoupper(explode('@', $usuario['email'])[0])) ?>
                </h1>
                <p class="text-stone-400 text-sm mt-1">
                    Cadastrado com <strong class="text-stone-200"><?= htmlspecialchars($usuario['email']) ?></strong>
                </p>
            </div>

            <!-- Botões de Ação Rápida -->
            <div class="flex items-center gap-3">
                <a href="index.php" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-bold text-xs tracking-wider transition flex items-center gap-1.5 shadow">
                    <i class="bi bi-plus-circle-fill"></i> Explorar Novos Filmes
                </a>
                <a href="actions/auth_logout.php" class="px-3.5 py-2 rounded-lg bg-stone-900 border border-stone-700 hover:border-rose-500 text-stone-300 hover:text-rose-400 font-medium text-xs transition">
                    <i class="bi bi-box-arrow-right mr-1"></i> Sair
                </a>
            </div>
        </div>

        <!-- Cartões de Estatísticas -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-stone-800/80">
            
            <div class="bg-stone-950/70 border border-stone-800 p-3.5 rounded-xl">
                <div class="text-amber-500 text-xl mb-1"><i class="bi bi-check2-circle"></i></div>
                <div class="text-2xl font-bold font-cartaz text-stone-100 tracking-wider">
                    <?= (int)$stats['total_assistidos'] ?>
                </div>
                <div class="text-xs text-stone-400 uppercase tracking-wider font-vintage">Filmes Assistidos</div>
            </div>

            <div class="bg-stone-950/70 border border-stone-800 p-3.5 rounded-xl">
                <div class="text-rose-500 text-xl mb-1"><i class="bi bi-heart-fill"></i></div>
                <div class="text-2xl font-bold font-cartaz text-stone-100 tracking-wider">
                    <?= (int)$stats['total_gostou'] ?>
                </div>
                <div class="text-xs text-stone-400 uppercase tracking-wider font-vintage">Filmes que Gostei</div>
            </div>

            <div class="bg-stone-950/70 border border-stone-800 p-3.5 rounded-xl">
                <div class="text-amber-400 text-xl mb-1"><i class="bi bi-star-fill"></i></div>
                <div class="text-2xl font-bold font-cartaz text-stone-100 tracking-wider">
                    <?= (int)$stats['total_avaliados'] ?>
                </div>
                <div class="text-xs text-stone-400 uppercase tracking-wider font-vintage">Classificados</div>
            </div>

            <div class="bg-stone-950/70 border border-stone-800 p-3.5 rounded-xl">
                <div class="text-amber-300 text-xl mb-1"><i class="bi bi-award-fill"></i></div>
                <div class="text-2xl font-bold font-cartaz text-stone-100 tracking-wider">
                    <?= $stats['media_estrelas'] ? number_format((float)$stats['media_estrelas'], 1, '.', '') : '0.0' ?>
                </div>
                <div class="text-xs text-stone-400 uppercase tracking-wider font-vintage">Média das Notas</div>
            </div>

        </div>

    </div>
</div>

<!-- Abas de Navegação do Espaço -->
<div class="flex flex-wrap items-center gap-2 mb-6 border-b border-stone-800 pb-3">
    <a href="meu_espaco.php?aba=todos" 
       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 <?= $aba === 'todos' ? 'bg-amber-600 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
        <i class="bi bi-collection-play-fill"></i> Minha Coleção Completa
    </a>

    <a href="meu_espaco.php?aba=gostou" 
       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 <?= $aba === 'gostou' ? 'bg-rose-600 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
        <i class="bi bi-heart-fill"></i> Filmes que Gostei (<?= (int)$stats['total_gostou'] ?>)
    </a>

    <a href="meu_espaco.php?aba=assistido" 
       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 <?= $aba === 'assistido' ? 'bg-emerald-600 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
        <i class="bi bi-check2-circle"></i> Assistidos (<?= (int)$stats['total_assistidos'] ?>)
    </a>

    <a href="meu_espaco.php?aba=avaliados" 
       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 <?= $aba === 'avaliados' ? 'bg-amber-500 text-stone-950 shadow' : 'bg-stone-900 text-stone-300 hover:bg-stone-800' ?>">
        <i class="bi bi-star-fill"></i> Minhas Notas em Estrelas (<?= (int)$stats['total_avaliados'] ?>)
    </a>
</div>

<!-- Lista de Filmes Marcados -->
<?php if (empty($meusFilmes)): ?>
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-12 text-center my-6">
        <i class="bi bi-bookmark-plus text-5xl text-amber-500/40 mb-3 block"></i>
        <h3 class="font-cartaz text-2xl text-amber-200 mb-2">NENHUM FILME NESTA SEÇÃO</h3>
        <p class="text-stone-400 text-sm max-w-md mx-auto mb-6">
            Navegue pelo catálogo e clique nas estrelas ou nos botões "Assistir" e "Gostei" para salvar seus filmes aqui.
        </p>
        <a href="index.php" class="px-5 py-2.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-bold text-sm inline-flex items-center gap-2 transition">
            <i class="bi bi-compass"></i> Ver Catálogo Completo
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($meusFilmes as $f): 
            $estrelas = $f['avaliacao_estrelas'] !== null ? (int)$f['avaliacao_estrelas'] : 0;
            $classif = strtoupper($f['classificacao_indicativa']);
            $classifClass = 'classif-' . $classif;
        ?>
        <div id="filme-card-<?= $f['id'] ?>" class="cartaz-card rounded-xl overflow-hidden flex flex-col group">
            
            <div class="relative aspect-[2/3] w-full bg-stone-950 overflow-hidden">
                <a href="filme.php?id=<?= $f['id'] ?>" class="block w-full h-full">
                    <img src="imagem.php?id=<?= $f['id'] ?>" 
                         alt="<?= htmlspecialchars($f['titulo']) ?>"
                         data-filme-id="<?= $f['id'] ?>"
                         data-titulo="<?= htmlspecialchars($f['titulo']) ?>"
                         data-diretor="<?= htmlspecialchars($f['diretor']) ?>"
                         loading="lazy"
                         onerror="this.src='assets/images/placeholder.svg'; this.onerror=null;"
                         class="cartaz-img w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </a>

                <div class="absolute top-2.5 left-2.5 z-10">
                    <span class="classif-badge <?= $classifClass ?> text-xs px-2 py-0.5">
                        <?= htmlspecialchars($classif) ?>
                    </span>
                </div>

                <div class="absolute top-2.5 right-2.5 z-10 flex gap-1">
                    <?php if ($f['gostou']): ?>
                        <span class="w-7 h-7 rounded-full bg-rose-600 text-white flex items-center justify-center shadow" title="Marcado como Gostei">
                            <i class="bi bi-heart-fill text-xs"></i>
                        </span>
                    <?php endif; ?>
                    <?php if ($f['assistido']): ?>
                        <span class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow" title="Marcado como Assistido">
                            <i class="bi bi-check-lg text-sm"></i>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-stone-950 via-stone-950/80 to-transparent p-3 pt-6 flex items-end justify-between">
                    <span class="text-xs font-bold text-amber-400">
                        <i class="bi bi-star-fill"></i> <?= number_format($f['nota_imdb'], 1, '.', '') ?>
                    </span>
                    <span class="text-xs text-stone-300 font-mono"><?= $f['ano'] ?></span>
                </div>
            </div>

            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <div>
                    <h3 class="font-bold text-base text-stone-100 group-hover:text-amber-400 transition leading-snug line-clamp-1">
                        <a href="filme.php?id=<?= $f['id'] ?>"><?= htmlspecialchars($f['titulo']) ?></a>
                    </h3>
                    <p class="text-xs text-stone-400 truncate mt-0.5">Dir. <?= htmlspecialchars($f['diretor']) ?></p>
                </div>

                <!-- Avaliação em Estrelas -->
                <div class="pt-2 border-t border-stone-800/80 flex items-center justify-between">
                    <span class="text-[10px] uppercase font-vintage text-stone-400">Sua Nota:</span>
                    <div class="estrelas-container" data-filme-id="<?= $f['id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" 
                                    class="estrela-btn <?= $i <= $estrelas ? 'ativa' : '' ?>" 
                                    data-valor="<?= $i ?>" 
                                    title="<?= $i ?> estrela<?= $i > 1 ? 's' : '' ?>">
                                <i class="bi <?= $i <= $estrelas ? 'bi-star-fill text-amber-400' : 'bi-star text-stone-600' ?>"></i>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Ações Rápida -->
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" 
                            data-filme-id="<?= $f['id'] ?>"
                            class="btn-assistido w-full py-1.5 px-2 rounded text-xs font-semibold border transition flex items-center justify-center <?= !empty($f['assistido']) ? 'bg-emerald-950 text-emerald-400 border-emerald-600' : 'bg-stone-900 text-stone-400 border-stone-700' ?>">
                        <i class="bi <?= !empty($f['assistido']) ? 'bi-check2-circle' : 'bi-eye' ?> mr-1.5"></i>
                        <?= !empty($f['assistido']) ? 'Assistido' : 'Assistir' ?>
                    </button>

                    <button type="button" 
                            data-filme-id="<?= $f['id'] ?>"
                            class="btn-gostei w-full py-1.5 px-2 rounded text-xs font-semibold border transition flex items-center justify-center <?= !empty($f['gostou']) ? 'bg-rose-950 text-rose-400 border-rose-600' : 'bg-stone-900 text-stone-400 border-stone-700' ?>">
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
