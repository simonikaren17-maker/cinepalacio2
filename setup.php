<?php
/**
 * Cine Palácio - Painel de Diagnóstico e Configuração do Banco de Dados
 * Permite testar a conexão PDO MySQL, verificar tabelas e recarregar dados iniciais.
 */

require_once __DIR__ . '/config/database.php';

$mensagem = '';
$tipoMensagem = '';

// Ações executadas pelo painel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'recarregar_filmes') {
        try {
            require_once __DIR__ . '/config/bd.php';
            // Limpa e recarrega os filmes a partir do schema
            if (file_exists(SCHEMA_SQL_PATH)) {
                $sql = file_get_contents(SCHEMA_SQL_PATH);
                if (preg_match('/INSERT INTO filmes.*?;/is', $sql, $matches)) {
                    $pdo->exec("DELETE FROM filmes");
                    $pdo->exec($matches[0]);
                    $mensagem = "Tabela de filmes recarregada com sucesso com as 30 obras brasileiras!";
                    $tipoMensagem = "sucesso";
                }
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao recarregar filmes: " . $e->getMessage();
            $tipoMensagem = "erro";
        }
    } elseif ($acao === 'testar_mysql') {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
            $testPdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $mensagem = "Conexão com o servidor MySQL no host " . DB_HOST . ":" . DB_PORT . " realizada com SUCESSO!";
            $tipoMensagem = "sucesso";
        } catch (Exception $e) {
            $mensagem = "Falha ao conectar ao MySQL: " . $e->getMessage();
            $tipoMensagem = "erro";
        }
    }
}

require_once __DIR__ . '/config/bd.php';
$tituloPagina = 'Configuração & Diagnóstico';

// Coletar status do banco
$driverAtivo = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
$totalFilmes = 0;
$totalUsuarios = 0;
$totalLogs = 0;
$totalInteracoes = 0;

