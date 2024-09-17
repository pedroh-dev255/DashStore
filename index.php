<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ./login.php");
    }

    //carrega as vareaveis de ambiente
    $env = parse_ini_file('./.env');


    if(isset($_GET['logoff']) && $_GET['logoff'] =='true' ){
        session_destroy();
        header("Location: ./login.php");
    }


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <form action="./" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
</body>
</html>