<?php

require_once("config/database.php");
header("Content-Type: application/json; charset=utf-8");


try {

    $sql = "SELECT sigla, nome FROM tbl_estado ORDER BY nome ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->get_result();

    $estados = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($estados);

} catch (mysqli_sql_exception $e) {

    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao buscar estados."
    ]);

}