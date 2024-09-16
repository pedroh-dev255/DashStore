<?php
    $env = parse_ini_file('./.env');



    echo $env['DB_HOST'];

?>