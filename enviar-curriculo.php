<?php
session_start();

require_once("config/database.php");

$idUsuario = $_SESSION['id'];
$resumoProfissional = $_POST['resumoProfissional'];
$curriculo = $_FILES['curriculo'];
$idVaga = $_POST['vaga'];

$query = "SELECT id_usuario, id_vaga FROM tbl_curriculo WHERE id_usuario = ? AND id_vaga = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $idUsuario, $idVaga);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0) {
    die("Você já enviou currículo para essa vaga!");
}

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
            'pdf'  => 'application/pdf'
        ];

        if (!array_key_exists($extensao, $mimesPermitidos)) {
            die("Tipo de arquivo inválido.");
        }

        $tipoMime = mime_content_type($caminhoTemp);
        if ($tipoMime !== $mimesPermitidos[$extensao]) {
            die("Tipo de arquivo inválido.");
        }

        if ($tamanho > $tamanhoMax) {
            die("Tamanho de arquivo excedido, permitido apenas 2MB.");
        }

        $hash16       = bin2hex(random_bytes(8));
        $novoNome     = $hash16 . '.' . $extensao;
        $caminhoFinal = $pastaDestino . $novoNome;

        if (move_uploaded_file($caminhoTemp, $caminhoFinal)) {
            
            $sql = "INSERT INTO tbl_curriculo (resumo_profissional, curriculo, id_usuario, id_vaga) VALUES
                    (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $resumoProfissional, $caminhoFinal, $idUsuario, $idVaga);

            if ($stmt->execute()) {
                header("Location: index.php");
            } else {
                die("Erro ao enviar currículo, contate o suporte.");
            }

            $stmt->close();

        } else {
            die("Erro ao enviar currículo, contate o suporte.");
        }

    } else {
        die("Erro ao enviar currículo, contate o suporte.");
    }
} catch (Exception $e) {
    echo "Erro ao enviar currículo, contate o suporte.";
}

$conn->close();

?>