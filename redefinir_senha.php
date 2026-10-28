<?php
/**
 * Cine Palácio - Tela de Nova Senha com Token
 * Requisito: "o site deve dar a opção de redefinição de senha para o usuario."
 */

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/auth.php';

$token = trim($_GET['token'] ?? '');
$tituloPagina = 'Nova Senha';

$tokenValido = false;
$usuarioEmail = '';

if (!empty($token)) {
    $agora = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT id, email FROM usuarios WHERE token_recuperacao = :token AND token_expira >= :agora");
    $stmt->execute([':token' => $token, ':agora' => $agora]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $tokenValido = true;
        $usuarioEmail = $usuario['email'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-cine-card border border-cine-borda rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        
        <div class="text-center mb-8">
            <div class="w-14 h-14 mx-auto rounded-full bg-gradient-to-br from-amber-500 to-cine-laranja p-0.5 shadow-lg mb-3">
                <div class="w-full h-full bg-cine-tabaco rounded-full flex items-center justify-center text-amber-400">
                    <i class="bi bi-shield-lock-fill text-2xl"></i>
                </div>
            </div>
            <h1 class="font-cartaz text-3xl sm:text-4xl text-white tracking-wider letreiro-neon">
                CRIAR NOVA SENHA
            </h1>
            <p class="text-xs font-vintage uppercase tracking-wider text-amber-400/90 mt-1">
                Atualização Criptográfica Bcrypt
            </p>
        </div>

        <?php if (!$tokenValido): ?>
            <div class="bg-rose-950/70 border border-rose-700 p-5 rounded-xl text-center space-y-4">
                <i class="bi bi-exclamation-triangle-fill text-rose-500 text-3xl"></i>
                <h3 class="text-rose-200 font-bold text-sm">Token Inválido ou Expirado</h3>
                <p class="text-xs text-stone-300 leading-relaxed">
                    Este link de redefinição não é mais válido ou o prazo de 1 hora expirou por questões de segurança.
                </p>
                <a href="esqueci_senha.php" class="inline-block py-2 px-4 rounded-lg bg-amber-600 hover:bg-amber-500 text-stone-950 font-bold text-xs tracking-wider transition">
                    Solicitar Novo Link de Redefinição &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="mb-5 p-3 rounded-lg bg-stone-950/80 border border-stone-800 text-xs text-stone-300">
                Redefinindo senha para a conta: <strong class="text-amber-400"><?= htmlspecialchars($usuarioEmail) ?></strong>
            </div>

            <form action="actions/auth_redefinir.php" method="POST" class="space-y-4">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div>
                    <label for="senha" class="block text-xs font-vintage uppercase text-stone-300 mb-1.5">
                        Nova Senha (Mínimo 6 caracteres):
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="senha" 
                               name="senha" 
                               required 
                               minlength="6"
                               placeholder="••••••••"
                               class="w-full bg-stone-900 border border-cine-borda rounded-lg pl-10 pr-4 py-2.5 text-sm text-stone-100 placeholder-stone-500 outline-none focus:border-cine-ambar focus:ring-1 focus:ring-cine-ambar transition">
                        <i class="bi bi-lock absolute left-3.5 top-3 text-stone-500 text-sm"></i>
                    </div>
                </div>

                <div>
                    <label for="confirmar_senha" class="block text-xs font-vintage uppercase text-stone-300 mb-1.5">
                        Confirmar Nova Senha:
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="confirmar_senha" 
                               name="confirmar_senha" 
                               required 
                               minlength="6"
                               placeholder="••••••••"
                               class="w-full bg-stone-900 border border-cine-borda rounded-lg pl-10 pr-4 py-2.5 text-sm text-stone-100 placeholder-stone-500 outline-none focus:border-cine-ambar focus:ring-1 focus:ring-cine-ambar transition">
                        <i class="bi bi-shield-check absolute left-3.5 top-3 text-stone-500 text-sm"></i>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-cine-ambar to-cine-laranja text-stone-950 font-bold text-sm tracking-wide shadow-lg hover:brightness-110 transition flex items-center justify-center gap-2 mt-2">
                    <i class="bi bi-save2-fill text-sm"></i> Salvar Nova Senha
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-8 pt-6 border-t border-stone-800 text-center">
            <a href="login.php" class="text-xs text-stone-400 hover:text-amber-400 transition">
                &larr; Voltar para a tela de Login
            </a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