try {
    $totalFilmes = (int)$pdo->query("SELECT COUNT(*) FROM filmes")->fetchColumn();
    $totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $totalLogs = (int)$pdo->query("SELECT COUNT(*) FROM historico_logs")->fetchColumn();
    $totalInteracoes = (int)$pdo->query("SELECT COUNT(*) FROM interacoes_usuario")->fetchColumn();
} catch (Exception $e) {
    // Tabelas podem estar sendo criadas
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto my-6 space-y-8">
    
    <!-- Topo -->
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-3 mb-2">
            <i class="bi bi-gear-wide-connected text-amber-500 text-3xl"></i>
            <div>
                <h1 class="font-cartaz text-3xl sm:text-4xl text-white tracking-wider letreiro-neon">
                    DIAGNÓSTICO E BANCO DE DADOS
                </h1>
                <p class="text-xs font-vintage uppercase text-amber-300">
                    Cine Palácio • Conexão PDO MySQL & Integridade de Dados
                </p>
            </div>
        </div>
    </div>

    <?php if ($mensagem): ?>
        <div class="p-4 rounded-xl text-sm font-medium border flex items-center gap-3 <?= $tipoMensagem === 'sucesso' ? 'bg-emerald-950 border-emerald-600 text-emerald-200' : 'bg-rose-950 border-rose-600 text-rose-200' ?>">
            <i class="bi <?= $tipoMensagem === 'sucesso' ? 'bi-check-circle-fill text-emerald-400' : 'bi-exclamation-triangle-fill text-rose-400' ?> text-xl flex-shrink-0"></i>
            <div><?= htmlspecialchars($mensagem) ?></div>
        </div>
    <?php endif; ?>

    <!-- Status Geral da Conexão -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-cine-card border border-cine-borda p-4 rounded-xl">
            <span class="text-xs font-vintage text-stone-400 uppercase">Driver Ativo:</span>
            <div class="text-xl font-bold font-mono text-amber-400 mt-1 flex items-center gap-1.5">
                <i class="bi bi-database"></i> <?= strtoupper($driverAtivo) ?> (PDO)
            </div>
            <div class="text-[11px] text-stone-400 mt-1">
                <?= $driverAtivo === 'mysql' ? 'Conectado nativamente ao MySQL' : 'Ambiente local (Fallback SQLite ativo)' ?>
            </div>
        </div>

        <div class="bg-cine-card border border-cine-borda p-4 rounded-xl">
            <span class="text-xs font-vintage text-stone-400 uppercase">Filmes Cadastrados:</span>
            <div class="text-2xl font-bold font-cartaz text-stone-100 mt-1">
                <?= $totalFilmes ?> / 70
            </div>
            <div class="text-[11px] text-amber-400 mt-1">Acervo Ampliado do Cinema Brasileiro</div>
        </div>

        <div class="bg-cine-card border border-cine-borda p-4 rounded-xl">
            <span class="text-xs font-vintage text-stone-400 uppercase">Usuários Cadastrados:</span>
            <div class="text-2xl font-bold font-cartaz text-stone-100 mt-1">
                <?= $totalUsuarios ?>
            </div>
            <div class="text-[11px] text-stone-400 mt-1">Senhas tratadas com Bcrypt</div>
        </div>

        <div class="bg-cine-card border border-cine-borda p-4 rounded-xl">
            <span class="text-xs font-vintage text-stone-400 uppercase">Logs de Auditoria:</span>
            <div class="text-2xl font-bold font-cartaz text-stone-100 mt-1">
                <?= $totalLogs ?>
            </div>
            <div class="text-[11px] text-stone-400 mt-1">Histórico completo de ações</div>
        </div>

    </div>

    <!-- Detalhes de Configuração do MySQL -->
    <div class="bg-cine-card border border-cine-borda rounded-xl p-6 shadow-xl space-y-4">
        <h3 class="font-cartaz text-xl text-amber-400 tracking-wide flex items-center gap-2">
            <i class="bi bi-sliders"></i> PARÂMETROS CONFIGURADOS (config/database.php)
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs font-mono">
            <div class="bg-stone-950 p-3 rounded border border-stone-800">
                <span class="text-stone-500 block text-[10px] uppercase">DB_HOST:</span>
                <span class="text-amber-300 font-bold"><?= htmlspecialchars(DB_HOST) ?></span>
            </div>
            <div class="bg-stone-950 p-3 rounded border border-stone-800">
                <span class="text-stone-500 block text-[10px] uppercase">DB_PORT:</span>
                <span class="text-amber-300 font-bold"><?= htmlspecialchars(DB_PORT) ?></span>
            </div>
            <div class="bg-stone-950 p-3 rounded border border-stone-800">
                <span class="text-stone-500 block text-[10px] uppercase">DB_NAME:</span>
                <span class="text-amber-300 font-bold"><?= htmlspecialchars(DB_NAME) ?></span>
            </div>
            <div class="bg-stone-950 p-3 rounded border border-stone-800">
                <span class="text-stone-500 block text-[10px] uppercase">DB_USER:</span>
                <span class="text-amber-300 font-bold"><?= htmlspecialchars(DB_USER) ?></span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-stone-800">
            <form method="POST" action="setup.php" class="inline">
                <input type="hidden" name="acao" value="testar_mysql">
                <button type="submit" class="px-4 py-2 rounded-lg bg-stone-900 border border-amber-600/60 hover:bg-stone-800 text-amber-300 text-xs font-bold transition flex items-center gap-2">
                    <i class="bi bi-hdd-network"></i> Testar Conexão Direta com MySQL
                </button>
            </form>

            <form method="POST" action="setup.php" class="inline">
                <input type="hidden" name="acao" value="recarregar_filmes">
                <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 text-xs font-bold transition flex items-center gap-2">
                    <i class="bi bi-arrow-repeat"></i> Restaurar e Recarregar Filmes Brasileiros
                </button>
            </form>
        </div>
    </div>

    <!-- Guia Rápido de Instalação e Execução -->
    <div class="bg-cine-card border border-cine-borda rounded-xl p-6 shadow-xl space-y-4">
        <h3 class="font-cartaz text-xl text-amber-400 tracking-wide flex items-center gap-2">
            <i class="bi bi-info-circle-fill"></i> INSTRUÇÕES PARA RODAR O PROJETO COM MYSQL
        </h3>
        <div class="text-xs text-stone-300 space-y-2 leading-relaxed">
            <p>1. Se você possui o <strong>XAMPP, WampServer, Laragon ou MySQL Server</strong> instalado, basta iniciar o serviço do MySQL.</p>
            <p>2. O arquivo <code class="text-amber-300 bg-stone-950 px-1 py-0.5 rounded">config/bd.php</code> conectará automaticamente e criará o banco <code class="text-amber-300 bg-stone-950 px-1 py-0.5 rounded">cinepalacio</code> e as tabelas com a carga dos 30 filmes brasileiros.</p>
            <p>3. Você também pode importar manualmente o arquivo <code class="text-amber-300 bg-stone-950 px-1 py-0.5 rounded">database/schema.sql</code> no phpMyAdmin ou MySQL Workbench.</p>
            <p>4. Para iniciar o servidor web embutido do PHP na porta 8000, basta rodar no terminal:</p>
            <div class="bg-stone-950 p-3 rounded border border-stone-800 font-mono text-amber-300 text-xs">
                php -S localhost:8000
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
