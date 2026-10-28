/**
 * Cine Palácio - Scripts Globais e Interações
 * Requisitos:
 * - Interação de estrelas (1 a 5)
 * - Toasts para feedback visual
 * - Modais para substituir alertas e caixas nativas do navegador
 */

document.addEventListener('DOMContentLoaded', () => {
    inicializarModais();
    inicializarInteracoesFilmes();
    inicializarImagensFallback();
});

/* ========================================================
   SISTEMA DE MODAIS (Substituto de alert/confirm nativo)
   Requisito: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
======================================================== */

function inicializarModais() {
    const modalEl = document.getElementById('modal-global');
    if (!modalEl) return;

    // Fechar ao clicar no fundo
    modalEl.addEventListener('click', (e) => {
        if (e.target === modalEl) {
            window.fecharModal();
        }
    });

    // Fechar com tecla ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) {
            window.fecharModal();
        }
    });
}

/**
 * Abre o Modal Global com título, mensagem e botões customizados
 */
window.abrirModal = function(titulo, mensagem, opcoes = {}) {
    const modal = document.getElementById('modal-global');
    if (!modal) return;

    document.getElementById('modal-titulo').innerHTML = titulo;
    document.getElementById('modal-corpo').innerHTML = mensagem;

    const rodape = document.getElementById('modal-rodape');
    rodape.innerHTML = '';

    if (opcoes.tipo === 'confirmacao') {
        const btnCancelar = document.createElement('button');
        btnCancelar.type = 'button';
        btnCancelar.className = 'px-4 py-2 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 font-medium transition text-sm';
        btnCancelar.textContent = opcoes.textoCancelar || 'Cancelar';
        btnCancelar.onclick = () => {
            window.fecharModal();
            if (typeof opcoes.onCancel === 'function') opcoes.onCancel();
        };

        const btnConfirmar = document.createElement('button');
        btnConfirmar.type = 'button';
        btnConfirmar.className = 'px-5 py-2 rounded bg-amber-600 hover:bg-amber-500 text-stone-900 font-bold transition text-sm shadow';
        btnConfirmar.textContent = opcoes.textoConfirmar || 'Confirmar';
        btnConfirmar.onclick = () => {
            window.fecharModal();
            if (typeof opcoes.onConfirm === 'function') opcoes.onConfirm();
        };

        rodape.appendChild(btnCancelar);
        rodape.appendChild(btnConfirmar);
    } else {
        const btnFechar = document.createElement('button');
        btnFechar.type = 'button';
        btnFechar.className = 'px-5 py-2 rounded bg-amber-600 hover:bg-amber-500 text-stone-900 font-bold transition text-sm shadow';
        btnFechar.textContent = opcoes.textoBotao || 'Entendido';
        btnFechar.onclick = () => {
            window.fecharModal();
            if (typeof opcoes.onClose === 'function') opcoes.onClose();
        };
        rodape.appendChild(btnFechar);
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
};

window.fecharModal = function() {
    const modal = document.getElementById('modal-global');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};

/**
 * Substitui o window.alert nativo por Modal Retrô
 */
window.alert = function(msg) {
    window.abrirModal('<span class="text-amber-500 font-cartaz text-2xl tracking-wide">AVISO CINE PALÁCIO</span>', msg);
};

/**
 * Diálogo de confirmação com Modal Retrô
 */
window.confirmarAcao = function(mensagem, onConfirm, onCancel) {
    window.abrirModal(
        '<span class="text-amber-500 font-cartaz text-2xl tracking-wide">CONFIRMAÇÃO</span>',
        mensagem,
        {
            tipo: 'confirmacao',
            textoConfirmar: 'Sim, Prosseguir',
            textoCancelar: 'Não, Cancelar',
            onConfirm: onConfirm,
            onCancel: onCancel
        }
    );
};

/* ========================================================
   SISTEMA DE NOTIFICAÇÕES TOAST
   Requisito: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
======================================================== */

window.mostrarToast = function(tipo, mensagem, duracaoMs = 4000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast-item toast-${tipo}`;

    let icone = 'bi-info-circle-fill text-blue-400';
    if (tipo === 'sucesso') icone = 'bi-check-circle-fill text-emerald-400';
    if (tipo === 'erro') icone = 'bi-exclamation-triangle-fill text-rose-500';
    if (tipo === 'aviso') icone = 'bi-exclamation-circle-fill text-amber-400';

    toast.innerHTML = `
        <i class="bi ${icone} text-xl flex-shrink-0 mt-0.5"></i>
        <div class="flex-1 text-sm font-medium leading-relaxed">${mensagem}</div>
        <button type="button" class="text-stone-400 hover:text-stone-100 flex-shrink-0 text-lg leading-none" onclick="this.parentElement.remove()">
            &times;
        </button>
        <div class="toast-progresso" style="animation-duration: ${duracaoMs}ms;"></div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px) scale(0.95)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duracaoMs);
};

