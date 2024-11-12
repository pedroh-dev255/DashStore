<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    //carrega as vareaveis de ambiente
    $env = parse_ini_file('.env');

    // Create connection
    $conn = mysqli_connect($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);

    // Função para verificar a conexão
    function checkConnection($conn,$ponto) {
        if (!$conn) {
            header("Location: ".$ponto."/error.php"); // Redireciona para a página de erro
            exit(); // Encerra o script para evitar execuções adicionais
        }
    }

    //checkConnection($conn);
?>