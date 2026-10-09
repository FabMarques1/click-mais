<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php', true, 303);
    exit;
}

$email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
$senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
$erro = '';

if ($email === '' || $senha === '') {
    $erro = 'Preencha o e-mail e a senha.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = 'Usuário ou senha inválidos.';
}

if ($erro === '') {
    try {
        require_once __DIR__ . '/config/database.php';

        $query = 'SELECT u.id, u.nome, u.sobrenome, u.data_nascimento, u.email, u.senha, t.id AS tipo, ui.id_instituicao
                  FROM tbl_usuario u
                  INNER JOIN tbl_tipo_usuario t ON u.tipo_usuario = t.id
                  LEFT JOIN tbl_usuario_tem_instituicao ui ON u.id = ui.id_usuario
                  WHERE u.email = ?';
        $stmtUser = $conn->prepare($query);
        $stmtUser->bind_param('s', $email);
        $stmtUser->execute();
        $rowUser = $stmtUser->get_result()->fetch_assoc();
        $stmtUser->close();

        if (!$rowUser || !hash_equals($rowUser['senha'], hash('sha256', $senha))) {
            $erro = 'Usuário ou senha incorretos!';
        } else {
            $query = "SELECT e.cep, e.logradouro,
                             COALESCE(e.complemento, 'Sem complemento') AS complemento,
                             e.bairro, e.numero, c.nome AS cidade, es.sigla AS estado
                      FROM tbl_usuario_tem_endereco ue
                      INNER JOIN tbl_endereco e ON ue.id_endereco = e.id
                      INNER JOIN tbl_cidade c ON e.id_cidade = c.id
                      INNER JOIN tbl_estado es ON c.id_estado = es.id
                      WHERE ue.id_usuario = ?";
            $stmtEndereco = $conn->prepare($query);
            $stmtEndereco->bind_param('i', $rowUser['id']);
            $stmtEndereco->execute();
            $rowEndereco = $stmtEndereco->get_result()->fetch_assoc() ?? [];
            $stmtEndereco->close();

            session_regenerate_id(true);

            // Usuário autenticado.
            $_SESSION['login'] = true;
            $_SESSION['id'] = $rowUser['id'];
            $_SESSION['nome'] = $rowUser['nome'];
            $_SESSION['data_nascimento'] = $rowUser['data_nascimento'];
            $_SESSION['sobrenome'] = $rowUser['sobrenome'];
            $_SESSION['email'] = $rowUser['email'];
            $_SESSION['tipo_usuario'] = $rowUser['tipo'];
            $_SESSION['instituicao'] = $rowUser['id_instituicao'] ?? null;

            // O cadastro pode ainda não ter um endereço preenchido.
            foreach (['cep', 'logradouro', 'complemento', 'bairro', 'numero', 'cidade', 'estado'] as $campo) {
                $_SESSION[$campo] = $rowEndereco[$campo] ?? '';
            }

            unset($_SESSION['login_erro'], $_SESSION['login_email'], $_SESSION['erro_toast']['login.php']);
            $conn->close();
            header("Location: index.php");
            exit;
        }

        $conn->close();
    } catch (Exception $e) {
        error_log('Erro no login: ' . $e->getMessage());
        $erro = 'Erro no login, contate o suporte.';
    }
}

// O erro é exibido uma vez no formulário; a senha não é armazenada.
$_SESSION['login_erro'] = $erro;
$_SESSION['login_email'] = $email;
header('Location: login.php', true, 303);
exit;
