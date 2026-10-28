<?php
/**
 * Cine Palácio - Rodapé do Site
 * Estética Anos 70 com suporte a Toasts e Modais
 */

require_once __DIR__ . '/auth.php';
$flashToast = obterToast();
?>
    </main>

    <!-- Rodapé Estilizado Anos 70 -->
    <footer class="bg-cine-tabaco border-t border-cine-borda mt-12 py-10 text-stone-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                
                <!-- Coluna 1: Cine Palácio -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-film text-amber-500 text-2xl"></i>
                        <span class="font-cartaz text-2xl tracking-wider text-amber-400">CINE PALÁCIO</span>
                    </div>
                    <p class="text-sm text-stone-400 max-w-md leading-relaxed">
                        Homenagem e preservação da rica cinematografia brasileira. De Glauber Rocha e Rogério Sganzerla nos anos 60 e 70 ao pulsar contemporâneo de <em>Cidade de Deus</em>, <em>Bacurau</em> e <em>O Agente Secreto</em>.
                    </p>
                    <div class="text-xs font-mono text-amber-500/80 pt-2 flex items-center gap-3">
                        <span><i class="bi bi-shield-lock mr-1"></i> Bcrypt Hashing</span>
                        <span>•</span>
                        <span><i class="bi bi-database-check mr-1"></i> PDO Seguro</span>
                        <span>•</span>
                        <span><i class="bi bi-journal-check mr-1"></i> Auditoria de Logs</span>
                    </div>
                </div>

                <!-- Coluna 2: Navegação & Filtros -->
                <div>
                    <h4 class="font-cartaz text-lg text-amber-300 tracking-wider mb-3">FILTROS DE ACERVO</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="index.php" class="hover:text-amber-400 transition">Catálogo Geral</a></li>
                        <li><a href="index.php?filtro=mais_conhecido" class="hover:text-amber-400 transition">Mais Conhecidos (Grandes Bilheterias)</a></li>
                        <li><a href="index.php?filtro=menos_conhecido" class="hover:text-amber-400 transition">Lado B & Cinema Marginal</a></li>
                        <li><a href="historico.php" class="hover:text-amber-400 transition">Histórico de Auditoria</a></li>
                    </ul>
                </div>

                <!-- Coluna 3: Referências & Créditos -->
                <div>
                    <h4 class="font-cartaz text-lg text-amber-300 tracking-wider mb-3">REFERÊNCIAS</h4>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <a href="https://www.imdb.com/pt/" target="_blank" rel="noopener noreferrer" class="hover:text-amber-400 transition flex items-center gap-1.5">
                                <i class="bi bi-box-arrow-up-right text-xs"></i> Base Oficial IMDb Brasil
                            </a>
                        </li>
                        <li>
                            <a href="https://upload.wikimedia.org/wikipedia/pt/6/60/O_Agente_Secreto_%28Cartaz_brasileiro%29.jpg" target="_blank" rel="noopener noreferrer" class="hover:text-amber-400 transition flex items-center gap-1.5">
                                <i class="bi bi-palette text-xs"></i> Estética: O Agente Secreto (2025)
                            </a>
                        </li>
                        <li>
                            <a href="setup.php" class="hover:text-amber-400 transition flex items-center gap-1.5 text-xs text-stone-500">
                                <i class="bi bi-wrench-adjustable text-xs"></i> Diagnóstico de Banco
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Faixa Inferior -->
            <div class="border-t border-stone-800/80 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500 gap-4">
                <p>&copy; <?= date('Y') ?> Cine Palácio. Desenvolvido de acordo com os requisitos do projeto.</p>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1 text-amber-500/80">
                        <i class="bi bi-camera-reels"></i> Estilo Retrô Anos 70
                    </span>
                    <span>•</span>
                    <a href="https://github.com/simonikaren17-maker/cine-p-lacio.git" target="_blank" rel="noopener noreferrer" class="hover:text-amber-400 transition">
                        Repositório Git
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts JavaScript Globais -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/busca.js"></script>

    <!-- Disparo automático de Toasts enviados via PHP Session -->
    <?php if ($flashToast): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.mostrarToast('<?= htmlspecialchars($flashToast['tipo']) ?>', '<?= addslashes(htmlspecialchars($flashToast['mensagem'])) ?>');
        });
    </script>
    <?php endif; ?>

</body>
</html>
