<?php

session_start();
$erro = $_SESSION['registro_erro'] ?? '';
$dados = $_SESSION['registro_dados'] ?? [];
unset($_SESSION['registro_erro'], $_SESSION['registro_dados']);
$hoje = (new DateTimeImmutable('today', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CURRÍCULO+ | Registro</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/registro.css?v=5">
    <link
        rel="shortcut icon"
        href="assets/img/favicon.png"
        type="image/x-icon"
    >

</head>
<body>
    <form
        action="enviar-registro.php"
        method="POST"
        id="formRegistro"
    >
        <?php if ($erro !== ''): ?>
            <p class="registro-erro" role="alert">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label for="nome">
                    Primeiro nome *
                </label>

                <input
                    id="nome"
                    name="nome"
                    value="<?= htmlspecialchars($dados['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    type="text"
                    placeholder="Seu nome..."
                    minlength="1"
                    maxlength="40"
                    required
                >

            </div>
            <div class="form-group">
                <label for="sobrenome">
                    Sobrenome
                </label>

                <input
                    id="sobrenome"
                    name="sobrenome"
                    value="<?= htmlspecialchars($dados['sobrenome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    type="text"
                    placeholder="Seu sobrenome..."
                    minlength="1"
                    maxlength="75"
                >

            </div>
        </div>

        <div class="form-group">
            <label for="data_nascimento">
                Data de nascimento *
            </label>

            <input
                id="data_nascimento"
                name="data_nascimento"
                type="date"
                value="<?= htmlspecialchars($dados['data_nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                min="1000-01-01"
                max="<?= $hoje ?>"
                required
            >

            <small
                id="erroData"
                style="color: red; display: none;"
            >
                Informe uma data de nascimento válida.
            </small>

        </div>
        <div class="form-group">
            <label for="email">
                E-mail *
            </label>

            <input
                id="email"
                name="email"
                type="email"
                value="<?= htmlspecialchars($dados['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                maxlength="80"
                placeholder="Seu e-mail..."
                required
            >

        </div>
        <div class="form-group">
            <label for="senha">
                Senha *
            </label>

            <input
                id="senha"
                name="senha"
                type="password"
                minlength="8"
                placeholder="Sua senha..."
                required
            >

        </div>

        <button type="submit">
            Cadastrar
        </button>
        <p>Já tem uma conta? <a class="btn-link" href="login.php">Faça login</a></p>

    </form>

</body>

</html>
