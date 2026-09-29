<?php

session_start();

if (isset($_SESSION['login'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

?>