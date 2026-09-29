<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>CURRICULO+ | Login</title>

    <link rel="stylesheet" href="css/login.css">

    <link rel="shortcut icon"
        href="assets/img/favicon.png"
        type="image/x-icon">
</head>

<body>

    <main class="login-container">
          <div class="logo">
            CURRICULO<span>+</span>
        </div>

        <form action="enviar-login.php" method="POST">
            <h3>Bem-vindo de volta!</h3>
            <label for="email">
                Digite seu e-mail
            </label>

            <input
                name="email"
                id="email"
                type="email"
                placeholder="seu@email.com"
                autocomplete="email"
                required
            >

            <label for="senha">
                Digite sua senha
            </label>

            <input
                name="senha"
                id="senha"
                type="password"
                placeholder="Digite sua senha"
                autocomplete="current-password"
                required
            >
            <button type="submit">
                Login
            </button>

        </form>

    </main>

<?php require __DIR__ . '/includes/notificacao.php'; ?>
</body>

</html>
