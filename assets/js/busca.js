/**
 * Cine Palácio - Busca Dinâmica com Sugestões ao Digitar
 * Requisito: "as buscas deve ser dinamica, na barra de busca deve aparecer as sugestões ao digitar."
 */

document.addEventListener('DOMContentLoaded', () => {
    const inputBusca = document.getElementById('busca-filmes-input');
    const containerSugestoes = document.getElementById('sugestoes-busca-container');
    const listaSugestoes = document.getElementById('sugestoes-busca-lista');
    const iconeCarregando = document.getElementById('busca-loading-icon');

    if (!inputBusca || !containerSugestoes || !listaSugestoes) return;

    let timeoutDebounce = null;

    inputBusca.addEventListener('input', () => {
        const termo = inputBusca.value.trim();

        clearTimeout(timeoutDebounce);

        if (termo.length < 1) {
            containerSugestoes.classList.add('hidden');
            listaSugestoes.innerHTML = '';
            if (iconeCarregando) iconeCarregando.classList.add('hidden');
            return;
        }

        if (iconeCarregando) iconeCarregando.classList.remove('hidden');

        // Debounce de 250ms para fluidez sem sobrecarga
        timeoutDebounce = setTimeout(async () => {
            try {
                const res = await fetch(`actions/buscar_sugestoes.php?q=${encodeURIComponent(termo)}`);
                const filmes = await res.json();

                if (iconeCarregando) iconeCarregando.classList.add('hidden');

                if (!Array.isArray(filmes) || filmes.length === 0) {
                    listaSugestoes.innerHTML = `
                        <div class="p-4 text-center text-stone-400 text-sm">
                            <i class="bi bi-film text-xl block mb-1 text-amber-500/60"></i>
                            Nenhum filme brasileiro encontrado para "<strong>${escapeHtml(termo)}</strong>".
                        </div>
                    `;
                } else {
                    listaSugestoes.innerHTML = filmes.map(f => {
                        const classifCor = obterClasseClassificacao(f.classificacao);
                        const estrelasHtml = f.avaliacao_estrelas 
                            ? `<span class="text-amber-400 font-bold text-xs"><i class="bi bi-star-fill"></i> ${f.avaliacao_estrelas}/5</span>` 
                            : '';
                        const assistidoBadge = f.assistido 
                            ? `<span class="text-[10px] bg-emerald-950 text-emerald-400 border border-emerald-700/60 px-1.5 py-0.5 rounded font-mono">Assistido</span>` 
                            : '';

                        return `
                            <a href="${f.url}" class="flex items-center gap-3 p-3 hover:bg-stone-800/80 border-b border-stone-800/60 transition group">
                                <div class="w-11 h-16 flex-shrink-0 bg-stone-900 rounded overflow-hidden border border-stone-700/60 relative">
                                    <img src="${f.capa_url || ''}" 
                                         alt="${escapeHtml(f.titulo)}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition"
                                         onerror="this.src='assets/images/placeholder.svg'; this.onerror=null;" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <h4 class="text-stone-100 font-bold text-sm truncate group-hover:text-amber-400 transition">${escapeHtml(f.titulo)}</h4>
                                        <span class="text-xs text-stone-400">(${f.ano})</span>
                                        <span class="classif-badge ${classifCor} text-[10px] py-0 px-1">${f.classificacao}</span>
                                    </div>
                                    <p class="text-xs text-stone-400 truncate mb-1">Dir. ${escapeHtml(f.diretor)} • <span class="text-amber-300/80">${escapeHtml(f.generos)}</span></p>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="text-amber-400/90 font-semibold"><i class="bi bi-star-fill text-amber-500"></i> ${f.nota_imdb.toFixed(1)} IMDb</span>
                                        ${estrelasHtml}
                                        ${assistidoBadge}
                                    </div>
                                </div>
                                <div class="text-stone-500 group-hover:text-amber-400 transition pr-2">
                                    <i class="bi bi-chevron-right"></i>
                                </div>
                            </a>
                        `;
                    }).join('');
                }

                containerSugestoes.classList.remove('hidden');

            } catch (erro) {
                if (iconeCarregando) iconeCarregando.classList.add('hidden');
                console.error('Erro na busca de sugestões:', erro);
            }
        }, 250);
    });

    // Fechar dropdown de sugestões ao clicar fora
    document.addEventListener('click', (e) => {
        if (!containerSugestoes.contains(e.target) && e.target !== inputBusca) {
            containerSugestoes.classList.add('hidden');
        }
    });

    // Reabrir se o input tiver foco e texto
    inputBusca.addEventListener('focus', () => {
        if (inputBusca.value.trim().length >= 1 && listaSugestoes.children.length > 0) {
            containerSugestoes.classList.remove('hidden');
        }
    });
});

function obterClasseClassificacao(classif) {
    switch (String(classif).toUpperCase()) {
        case 'L': return 'classif-L';
        case '10': return 'classif-10';
        case '12': return 'classif-12';
        case '14': return 'classif-14';
        case '16': return 'classif-16';
        case '18': return 'classif-18';
        default: return 'classif-12';
    }
}

function escapeHtml(texto) {
    if (!texto) return '';
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}
