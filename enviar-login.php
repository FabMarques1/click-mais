<?php
session_start();
require_once __DIR__ . '/includes/erros.php';

require_once("config/database.php");

$email = strtolower($_POST['email']);
$senha = $_POST['senha'];

try{
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $query = "SELECT u.id, u.nome, u.sobrenome, u.email, u.senha, u.icone, t.id AS tipo FROM tbl_usuario u INNER JOIN tbl_tipo_usuario t ON u.tipo_usuario = t.id WHERE u.email = ?";
        $stmtUser = $conn->prepare($query);
        $stmtUser->bind_param("s", $email);

        $stmtUser->execute();

        $resultUser = $stmtUser->get_result();

        $stmtUser->close();

        if($resultUser->num_rows > 0) {
            $rowUser = $resultUser->fetch_assoc();

            $query = "SELECT 
                        ue.id_usuario,
                        e.cep,
                        e.logradouro,
                        COALESCE(e.complemento, 'Sem complemento') AS complemento,
                        e.bairro,
                        e.numero,
                        c.nome AS cidade,
                        es.sigla AS estado
                    FROM tbl_usuario_has_tbl_endereco ue
                    INNER JOIN tbl_endereco e
                        ON ue.id_endereco = e.id
                    INNER JOIN tbl_cidade c
                        ON e.id_cidade = c.id
                    INNER JOIN tbl_estado es
                        ON c.id_estado = es.id
                    WHERE ue.id_usuario = ?";
            $stmtEndereco = $conn->prepare($query);
            $stmtEndereco->bind_param("i", $rowUser['id']);

            $stmtEndereco->execute();

            $resultEndereco = $stmtEndereco->get_result();

            $stmtEndereco->close();

            $rowEndereco = $resultEndereco->fetch_assoc();

            $senhaHash = hash('sha256', $senha);

            if($senhaHash === $rowUser['senha']) {

                // Usuário
                $_SESSION['login'] = True;
                $_SESSION['id'] = $rowUser['id'];
                $_SESSION['nome'] = $rowUser['nome'];
                $_SESSION['data_nascimento'] = $rowUser['data_nascimento'];
                $_SESSION['sobrenome'] = $rowUser['sobrenome'];
                $_SESSION['email'] = $rowUser['email'];
                $_SESSION['tipo_usuario'] = $rowUser['tipo'];
                $_SESSION['icone'] = $rowUser['icone'];

                // Endereço
                $_SESSION['cep'] = $rowEndereco['cep'];
                $_SESSION['logradouro'] = $rowEndereco['logradouro'];
                $_SESSION['complemento'] = $rowEndereco['complemento'];
                $_SESSION['bairro'] = $rowEndereco['bairro'];
                $_SESSION['numero'] = $rowEndereco['numero'];
                $_SESSION['cidade'] = $rowEndereco['cidade'];
                $_SESSION['estado'] = $rowEndereco['estado'];

            } else {
                redirecionarComErro("Usuário ou senha incorretos!", 'login.php');
            }

        } else {
            redirecionarComErro("Usuário ou senha incorretos!", 'login.php');
        }

    }
} catch (Exception $e) {
    echo "Erro no login, contate o suporte.";
}

$conn->close();

if ($_SESSION['tipo_usuario'] === 2) {
    header("Location: ver-curriculos.php");
} else{
    header("Location: index.php");
}
?>
