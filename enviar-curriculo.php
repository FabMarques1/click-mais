<?php
session_start();
require_once __DIR__ . '/includes/erros.php';

require_once("config/database.php");

$idUsuario = $_SESSION['id'];
$resumoProfissional = $_POST['resumoProfissional'];
$curriculo = $_FILES['curriculo'];

$query = "SELECT id_usuario FROM tbl_curriculo WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0) {
    redirecionarComErro("Você já tem um currículo, vá para as configurações para modificá-lo.", 'formulario.php');
}

$stmt->close();

try{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($curriculo) && $curriculo['error'] === UPLOAD_ERR_OK) {
            
        $pastaDestino = 'curriculos/';

        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        $nomeOriginal = $curriculo['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $caminhoTemp = $curriculo['tmp_name'];
        $tamanho = $curriculo['size'];

        $tamanhoMax = 2 * 1024 * 1024;

        $mimesPermitidos = [
            'pdf'  => ['application/pdf'],
            'doc'  => ['application/msword'],
            'docx' => [
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip',
            ],
        ];

        if (!array_key_exists($extensao, $mimesPermitidos)) {
            redirecionarComErro("Tipo de arquivo inválido.", 'formulario.php');
        }

        $tipoMime = mime_content_type($caminhoTemp);
        if (!in_array($tipoMime, $mimesPermitidos[$extensao], true)) {
            redirecionarComErro("Tipo de arquivo inválido.", 'formulario.php');
        }

        if ($tamanho > $tamanhoMax) {
            redirecionarComErro("Tamanho de arquivo excedido, permitido apenas 2MB.", 'formulario.php');
        }

        $hash16       = bin2hex(random_bytes(8));
        $novoNome     = $hash16 . '.' . $extensao;
        $caminhoFinal = $pastaDestino . $novoNome;

        if (move_uploaded_file($caminhoTemp, $caminhoFinal)) {
            
            $sql = "INSERT INTO tbl_curriculo (resumo_profissional, curriculo, id_usuario) VALUES
                    (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $resumoProfissional, $caminhoFinal, $idUsuario);

            if ($stmt->execute()) {
                header("Location: index.php");
            } else {
                redirecionarComErro("Erro ao enviar currículo, contate o suporte.", 'formulario.php');
            }

            $stmt->close();

        } else {
            redirecionarComErro("Erro ao enviar currículo, contate o suporte.", 'formulario.php');
        }

    } else {
        redirecionarComErro("Erro ao enviar currículo, contate o suporte.", 'formulario.php');
    }
} catch (Exception $e) {
    echo "Erro ao enviar currículo, contate o suporte." . $e->getMessage();
}

$conn->close();