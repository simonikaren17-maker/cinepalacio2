<?php
/**
 * Cine Palácio - Componente de Modal Retrô
 * Requisito: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 */
?>
<!-- Modal Global Cine Palácio -->
<div id="modal-global" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 modal-overlay" aria-modal="true" role="dialog">
    <div class="modal-caixa w-full max-w-lg rounded-xl overflow-hidden shadow-2xl transform transition-all border border-amber-900/60">
        <!-- Topo com Faixa Retrô -->
        <div class="bg-gradient-to-r from-amber-900/80 via-stone-900 to-amber-950 px-6 py-4 border-b border-amber-800/40 flex items-center justify-between">
            <h3 id="modal-titulo" class="text-lg font-bold text-amber-200 tracking-wide flex items-center gap-2">
                <!-- Título dinâmico -->
            </h3>
            <button type="button" onclick="window.fecharModal()" class="text-stone-400 hover:text-amber-400 text-2xl leading-none transition">
                &times;
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        <div id="modal-corpo" class="p-6 text-stone-300 text-sm leading-relaxed max-h-[75vh] overflow-y-auto">
            <!-- Mensagem dinâmica -->
        </div>

        <!-- Rodapé com Botões -->
        <div id="modal-rodape" class="bg-stone-950/80 px-6 py-3.5 border-t border-stone-800 flex items-center justify-end gap-3">
            <!-- Botões dinâmicos -->
        </div>
    </div>
</div>
