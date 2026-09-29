<?php

require_once("config/database.php");
header("Content-Type: application/json; charset=utf-8");


try {

    $sigla = strtoupper(trim($_GET['estado'] ?? ''));

    if ($sigla === '') {
        echo json_encode([]);
        exit;
    }

    $sql = "SELECT c.id, c.nome
            FROM tbl_cidade c
            INNER JOIN tbl_estado e ON c.id_estado = e.id
            WHERE e.sigla = ?
            ORDER BY c.nome ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $sigla);
    $stmt->execute();

    $result = $stmt->get_result();
    $cidades = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    echo json_encode($cidades);

} catch (mysqli_sql_exception $e) {

    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao buscar cidades."
    ]);

}