<?php
/**
 * Cine Palácio - Página de Histórico e Auditoria de Logs
 * Requisito CORE: "O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração 
 * feita por qualquer usuário, para auditorias futuras."
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Histórico & Auditoria';

// Filtros
$filtroAcao = trim($_GET['acao'] ?? '');
$filtroEmail = trim($_GET['email'] ?? '');

$where = [];
$params = [];

if (!empty($filtroAcao)) {
    $where[] = "l.acao = :acao";
    $params[':acao'] = $filtroAcao;
}

if (!empty($filtroEmail)) {
    $where[] = "u.email LIKE :email";
    $params[':email'] = '%' . $filtroEmail . '%';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT l.*, u.email AS usuario_email
    FROM historico_logs l
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    {$whereSql}
    ORDER BY l.id DESC
    LIMIT 100
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Total geral de logs registrados
$totalLogs = $pdo->query("SELECT COUNT(*) FROM historico_logs")->fetchColumn();
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

// Lista de ações distintas para o dropdown de filtro
$acoesDistintas = $pdo->query("SELECT DISTINCT acao FROM historico_logs ORDER BY acao ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Cabeçalho da Página de Auditoria -->
<div class="mb-8">
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 text-xs font-mono mb-2">
                    <i class="bi bi-shield-check"></i> Módulo de Auditoria & Conformidade
                </div>
                <h1 class="font-cartaz text-4xl sm:text-5xl text-white tracking-wide letreiro-neon leading-none">
                    HISTÓRICO DE AUDITORIA DO SISTEMA
                </h1>
                <p class="text-stone-400 text-sm mt-1 max-w-2xl leading-relaxed">
                    Registro detalhado e cronológico de todas as interações e alterações realizadas no Cine Palácio: avaliações em estrelas, filmes assistidos, favoritos, acessos e redefinições de credenciais.
                </p>
            </div>

            <!-- Contadores -->
            <div class="flex items-center gap-4 bg-stone-950/80 border border-stone-800 p-4 rounded-xl flex-shrink-0">
                <div class="text-center px-2">
                    <div class="text-2xl font-bold font-cartaz text-amber-400"><?= number_format($totalLogs) ?></div>
                    <div class="text-[10px] text-stone-400 uppercase font-vintage">Eventos Registrados</div>
                </div>
                <div class="h-8 w-px bg-stone-800"></div>
                <div class="text-center px-2">
                    <div class="text-2xl font-bold font-cartaz text-stone-200"><?= number_format($totalUsuarios) ?></div>
                    <div class="text-[10px] text-stone-400 uppercase font-vintage">Usuários Ativos</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Busca de Auditoria -->
<div class="bg-cine-card border border-cine-borda rounded-xl p-4 mb-6 shadow">
    <form method="GET" action="historico.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
        
        <div>
            <label class="block text-xs font-vintage uppercase text-stone-400 mb-1">Filtrar por Ação:</label>
            <select name="acao" class="w-full bg-stone-900 border border-stone-700 rounded-lg px-3 py-2 text-xs text-stone-200 outline-none focus:border-amber-500">
                <option value="">Todas as Ações</option>
                <?php foreach ($acoesDistintas as $ac): ?>
                    <option value="<?= htmlspecialchars($ac) ?>" <?= $filtroAcao === $ac ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ac) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-vintage uppercase text-stone-400 mb-1">Filtrar por E-mail do Usuário:</label>
            <input type="text" 
                   name="email" 
                   value="<?= htmlspecialchars($filtroEmail) ?>" 
                   placeholder="Ex: usuario@email.com" 
                   class="w-full bg-stone-900 border border-stone-700 rounded-lg px-3 py-2 text-xs text-stone-200 placeholder-stone-500 outline-none focus:border-amber-500">
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2 px-4 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-bold text-xs tracking-wider transition">
                <i class="bi bi-funnel-fill mr-1"></i> Filtrar Logs
            </button>
            <?php if (!empty($filtroAcao) || !empty($filtroEmail)): ?>
                <a href="historico.php" class="py-2 px-3 rounded-lg bg-stone-800 text-stone-300 hover:text-white text-xs font-semibold transition" title="Limpar Filtros">
                    Limpar
                </a>
            <?php endif; ?>
        </div>

    </form>
</div>

<!-- Tabela de Logs de Auditoria -->
<div class="bg-cine-card border border-cine-borda rounded-2xl overflow-hidden shadow-2xl">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-stone-950/90 text-stone-400 font-vintage text-[11px] uppercase tracking-wider border-b border-stone-800">
                    <th class="py-3.5 px-4 text-center w-16"># ID</th>
                    <th class="py-3.5 px-4">Data & Hora</th>
                    <th class="py-3.5 px-4">Usuário</th>
                    <th class="py-3.5 px-4">Ação</th>
                    <th class="py-3.5 px-4">Detalhes da Alteração</th>
                    <th class="py-3.5 px-4 text-right">IP / Origem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-800/60 text-xs">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-stone-500">
                            <i class="bi bi-journal-x text-3xl block mb-2 text-stone-600"></i>
                            Nenhum log encontrado para os filtros selecionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $badgeAcao = 'bg-stone-800 text-stone-300';
                        $acao = $log['acao'];
                        if (str_contains($acao, 'CADASTRO') || str_contains($acao, 'LOGIN')) {
                            $badgeAcao = 'bg-blue-950 text-blue-300 border border-blue-800';
                        } elseif (str_contains($acao, 'AVALIAR')) {
                            $badgeAcao = 'bg-amber-950 text-amber-300 border border-amber-700';
                        } elseif (str_contains($acao, 'ASSISTIDO')) {
                            $badgeAcao = 'bg-emerald-950 text-emerald-300 border border-emerald-800';
                        } elseif (str_contains($acao, 'GOSTEI')) {
                            $badgeAcao = 'bg-rose-950 text-rose-300 border border-rose-800';
                        } elseif (str_contains($acao, 'SENHA')) {
                            $badgeAcao = 'bg-purple-950 text-purple-300 border border-purple-800';
                        }
                    ?>
                    <tr class="hover:bg-stone-900/60 transition">
                        <td class="py-3 px-4 text-center text-stone-500 font-mono text-[11px]">
                            <?= $log['id'] ?>
                        </td>
                        <td class="py-3 px-4 text-stone-300 whitespace-nowrap font-mono text-[11px]">
                            <i class="bi bi-clock text-stone-500 mr-1"></i>
                            <?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?>
                        </td>
                        <td class="py-3 px-4 font-medium text-stone-200">
                            <?php if ($log['usuario_email']): ?>
                                <span class="text-amber-300 font-mono" title="ID: <?= $log['usuario_id'] ?>">
                                    <?= htmlspecialchars($log['usuario_email']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-stone-500 italic">Visitante Anônimo</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold tracking-wide <?= $badgeAcao ?>">
                                <?= htmlspecialchars($acao) ?>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-stone-300 leading-relaxed">
                            <?= htmlspecialchars($log['detalhes'] ?? '-') ?>
                        </td>
                        <td class="py-3 px-4 text-right text-stone-400 font-mono text-[11px] whitespace-nowrap">
                            <span class="bg-stone-900 px-2 py-0.5 rounded border border-stone-800" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
