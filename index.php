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
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="./" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>

    <h1>Dashboard</h1>

    <a href="./view/">Produtos</a>
    <a href="./view/clientes.php">Clientes</a>
    <a href="./view/users.php">Usuarios</a>

    <br><br>
    <!-- README durante desenvolvimento -->
    <h2>Relação do que ja foi desenvolvido:</h2>
    <p>
        [✔] Sistema de Login<br>
        [✔] Controle e Criação de Usuarios<br>
        [✔] Cadastro de Produtos<br>
        [✔] Cadastro de Estoque<br>
        [] Cadastro de Clientes<br>
        [] Cadastro de Pedidos<br>
        [] Cadastro de Pagamentos<br>
        [] Styles<br>
    </p>
</body>
</html>