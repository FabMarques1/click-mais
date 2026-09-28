<?php

require_once("config/database.php");

$query = "SELECT id, nome FROM tbl_cidade";

$stmt = $conn->prepare($query);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CURRÍCULO+ | Registro</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/registro.css?v=2">
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
        <div class="form-row">
            <div class="form-group">
                <label for="nome">
                    Primeiro nome *
                </label>

                <input
                    id="nome"
                    name="nome"
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
                placeholder="Sua senha..."
                required
            >

        </div>

        <div class="form-group">
            <label for="cep">
                CEP *
            </label>

            <input
                id="cep"
                name="cep"
                type="text"
                placeholder="00000-000"
                maxlength="9"
                required
            >

        </div>

        <div class="form-group">
            <label for="estado">
                Estado *
            </label>

            <select
                id="estado"
                name="estado"
                required
            >

                <option value="">
                    Selecione o estado
                </option>

            </select>
        </div>

        <div class="form-group">
            <label for="cidade">
                Cidade *
            </label>

            <select
                id="cidade"
                name="cidade"
                required
                disabled
            >

                <option value="">
                    Selecione o estado primeiro
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="logradouro">
                Logradouro *
            </label>

            <input
                id="logradouro"
                name="logradouro"
                type="text"
                placeholder="Rua, avenida..."
                maxlength="150"
                required
            >

        </div>


        <div class="form-group">
            <label for="bairro">
                Bairro *
            </label>

            <input
                id="bairro"
                name="bairro"
                type="text"
                placeholder="Seu bairro..."
                maxlength="100"
                required
            >

        </div>

        <div class="form-group">
            <label for="complemento">
                Complemento
            </label>

            <input
                id="complemento"
                name="complemento"
                type="text"
                placeholder="Apartamento, bloco, casa..."
                maxlength="150"
            >

        </div>

        <div class="form-group">
            <label for="numero">
                Número
            </label>

            <input
                id="numero"
                name="numero"
                type="number"
                placeholder="Número de sua residência..."
            >

        </div>

        <button type="submit">
            Cadastrar
        </button>

    </form>


    <script src="js/registro.js"></script>

</body>

</html>