/* ========================================================
   INTERAÇÕES DE FILMES (Estrelas, Assistido, Gostei)
   Requisitos:
   - "os usuários vão poder classificar o filmes clicando em estrelas."
   - "cada usuário tem seu espaço onde ele marca os filmes que ele gostou ou assistiu."
======================================================== */

function inicializarInteracoesFilmes() {
    // 1. Hover e clique nas Estrelas
    document.querySelectorAll('.estrelas-container').forEach(container => {
        const botoes = container.querySelectorAll('.estrela-btn');
        const filmeId = container.dataset.filmeId;

        botoes.forEach(btn => {
            const valor = parseInt(btn.dataset.valor, 10);

            // Efeito visual no hover
            btn.addEventListener('mouseenter', () => {
                botoes.forEach(b => {
                    const v = parseInt(b.dataset.valor, 10);
                    if (v <= valor) {
                        b.classList.add('hover-ativo');
                    } else {
                        b.classList.remove('hover-ativo');
                    }
                });
            });

            // Clique para avaliar
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                enviarInteracao(filmeId, 'estrelas', valor, container);
            });
        });

        container.addEventListener('mouseleave', () => {
            botoes.forEach(b => b.classList.remove('hover-ativo'));
        });
    });

    // 2. Botões "Assistido"
    document.querySelectorAll('.btn-assistido').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const filmeId = btn.dataset.filmeId;
            enviarInteracao(filmeId, 'assistido', null, btn);
        });
    });

    // 3. Botões "Gostei / Favorito"
    document.querySelectorAll('.btn-gostei').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const filmeId = btn.dataset.filmeId;
            enviarInteracao(filmeId, 'gostou', null, btn);
        });
    });
}

/**
 * Envia a requisição AJAX para registrar a interação e atualiza a interface
 */
