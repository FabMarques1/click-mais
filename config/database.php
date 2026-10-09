<?php

$host = "localhost";
$user = "root";
$password = "&tec77@info!";
$database = "curriculo_mais_db";

try{
    $conn = new MySQLi($host, $user, $password, $database);
    $conn->set_charset("utf8mb4");
    # Conexão bem-sucedida!
} catch (Exception $e) {
    die("Erro na conexão com o banco de dados, contate o suporte.");
}

?>
