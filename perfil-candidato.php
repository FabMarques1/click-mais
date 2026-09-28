<?php

session_start();

require_once('config/database.php');

header('Content-Type: text/html; charset=utf-8');

// Verifica se o usuário está logado como recrutador
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 2) {
    header('Location: index.php');
    exit;
}

// Recebe o email e vaga do candidato pela URL
$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_SPECIAL_CHARS);
$vaga = filter_input(INPUT_GET, 'vaga', FILTER_SANITIZE_SPECIAL_CHARS);

// Verifica se o nome foi informado
if (!$email) {
    header('Location: ver-curriculos.php');
    exit;
}

try{
    // Busca os dados do candidato
    $sql = "SELECT
                u.id,
                u.nome,
                u.sobrenome,
                u.email,
                u.data_nascimento,
                c.resumo_profissional,
                v.titulo,
                c.curriculo
            FROM tbl_usuario u
            INNER JOIN tbl_curriculo c ON c.id_usuario = u.id
            INNER JOIN tbl_vaga v ON c.id_vaga = v.id
            WHERE email = ? AND titulo = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro ao preparar consulta.");
    }

    $stmt->bind_param('ss', $email, $vaga);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();

        header('Location: ver-curriculos.php');
        exit;
    }

    $candidato = $result->fetch_assoc();

    $stmt->close();

    $sqlEndereco = "SELECT
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

    $stmtEndereco = $conn->prepare($sqlEndereco);

    if (!$stmtEndereco) {
        die("Erro ao preparar consulta.");
    }

    $stmtEndereco->bind_param('i', $candidato['id']);
    $stmtEndereco->execute();

    $endereco = $stmtEndereco->get_result()->fetch_assoc();

    $stmtEndereco->close();
} catch (Exception $e) {
    die("Erro ao ver candidato, contate o suporte.");
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CURRÍCULO+ | Perfil do candidato</title>
    <link rel="stylesheet" href="css/perfil-candidato.css">
    <link
        rel="shortcut icon"
        href="assets/img/favicon.png"
        type="image/x-icon"
    >

</head>

<body>
    <main>
        <a href="ver-curriculos.php">
            ← Voltar para candidatos
        </a>
        <header>
            <h1>Perfil do candidato</h1>

            <p>
                Informações do candidato e currículo enviado.
            </p>

        </header>

        <section>

            <h2>

                <?php

                echo htmlspecialchars(
                    $candidato['nome'] . ' ' . $candidato['sobrenome']
                );

                ?>

            </h2>

            <div>

                <strong>E-mail</strong>

                <span>
                    <?php echo htmlspecialchars($candidato['email']); ?>
                </span>

            </div>

            <div>
                <strong>Data de nascimento</strong>

                <span>
                    <?php
                    echo date(
                        'd/m/Y',
                        strtotime($candidato['data_nascimento'])
                    );
                    ?>
                </span>

            </div>

            <div>
                <strong>Endereço</strong>

                <span>
                    <?php if ($endereco): ?>

                        <?php echo htmlspecialchars(
                            $endereco['logradouro'] . ", " .
                            $endereco['numero'] . ", " .
                            $endereco['complemento'] . ", " .
                            $endereco['bairro'] . ", " .
                            $endereco['cidade'] . " - " .
                            $endereco['estado'] . ", " .
                            substr($endereco['cep'], 0, 5) . "-" .
                            substr($endereco['cep'], 5)
                        ); ?>

                    <?php else: ?>

                        Endereço não cadastrado.

                    <?php endif; ?>
                </span>
            </div>

            <div>
                <strong>Vaga aplicada</strong>
                <span>
                    <?php echo htmlspecialchars($candidato['titulo']); ?>
                </span>
            </div>

            <div>
                <strong>Resumo profissional</strong>

                <span>
                    <?php
                        if(!empty($candidato['resumo_profissional'])) {
                            echo htmlspecialchars($candidato['resumo_profissional']);
                        } else {
                            echo "<i>Sem resumo profissional</i>";
                        }
                    ?>
                </span>

            </div>

        </section>

        <section>
            <h2>Currículo</h2>
            <p>
                O candidato enviou um currículo para esta vaga.
            </p>

            <?php if (!empty($candidato['curriculo'])): ?>

                <a
                    href="<?php echo htmlspecialchars($candidato['curriculo']); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Visualizar currículo
                </a>

            <?php else: ?>

                <p>
                    Este candidato não possui um currículo disponível.
                </p>

            <?php endif; ?>
        </section>

    </main>

</body>
</html>

<?php $conn->close(); ?>