async function enviarInteracao(filmeId, tipo, valor, elementoOrigem) {
    const formData = new FormData();
    formData.append('filme_id', filmeId);
    formData.append('tipo', tipo);
    if (valor !== null) {
        formData.append('estrelas', valor);
    }

    try {
        const resposta = await fetch('actions/interacao.php', {
            method: 'POST',
            body: formData
        });

        const dados = await resposta.json();

        // Se o usuário não estiver logado, exibe Modal convidando para o cadastro
        if (!dados.sucesso && dados.precisa_login) {
            window.abrirModal(
                '<span class="text-amber-500 font-cartaz text-2xl tracking-wide">ENTRE NO CINE PALÁCIO</span>',
                `<p class="text-stone-300 text-sm leading-relaxed mb-4">${dados.erro}</p>
                 <div class="bg-stone-900/80 border border-stone-800 p-3 rounded mb-4 text-xs text-amber-200/90 font-mono">
                     <i class="bi bi-star-fill text-amber-400 mr-1"></i> Usuários cadastrados podem classificar com estrelas e ter seu espaço personalizado com filmes assistidos e favoritos.
                 </div>`,
                {
                    tipo: 'confirmacao',
                    textoConfirmar: 'Criar Conta Grátis',
                    textoCancelar: 'Entrar na Conta',
                    onConfirm: () => { window.location.href = 'cadastro.php'; },
                    onCancel: () => { window.location.href = 'login.php'; }
                }
            );
            return;
        }

        if (!dados.sucesso) {
            window.mostrarToast('erro', dados.erro || 'Falha ao salvar interação.');
            return;
        }

        // Sucesso: feedback visual
        window.mostrarToast('sucesso', dados.mensagem);

        // Atualizar estado visual do componente de estrelas
        if (tipo === 'estrelas') {
            const containers = document.querySelectorAll(`.estrelas-container[data-filme-id="${filmeId}"]`);
            containers.forEach(cont => {
                const botoes = cont.querySelectorAll('.estrela-btn');
                const estrelasAtuais = dados.avaliacao_estrelas || 0;
                botoes.forEach(b => {
                    const v = parseInt(b.dataset.valor, 10);
                    if (v <= estrelasAtuais) {
                        b.classList.add('ativa');
                        b.innerHTML = '<i class="bi bi-star-fill text-amber-400"></i>';
                    } else {
                        b.classList.remove('ativa');
                        b.innerHTML = '<i class="bi bi-star text-stone-600"></i>';
                    }
                });
            });
        }

        // Atualizar botões de "Assistido"
        if (tipo === 'assistido') {
            const botoesAssistido = document.querySelectorAll(`.btn-assistido[data-filme-id="${filmeId}"]`);
            botoesAssistido.forEach(b => {
                if (dados.assistido) {
                    b.classList.add('bg-emerald-950', 'text-emerald-400', 'border-emerald-600');
                    b.classList.remove('bg-stone-900', 'text-stone-400', 'border-stone-700');
                    b.innerHTML = '<i class="bi bi-check2-circle mr-1.5"></i> Assistido';
                } else {
                    b.classList.remove('bg-emerald-950', 'text-emerald-400', 'border-emerald-600');
                    b.classList.add('bg-stone-900', 'text-stone-400', 'border-stone-700');
                    b.innerHTML = '<i class="bi bi-eye mr-1.5"></i> Marcar Assistido';
                }
            });

            // Se a tela estiver configurada para ocultar assistidos, anima a saída do card
            const esconderAssistidosAtivo = document.getElementById('filtro-ocultar-assistidos')?.checked;
            if (esconderAssistidosAtivo && dados.assistido) {
                const card = document.getElementById(`filme-card-${filmeId}`);
                if (card) {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => card.remove(), 400);
                }
            }
        }

        // Atualizar botões de "Gostei"
        if (tipo === 'gostou') {
            const botoesGostei = document.querySelectorAll(`.btn-gostei[data-filme-id="${filmeId}"]`);
            botoesGostei.forEach(b => {
                if (dados.gostou) {
                    b.classList.add('bg-rose-950', 'text-rose-400', 'border-rose-600');
                    b.classList.remove('bg-stone-900', 'text-stone-400', 'border-stone-700');
                    b.innerHTML = '<i class="bi bi-heart-fill mr-1.5 text-rose-500"></i> Gostei';
                } else {
                    b.classList.remove('bg-rose-950', 'text-rose-400', 'border-rose-600');
                    b.classList.add('bg-stone-900', 'text-stone-400', 'border-stone-700');
                    b.innerHTML = '<i class="bi bi-heart mr-1.5"></i> Curtir';
                }
            });
        }

    } catch (erro) {
        window.mostrarToast('erro', 'Erro de conexão com o servidor.');
    }
}

/* ========================================================
   TRATAMENTO DE IMAGENS E BUSCA AUTOMÁTICA DE CARTAZ
   Requisito: "se não tiver a capa no imdb procurar pelo nome do filme e o diretor para encontra-la"
======================================================== */

function inicializarImagensFallback() {
    document.querySelectorAll('img.cartaz-img').forEach(img => {
        img.addEventListener('error', function() {
            const filmeId = this.dataset.filmeId;
            const titulo = this.dataset.titulo || 'Filme';
            const diretor = this.dataset.diretor || 'Cinema Brasileiro';
            
            // Disparar busca de capa em background
            if (filmeId) {
                const fd = new FormData();
                fd.append('filme_id', filmeId);
                fetch('actions/buscar_capa.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if (res.sucesso && res.capa_url) {
                            img.src = res.capa_url;
                        }
                    })
                    .catch(() => {});
            }
            
            // Fallback imediato SVG estilizado retrô anos 70
            this.src = `data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 450" width="100%" height="100%"><rect width="300" height="450" fill="%23241A13"/><rect x="10" y="10" width="280" height="430" fill="none" stroke="%23E5A93C" stroke-width="2" opacity="0.6"/><text x="150" y="210" font-family="sans-serif" font-weight="bold" font-size="18" fill="%23FAF0E6" text-anchor="middle">${encodeURIComponent(titulo)}</text><text x="150" y="240" font-family="serif" font-style="italic" font-size="13" fill="%23E5A93C" text-anchor="middle">${encodeURIComponent(diretor)}</text><text x="150" y="290" font-family="monospace" font-size="11" fill="%23C2410C" text-anchor="middle">CINE PALÁCIO • ANOS 70</text></svg>`;
        }, { once: true });
    });
}
