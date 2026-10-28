<?php
/**
 * Cine Palácio - Cabeçalho do Site
 * Estética: Anos 70 / O Agente Secreto (2025) / Cidade de Deus
 * Responsivo (MobileFirst) com Tailwind CSS
 */

require_once __DIR__ . '/../config/bd.php';
require_once __DIR__ . '/auth.php';

$usuario = obterUsuarioLogado();
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' • ' : '' ?>Cine Palácio | Cinema Brasileiro</title>
    
    <!-- Meta tags temáticas -->
    <meta name="description" content="Acervo de Cinema Brasileiro dos anos 60, 70 até a atualidade com referências do IMDb. Classifique filmes, crie sua lista e explore filmes mais e menos conhecidos.">
    <meta name="theme-color" content="#140E0A">

    <!-- Tailwind CSS (via CDN oficial) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cine: {
                            fundo: '#140E0A',
                            card: '#201610',
                            cardhover: '#2B1D15',
                            borda: '#453022',
                            ambar: '#E5A93C',
                            ouro: '#F59E0B',
                            laranja: '#C2410C',
                            terracota: '#9A3412',
                            creme: '#FAF0E6',
                            cremesuave: '#EAD8C0',
                            tabaco: '#18110D'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Ícones Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Estilos Customizados Retrô Anos 70 -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="bg-cine-fundo text-cine-creme min-h-screen flex flex-col pelicula-grain">

    <!-- Letreiro Superior Estilo Cinema de Rua Anos 70 -->
    <div class="bg-gradient-to-r from-amber-950 via-cine-laranja to-amber-950 text-amber-200 text-xs py-1.5 px-4 text-center font-vintage tracking-wider border-b border-amber-900/60 shadow-inner flex items-center justify-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
        <span>SESSÃO NACIONAL • DO CINEMA NOVO E MARGINAL AOS GRANDES CLÁSSICOS CONTEMPORÂNEOS</span>
        <span class="inline-block w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
    </div>

    <!-- Navegação Principal -->
    <header class="bg-cine-tabaco/95 backdrop-blur border-b border-cine-borda sticky top-0 z-50 shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                
                <!-- Logo Retrô Cine Palácio -->
                <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
                    <div class="w-11 h-11 rounded-lg bg-gradient-to-br from-amber-500 to-cine-laranja p-0.5 shadow-lg shadow-amber-900/40 transform group-hover:rotate-3 transition">
                        <div class="w-full h-full bg-cine-tabaco rounded-[6px] flex items-center justify-center text-amber-400">
                            <i class="bi bi-film text-2xl group-hover:scale-110 transition"></i>
                        </div>
                    </div>
                    <div>
                        <span class="font-cartaz text-3xl sm:text-4xl tracking-wider text-amber-400 letreiro-neon block leading-none">
                            CINE PALÁCIO
                        </span>
                        <span class="text-[10px] font-vintage uppercase tracking-widest text-amber-300/80 block -mt-1">
                            Cinema Brasileiro • 1970 - 2025
                        </span>
                    </div>
                </a>

                <!-- Barra de Busca Dinâmica com Sugestões (Visível em Tablets e Desktops) -->
                <div class="hidden md:flex flex-1 max-w-lg mx-4 relative">
                    <div class="relative w-full">
                        <input type="text" 
                               id="busca-filmes-input"
                               placeholder="Buscar por filme, diretor ou gênero..." 
                               autocomplete="off"
                               class="w-full bg-stone-900/90 text-stone-100 placeholder-stone-500 text-sm rounded-full pl-10 pr-10 py-2.5 border border-cine-borda focus:border-cine-ambar focus:ring-1 focus:ring-cine-ambar outline-none transition shadow-inner">
                        <i class="bi bi-search absolute left-3.5 top-3 text-stone-400 text-sm"></i>
                        <span id="busca-loading-icon" class="absolute right-3.5 top-2.5 text-amber-500 hidden animate-spin">
                            <i class="bi bi-arrow-repeat text-base"></i>
                        </span>
                    </div>

                    <!-- Dropdown de Sugestões Dinâmicas -->
                    <div id="sugestoes-busca-container" class="absolute top-full left-0 right-0 mt-2 bg-cine-card border border-cine-borda rounded-xl shadow-2xl overflow-hidden hidden z-50 max-h-96 overflow-y-auto">
                        <div class="p-2.5 bg-stone-950/80 border-b border-stone-800 text-[11px] font-vintage tracking-wider text-amber-400 flex items-center justify-between">
                            <span><i class="bi bi-lightning-charge-fill mr-1"></i> SUGESTÕES ENCONTRADAS NO ACERVO</span>
                            <span class="text-stone-500">Pressione ESC para fechar</span>
                        </div>
                        <div id="sugestoes-busca-lista" class="divide-y divide-stone-800/60">
                            <!-- Inserido dinamicamente via busca.js -->
                        </div>
                    </div>
                </div>

                <!-- Menu de Navegação Desktop -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
                    <a href="index.php" class="px-3 py-2 rounded-md text-sm font-semibold transition <?= $paginaAtual === 'index.php' && !isset($_GET['filtro']) ? 'text-amber-400 bg-stone-900' : 'text-stone-300 hover:text-amber-300 hover:bg-stone-900/60' ?>">
                        <i class="bi bi-grid-fill mr-1 text-xs"></i> Catálogo
                    </a>
                    <a href="index.php?filtro=mais_conhecido" class="px-3 py-2 rounded-md text-sm font-semibold transition <?= isset($_GET['filtro']) && $_GET['filtro'] === 'mais_conhecido' ? 'text-amber-400 bg-stone-900' : 'text-stone-300 hover:text-amber-300 hover:bg-stone-900/60' ?>">
                        Mais Conhecidos
                    </a>
                    <a href="index.php?filtro=menos_conhecido" class="px-3 py-2 rounded-md text-sm font-semibold transition <?= isset($_GET['filtro']) && $_GET['filtro'] === 'menos_conhecido' ? 'text-amber-400 bg-stone-900' : 'text-stone-300 hover:text-amber-300 hover:bg-stone-900/60' ?>">
                        <span class="text-amber-500">Lado B</span> / Cult
                    </a>
                    <a href="historico.php" class="px-3 py-2 rounded-md text-sm font-semibold transition <?= $paginaAtual === 'historico.php' ? 'text-amber-400 bg-stone-900' : 'text-stone-300 hover:text-amber-300 hover:bg-stone-900/60' ?>" title="Auditoria de alterações">
                        <i class="bi bi-clock-history mr-1"></i> Histórico
                    </a>
                </nav>

                <!-- Área do Usuário / Login -->
                <div class="hidden sm:flex items-center gap-3">
                    <?php if ($usuario): ?>
                        <a href="meu_espaco.php" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-950/70 border border-amber-700/60 hover:border-amber-500 text-amber-200 text-sm font-semibold transition shadow group">
                            <i class="bi bi-person-fill-check text-amber-400 group-hover:scale-110 transition"></i>
                            <span class="max-w-[120px] truncate" title="<?= htmlspecialchars($usuario['email']) ?>">Meu Espaço</span>
                        </a>
                        <a href="actions/auth_logout.php" class="text-stone-400 hover:text-rose-400 p-2 rounded transition" title="Sair da Conta">
                            <i class="bi bi-box-arrow-right text-lg"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="px-3.5 py-1.5 rounded-lg text-sm font-semibold text-stone-300 hover:text-white transition">
                            Entrar
                        </a>
                        <a href="cadastro.php" class="px-4 py-1.5 rounded-lg text-sm font-bold bg-gradient-to-r from-amber-600 to-cine-laranja hover:from-amber-500 hover:to-orange-600 text-stone-950 shadow-md transition transform hover:-translate-y-0.5">
                            Cadastre-se
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Botão Menu Mobile -->
                <button type="button" 
                        onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" 
                        class="lg:hidden p-2 text-stone-300 hover:text-amber-400 text-2xl focus:outline-none" 
                        aria-label="Abrir menu">
                    <i class="bi bi-list"></i>
                </button>

            </div>

            <!-- Busca Mobile (Apenas em telas pequenas) -->
            <div class="md:hidden pb-3 pt-1">
                <div class="relative w-full">
                    <input type="text" 
                           id="busca-filmes-input-mobile" 
                           placeholder="Buscar filmes, diretores..." 
                           oninput="document.getElementById('busca-filmes-input').value = this.value; document.getElementById('busca-filmes-input').dispatchEvent(new Event('input'))"
                           class="w-full bg-stone-900 text-stone-100 placeholder-stone-500 text-sm rounded-lg pl-9 pr-4 py-2 border border-cine-borda outline-none">
                    <i class="bi bi-search absolute left-3 top-2.5 text-stone-400 text-sm"></i>
                </div>
            </div>
        </div>

        <!-- Menu Mobile Dropdown -->
        <div id="mobile-menu" class="hidden lg:hidden bg-stone-950 border-b border-cine-borda px-4 pt-2 pb-4 space-y-2">
            <a href="index.php" class="block px-3 py-2 rounded text-base font-semibold text-stone-200 hover:bg-stone-900 hover:text-amber-400">
                <i class="bi bi-grid-fill mr-2 text-amber-500"></i> Todos os Filmes
            </a>
            <a href="index.php?filtro=mais_conhecido" class="block px-3 py-2 rounded text-base font-semibold text-stone-200 hover:bg-stone-900 hover:text-amber-400">
                <i class="bi bi-trophy-fill mr-2 text-amber-500"></i> Mais Conhecidos
            </a>
            <a href="index.php?filtro=menos_conhecido" class="block px-3 py-2 rounded text-base font-semibold text-stone-200 hover:bg-stone-900 hover:text-amber-400">
                <i class="bi bi-camera-reels-fill mr-2 text-amber-500"></i> Menos Conhecidos (Lado B)
            </a>
            <a href="historico.php" class="block px-3 py-2 rounded text-base font-semibold text-stone-200 hover:bg-stone-900 hover:text-amber-400">
                <i class="bi bi-clock-history mr-2 text-amber-500"></i> Histórico & Auditoria
            </a>

            <div class="border-t border-stone-800 pt-3 mt-2 flex flex-col gap-2">
                <?php if ($usuario): ?>
                    <a href="meu_espaco.php" class="block px-3 py-2 rounded bg-amber-950/60 border border-amber-800 text-amber-300 font-semibold text-center">
                        <i class="bi bi-person-fill mr-1"></i> Meu Espaço (<?= htmlspecialchars(explode('@', $usuario['email'])[0]) ?>)
                    </a>
                    <a href="actions/auth_logout.php" class="block px-3 py-2 rounded bg-stone-900 text-rose-400 text-center font-medium">
                        Sair da Conta
                    </a>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="login.php" class="px-3 py-2 rounded bg-stone-900 text-stone-200 text-center font-semibold text-sm">
                            Entrar
                        </a>
                        <a href="cadastro.php" class="px-3 py-2 rounded bg-gradient-to-r from-amber-600 to-cine-laranja text-stone-950 text-center font-bold text-sm">
                            Cadastrar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Componente de Modal Global -->
    <?php require_once __DIR__ . '/modal.php'; ?>

    <!-- Container de Toasts -->
    <div id="toast-container"></div>

    <!-- Conteúdo Principal -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
