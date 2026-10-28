<?php
/**
 * Cine Palácio - Tela de Login
 * Requisitos:
 * - Senhas tratadas com bcrypt (password_verify)
 * - Estética anos 70 / O Agente Secreto / Cidade de Deus
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

if (usuarioLogado()) {
    header('Location: meu_espaco.php');
    exit;
}

$tituloPagina = 'Entrar na Conta';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        
        <!-- Faixa Vintage Topo -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 mx-auto rounded-full bg-gradient-to-br from-amber-500 to-cine-laranja p-0.5 shadow-lg mb-3">
                <div class="w-full h-full bg-cine-tabaco rounded-full flex items-center justify-center text-amber-400">
                    <i class="bi bi-person-fill text-2xl"></i>
                </div>
            </div>
            <h1 class="font-cartaz text-3xl sm:text-4xl text-white tracking-wider letreiro-neon">
                ENTRAR NO CINE PALÁCIO
            </h1>
            <p class="text-xs font-vintage uppercase tracking-wider text-amber-400/90 mt-1">
                Acesse seu acervo e avaliações pessoais
            </p>
        </div>

        <!-- Formulário de Login Seguro -->
        <form action="actions/auth_login.php" method="POST" class="space-y-5">
            
            <div>
                <label for="email" class="block text-xs font-vintage uppercase text-stone-300 mb-1.5">
                    Endereço de E-mail:
                </label>
                <div class="relative">
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="seu@email.com"
                           class="w-full bg-stone-900 border border-cine-borda rounded-lg pl-10 pr-4 py-2.5 text-sm text-stone-100 placeholder-stone-500 outline-none focus:border-cine-ambar focus:ring-1 focus:ring-cine-ambar transition">
                    <i class="bi bi-envelope absolute left-3.5 top-3 text-stone-500 text-sm"></i>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="senha" class="block text-xs font-vintage uppercase text-stone-300">
                        Senha de Acesso:
                    </label>
                    <a href="esqueci_senha.php" class="text-xs text-amber-400 hover:text-amber-300 transition">
                        Esqueceu a senha?
                    </a>
                </div>
                <div class="relative">
                    <input type="password" 
                           id="senha" 
                           name="senha" 
                           required 
                           placeholder="••••••••"
                           class="w-full bg-stone-900 border border-cine-borda rounded-lg pl-10 pr-4 py-2.5 text-sm text-stone-100 placeholder-stone-500 outline-none focus:border-cine-ambar focus:ring-1 focus:ring-cine-ambar transition">
                    <i class="bi bi-lock absolute left-3.5 top-3 text-stone-500 text-sm"></i>
                </div>
            </div>

            <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-cine-ambar to-cine-laranja text-stone-950 font-bold text-sm tracking-wide shadow-lg hover:brightness-110 transition flex items-center justify-center gap-2">
                <i class="bi bi-box-arrow-in-right text-base"></i> Entrar na Minha Conta
            </button>

        </form>

        <!-- Rodapé do Card -->
        <div class="mt-8 pt-6 border-t border-stone-800 text-center">
            <p class="text-xs text-stone-400">
                Ainda não tem conta?
                <a href="cadastro.php" class="text-amber-400 hover:text-amber-300 font-bold ml-1 transition">
                    Cadastre-se grátis &rarr;
                </a>
            </p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
