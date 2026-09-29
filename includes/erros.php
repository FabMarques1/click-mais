<?php

function redirecionarComErro(string $mensagem, string $destino): void
{
    $_SESSION['erro_toast'][$destino] = $mensagem;
    header('Location: ' . $destino, true, 303);
    exit;
}
