<?php

require_once("config/database.php");

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome            = ucfirst(trim($_POST['nome'] ?? ''));
    $sobrenome       = ucfirst(trim($_POST['sobrenome'] ?? ''));
    $data_nascimento = $_POST['data_nascimento'] ?? '';
    $email           = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha           = $_POST['senha'];
    $cep             = preg_replace('/\D/', '', $_POST['cep'] ?? '');
    $cidade          = (int) ($_POST['cidade'] ?? 0);
    $logradouro      = trim($_POST['logradouro'] ?? '');
    $numero          = trim($_POST['numero'] ?? '');
    $bairro          = trim($_POST['bairro'] ?? '');
    $complemento     = trim($_POST['complemento'] ?? '');
    $complemento     = $complemento === '' ? null : $complemento;

    $nascimento = DateTime::createFromFormat('Y-m-d', $data_nascimento);

    if ($nome === '' || $sobrenome === '' || $logradouro === '' || $numero === '' || $bairro === '') {
        $erro = "Preencha todos os campos obrigatórios.";
    } elseif (!$email) {
        $erro = "Email inválido.";
    } elseif (mb_strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres.";
    } elseif (strlen($cep) !== 8) {
        $erro = "CEP inválido.";
    } elseif (!$cidade) {
        $erro = "Selecione o estado e a cidade.";
    } elseif (!$nascimento || $nascimento->format('Y-m-d') !== $data_nascimento || $nascimento > new DateTime()) {
        $erro = "Data de nascimento inválida.";
    } elseif (mb_strlen($logradouro) > 50 || mb_strlen($bairro) > 35) {
        $erro = "Logradouro ou bairro excede o tamanho permitido.";
    }


    if ($erro === "") {

        try {

            $email = strtolower($email);

            // Email já cadastrado?
            $stmt = $conn->prepare("SELECT id FROM tbl_usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $emailExiste = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            // Cidade existe?
            $stmt = $conn->prepare("SELECT id FROM tbl_cidade WHERE id = ?");
            $stmt->bind_param("i", $cidade);
            $stmt->execute();
            $cidadeExiste = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($emailExiste) {

                $erro = "Este email já está cadastrado.";

            } elseif (!$cidadeExiste) {

                $erro = "Cidade inválida.";

            } else {

                $senhaHash = hash('sha256', $senha);

                // Os três inserts precisam acontecer juntos
                $conn->begin_transaction();

                // 1. Usuário
                $stmt = $conn->prepare("INSERT INTO tbl_usuario (nome, sobrenome, data_nascimento, email, senha)
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nome, $sobrenome, $data_nascimento, $email, $senhaHash);
                $stmt->execute();
                $id_usuario = $conn->insert_id;
                $stmt->close();

                // 2. Endereço
                $stmt = $conn->prepare("INSERT INTO tbl_endereco (cep, logradouro, complemento, bairro, numero, id_cidade)
                                        VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $cep, $logradouro, $complemento, $bairro, $numero, $cidade);
                $stmt->execute();
                $id_endereco = $conn->insert_id;
                $stmt->close();

                // 3. Relação usuário <-> endereço
                $stmt = $conn->prepare("INSERT INTO tbl_usuario_has_tbl_endereco (id_usuario, id_endereco)
                                        VALUES (?, ?)");
                $stmt->bind_param("ii", $id_usuario, $id_endereco);
                $stmt->execute();
                $stmt->close();

                $conn->commit();

                header("Location: index.php");
                exit;

            }

        } catch (mysqli_sql_exception $e) {

            $conn->rollback();

            error_log($e->getMessage());

            // 1062 = email duplicado (duas requisições ao mesmo tempo)
            $erro = $e->getCode() === 1062
                ? "Este email já está cadastrado."
                : "Erro ao registrar, contate o suporte.";

        }
    }
}