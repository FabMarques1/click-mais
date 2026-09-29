<?php
session_start();

require_once("config/database.php");

if(isset($_SESSION['login'])) {
    header("Location: index.php");
}

$email = strtolower($_POST['email']);
$senha = $_POST['senha'];

try{
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $query = "SELECT u.id, u.nome, u.sobrenome, u.email, u.senha, t.id AS tipo FROM tbl_usuario u INNER JOIN tbl_tipo_usuario t ON u.tipo_usuario = t.id WHERE u.email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        $stmt->close();

        if($result->num_rows > 0) {

            while ($row = $result->fetch_assoc()) {
                $senhaHash = hash('sha256', $senha);

                if($senhaHash === $row['senha']) {

                    $_SESSION['logado'] = True;
                    $_SESSION['id'] = $row['id'];
                    $_SESSION['nome'] = $row['nome'];
                    $_SESSION['sobrenome'] = $row['sobrenome'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['cidade'] = $row['id_cidade'];
                    $_SESSION['tipo_usuario'] = $row['tipo'];

                } else {
                    die("Usuário ou senha incorretos!");
                }
            }

        } else {
            die("Usuário ou senha incorretos!");
        }

    }
} catch (Exception $e) {
    echo "Erro no login, contate o suporte.";
}

$conn->close();
header("Location: index.php");

?>
