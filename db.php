<?php

    //carrega as vareaveis de ambiente
    $env = parse_ini_file('./.env');

    // Create connection
    $conn = mysqli_connect($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
    // Check connection

    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

?>