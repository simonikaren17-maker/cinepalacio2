<?php
/**
 * Cine Palácio - Solicitação de Redefinição de Senha
 * Requisito: "o site deve dar a opção de redefinição de senha para o usuario."
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

$tituloPagina = 'Recuperar Senha';

$linkGerado = $_SESSION['link_recuperacao_gerado'] ?? null;
$emailEnviado = $_SESSION['email_recuperacao_enviado'] ?? null;

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        
        <div class="text-center mb-8">
            <div class="w-14 h-14 mx-auto rounded-full bg-gradient-to-br from-amber-500 to-cine-laranja p-0.5 shadow-lg mb-3">
                <div class="w-full h-full bg-cine-tabaco rounded-full flex items-center justify-center text-amber-400">
                    <i class="bi bi-key-fill text-2xl"></i>
                </div>
            </div>
            <h1 class="font-cartaz text-3xl sm:text-4xl text-white tracking-wider letreiro-neon">
                REDEFINIR SENHA
            </h1>
            <p class="text-xs font-vintage uppercase tracking-wider text-amber-400/90 mt-1">
                Recuperação de Acesso Cine Palácio
            </p>
        </div>

        <?php if ($linkGerado): ?>
            <!-- Painel de Demonstração do Link de Redefinição -->
            <div class="bg-amber-950/80 border-2 border-amber-600 rounded-xl p-5 mb-6 text-left shadow-lg">
                <div class="flex items-center gap-2 text-amber-300 font-bold text-sm mb-2">
                    <i class="bi bi-check-circle-fill text-emerald-400"></i>
                    <span>Link de Redefinição Gerado!</span>
                </div>
                <p class="text-xs text-stone-300 mb-3 leading-relaxed">
                    Instruções para o e-mail: <strong class="text-white"><?= htmlspecialchars($emailEnviado) ?></strong>
                </p>
                <a href="<?= htmlspecialchars($linkGerado) ?>" class="block w-full text-center py-2.5 px-4 rounded-lg bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs tracking-wider transition shadow">
                    <i class="bi bi-shield-lock mr-1"></i> Acessar Tela de Nova Senha Agora &rarr;
                </a>
            </div>
        <?php endif; ?>

        <form action="actions/auth_recuperar.php" method="POST" class="space-y-4">
            
            <div>
                <label for="email" class="block text-xs font-vintage uppercase text-stone-300 mb-1.5">
                    Informe seu E-mail Cadastrado:
                </label>
                <div class="relative">
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           value="<?= htmlspecialchars($emailEnviado ?? '') ?>"
                           placeholder="seu@email.com"
                           class="w-full bg-stone-900 border border-cine-borda rounded-lg pl-10 pr-4 py-2.5 text-sm text-stone-100 placeholder-stone-500 outline-none focus:border-cine-ambar focus:ring-1 focus:ring-cine-ambar transition">
                    <i class="bi bi-envelope absolute left-3.5 top-3 text-stone-500 text-sm"></i>
                </div>
            </div>

            <p class="text-xs text-stone-400 leading-relaxed">
                Um token único e seguro com validade de 1 hora será gerado para que você redefina sua senha com nova criptografia Bcrypt.
            </p>

            <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-cine-ambar to-cine-laranja text-stone-950 font-bold text-sm tracking-wide shadow-lg hover:brightness-110 transition flex items-center justify-center gap-2">
                <i class="bi bi-send-fill text-sm"></i> Gerar Link de Redefinição
            </button>

        </form>

        <div class="mt-8 pt-6 border-t border-stone-800 text-center">
            <a href="login.php" class="text-xs text-stone-400 hover:text-amber-400 transition">
                &larr; Voltar para a tela de Login
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
