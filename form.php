<?php
session_start();

require_once("config/database.php");

if(isset($_SESSION['login'])) {
    $nome = $_SESSION['nome'];
    $sobrenome = $_SESSION['sobrenome'];
    $email = $_SESSION['email'];
    $tipo = $_SESSION['tipo_usuario'];

    $cep = $_SESSION['cep'];
    $logradouro = $_SESSION['logradouro'];
    $complemento = $_SESSION['complemento'];
    $bairro = $_SESSION['bairro'];
    $numero = $_SESSION['numero'];
    $cidade = $_SESSION['cidade'];
    $estado = $_SESSION['estado'];
} else {
    header("Location: login.php");
    exit;
}

$query = "SELECT nome FROM tbl_cidade WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cidade);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$queryVagas = "SELECT id, titulo FROM tbl_vaga";
$stmtVagas = $conn->prepare($queryVagas);
$stmtVagas->execute();

$resultVagas = $stmtVagas->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currículo+ | Envio de currículo</title>
    <link rel="stylesheet" href="css/style.css?v=1">
    <link rel="stylesheet" href="css/form.css">
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
</head>
<body class="curriculo-page">

<main class="curriculo-main">
    <section class="curriculo-section">
        <div class="curriculo-container">

            <div class="curriculo-info">
                <p class="tag">TRABALHE CONOSCO</p>
                <h1>Envie seu currículo <span>e faça parte do time</span></h1>
                <p>Preencha o formulário ao lado com seus dados e anexe seu currículo. Nossa equipe de recrutamento entrará em contato assim que houver uma vaga compatível com seu perfil.</p>

                <div class="curriculo-benefits">
                    <div>
                        <strong>01</strong>
                        <span>Cadastro rápido e simples</span>
                    </div>
                    <div>
                        <strong>02</strong>
                        <span>Análise por recrutadores especializados</span>
                    </div>
                    <div>
                        <strong>03</strong>
                        <span>Oportunidades em toda a região</span>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <span>FORMULÁRIO</span>
                    <h2>Seus dados</h2>
                    <p>Confira se seus dados estão certos antes de enviar.</p>
                </div>

                <form action="enviar-curriculo.php" method="POST" enctype="multipart/form-data">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Primeiro nome</label>
                            <i><?php echo htmlspecialchars($nome); ?></i>
                        </div>

                        <div class="form-group">
                            <label for="sobrenome">Sobrenome</label>
                            <i><?php echo htmlspecialchars($sobrenome); ?></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <i><?php echo htmlspecialchars($email); ?></i>
                    </div>

                    <div class="form-group">
                        <label for="endereco">Endereço</label>
                        <i>
                            <?php 
                                if(empty($complemento)) {
                                    echo htmlspecialchars($logradouro . ", " . $numero . ", " . $bairro . ", " . $cidade . " - " . $estado . ", " . substr($cep, 0, 5) . "-" . substr($cep, 5));
                                } else if (empty($numero)) {
                                    echo htmlspecialchars($logradouro . ", " . $complemento . ", " . $bairro . ", " . $cidade . " - " . $estado . ", " . substr($cep, 0, 5) . "-" . substr($cep, 5));
                                } else if (empty($numero) && empty($complemento)) {
                                    echo htmlspecialchars($logradouro . ", " . $bairro . ", " . $cidade . " - " . $estado . ", " . substr($cep, 0, 5) . "-" . substr($cep, 5));
                                } else {
                                    echo htmlspecialchars($logradouro . ", " . $numero . ", " . $complemento . ", " . $bairro . ", " . $cidade . " - " . $estado . ", " . substr($cep, 0, 5) . "-" . substr($cep, 5));
                                }
                            ?>
                        </i>
                    </div>

                    <div class="form-group">
                        <label for="resumoProfissional">Resumo profissional</label>
                        <textarea name="resumoProfissional" id="resumoProfissional" placeholder="Conte-nos um pouco sobre sua experiência na área..." maxlength="200"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="vaga">Vagas</label>
                        <select name="vaga" id="vaga">
                            <?php while($row = $resultVagas->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['titulo']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="curriculo">Currículo *</label>
                        <div class="file-area" id="fileArea">
                            <div class="file-icon">&#128196;</div>
                            <div>
                                <strong id="fileText">Clique ou arraste seu arquivo aqui</strong>
                                <p id="fileSubtext">Prévia: PDF, DOC ou DOCX até 2MB. Envio: PDF.</p>
                            </div>
                            <input name="curriculo" id="curriculo" type="file" accept=".pdf,.doc,.docx" aria-describedby="fileSubtext fileError" required>
                        </div>
                        <p id="fileError" class="file-error" role="alert"></p>
                        <section id="previewContainer" class="curriculo-preview" aria-labelledby="previewTitle" hidden>
                            <h3 id="previewTitle">Prévia do currículo</h3>
                            <p id="previewStatus" role="status"></p>
                            <iframe id="pdfPreview" title="Prévia do currículo em PDF" hidden></iframe>
                            <pre id="textPreview" tabindex="0" aria-label="Texto do currículo" hidden></pre>
                            <a id="previewLink" target="_blank" rel="noopener" hidden>Abrir PDF em outra aba</a>
                        </section>
                    </div>

                    <button type="submit" class="submit-button">Enviar currículo</button>

                    <p class="privacy-text">Seus dados serão utilizados apenas para fins de recrutamento e seleção, conforme nossa política de privacidade.</p>

                </form>
            </div>

        </div>
    </section>
</main>

<footer>
    <div class="container">
        <p>CURRÍCULO<span>+</span></p>
        <small>&copy; 2026 Todos os direitos reservados.</small>
    </div>
</footer>

<script src="js/vendor/docToText.js" defer></script>
<script src="js/vendor/mammoth.browser.min.js" defer></script>
<script src="js/curriculo.js" defer></script>

<?php require __DIR__ . '/includes/notificacao.php'; ?>
</body>
</html>

<?php
$stmt->close();
$stmtVagas->close();
$conn->close();
?>
