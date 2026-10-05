<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: registro.php", true, 303);
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$nome            = is_string($_POST['nome'] ?? null) ? strtolower(ucfirst(trim($_POST['nome']))) : '';
$sobrenome       = is_string($_POST['sobrenome'] ?? null) ? ucfirst(trim($_POST['sobrenome'])) : '';
$data_nascimento = is_string($_POST['data_nascimento'] ?? null) ? $_POST['data_nascimento'] : '';
$email           = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
$senha           = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

$fuso = new DateTimeZone('America/Sao_Paulo');
$nascimento = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $data_nascimento)
    ? DateTimeImmutable::createFromFormat('!Y-m-d', $data_nascimento, $fuso)
    : false;

if ($nome === '' || $data_nascimento === '' || $email === '' || $senha === '') {
        $erro = "Preencha todos os campos obrigatórios.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
} elseif (!$nascimento || $nascimento->format('Y-m-d') !== $data_nascimento
    || $data_nascimento < '1000-01-01'
    || $nascimento > new DateTimeImmutable('today', $fuso)) {
    $erro = "Informe uma data de nascimento válida, que não seja futura.";
} elseif ($nascimento->diff(new DateTimeImmutable('today', $fuso))->y < 16) {
    $erro = 'Não é possível se registrar no site, pois a lei permite o cadastro apenas para pessoas com 16 anos ou mais.';
} elseif (mb_strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres.";
} elseif (mb_strlen($nome) > 40 || mb_strlen($sobrenome) > 75 || mb_strlen($email) > 80) {
    $erro = "Nome, sobrenome ou email excede o tamanho permitido.";
    }


    if ($erro === "") {

    require_once("config/database.php");
    $transacaoIniciada = false;

        try {

            // Email já cadastrado?
            $stmt = $conn->prepare("SELECT id FROM tbl_usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $emailExiste = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($emailExiste) {

                $erro = "Este email já está cadastrado.";

            } else {

                $senhaHash = hash('sha256', $senha);

                $conn->begin_transaction();
                $transacaoIniciada = true;

                // 1. Usuário
                $stmt = $conn->prepare("INSERT INTO tbl_usuario (nome, sobrenome, data_nascimento, email, senha)
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nome, $sobrenome, $data_nascimento, $email, $senhaHash);
                $stmt->execute();

                $stmt->close();

                $conn->commit();
            $transacaoIniciada = false;
            unset($_SESSION['registro_erro'], $_SESSION['registro_dados']);

            header("Location: index.php", true, 303);
                exit;

            }

        } catch (Exception $e) {

        if ($transacaoIniciada) {
            $conn->rollback();
        }

            error_log($e->getMessage());

            // 1062 = email duplicado (duas requisições ao mesmo tempo)
            $erro = $e->getCode() === 1062
                ? "Este email já está cadastrado."
                : "Erro ao registrar, contate o suporte.";

        }
    }

// Todo erro volta ao formulário; a senha nunca é guardada na sessão.
$_SESSION['registro_erro'] = $erro;
$_SESSION['registro_dados'] = compact('nome', 'sobrenome', 'data_nascimento', 'email');
header("Location: registro.php", true, 303);
exit;
}
