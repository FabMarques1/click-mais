<?php
session_start();
$erro = $_SESSION['login_erro'] ?? $_SESSION['erro_toast']['login.php'] ?? '';
$email = $_SESSION['login_email'] ?? '';
unset($_SESSION['login_erro'], $_SESSION['login_email'], $_SESSION['erro_toast']['login.php']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Click+ | Login</title>

    <link rel="stylesheet" href="css/output.css">
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
</head>

<body class="bg-background-light text-text">

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-12">

        <!-- Detalhes de fundo -->
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>

        
        <div class="relative w-full max-w-md">

            <div class="mb-8 text-center">
                <a href="index.php" class="text-3xl font-extrabold tracking-tight text-primary">
                    click<span class="text-text">+</span>
                </a>
            </div>

            <form action="enviar-login.php" method="POST" class="rounded-3xl border border-border bg-white p-8 shadow-xl shadow-primary/5 sm:p-10">

                <h3 class="text-2xl font-extrabold tracking-tight">Bem-vindo de volta!</h3>
                <p class="mt-1 text-sm text-text-secondary">Entre para enviar seu currículo e acompanhar as vagas.</p>

<?php if ($erro !== ''): ?>
                <p class="mt-6 flex items-start gap-2 rounded-xl border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-medium text-danger" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
                </p>
<?php endif; ?>

                <div class="mt-6">
                    <label for="email" class="mb-2 block text-sm font-medium">
                        Digite seu e-mail
                    </label>

                    <input
                        name="email"
                        id="email"
                        type="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="seu@email.com"
                        autocomplete="email"
                        required
                        class="w-full rounded-xl border border-border px-4 py-3 text-sm outline-none transition placeholder:text-text-muted focus:border-primary focus:ring-2 focus:ring-primary/20"
                    >
                </div>

                <div class="mt-5">
                    <label for="senha" class="mb-2 block text-sm font-medium">
                        Digite sua senha
                    </label>

                    <div class="relative">
                        <input
                            name="senha"
                            id="senha"
                            type="password"
                            placeholder="Digite sua senha"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-xl border border-border py-3 pl-4 pr-12 text-sm outline-none transition placeholder:text-text-muted focus:border-primary focus:ring-2 focus:ring-primary/20"
                        >

                        <button type="button" id="alternar-senha" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-text-muted transition hover:text-primary" aria-label="Mostrar senha">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="mt-8 w-full rounded-xl bg-primary px-5 py-3.5 font-semibold text-white transition hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-2">
                    Login
                </button>

                <p class="mt-6 text-center text-sm text-text-secondary">
                    Não tem uma conta?
                    <a class="btn-link font-semibold text-primary transition hover:text-primary-dark" href="registro.php">Cadastre-se</a>
                </p>

            </form>

        </div>
    </main>

    <script>
        const campoSenha = document.getElementById("senha");
        const botaoSenha = document.getElementById("alternar-senha");

        botaoSenha.addEventListener("click", function () {
            const mostrar = campoSenha.type === "password";
            campoSenha.type = mostrar ? "text" : "password";
            botaoSenha.setAttribute("aria-label", mostrar ? "Ocultar senha" : "Mostrar senha");
        });
    </script>

</body>

</html>