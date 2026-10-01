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

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>CURRICULO+ | Login</title>

    <link rel="stylesheet" href="css/login.css?v=2">

    <link rel="shortcut icon"
        href="assets/img/favicon.png"
        type="image/x-icon">
</head>

<body>

    <main class="login-container">
          <div class="logo">
            <a href="index.php">CURRICULO<span>+</span></a>
        </div>

        <form action="enviar-login.php" method="POST">
            <?php if ($erro !== ''): ?>
                <p class="login-erro" role="alert">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>
            <h3>Bem-vindo de volta!</h3>
            <label for="email">
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
            <p>Não tem uma conta? <a class="btn-link" href="registro.php">Cadastre-se</a></p>

        </form>

    </main>

</body>

</html>
