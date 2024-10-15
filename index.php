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
    <link rel="shortcut icon" href="./style/favicon.ico" type="image/x-icon">
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
    <?php
        require("db.php");
        
        $ss = "SELECT * FROM pedidos WHERE status = 0";
        $stmtss = $conn->prepare($ss);
        $stmtss->execute();
        $resultss = $stmtss->get_result();
        $rowss = mysqli_num_rows($resultss);

        if($rowss>0){
            $total=0;
            while($rowsss = mysqli_fetch_assoc($resultss)){
                
                // Busca o valor restante
                $sql_total = "SELECT SUM(preco) AS total_preco FROM pedido_produtos WHERE id_pedido = ".$rowsss['id'].";";
                $res_total = $conn->query($sql_total);
                $totais = $res_total->fetch_assoc();

                $sql2 = "SELECT SUM(valor_pago) AS total_pago FROM pagamentos WHERE id_pedido = ".$rowsss['id'].";";
                $res = $conn->query($sql2);
                $totais2 = $res->fetch_assoc();

                $total += $totais['total_preco'] - $totais2['total_pago'];
            }
        }
        if(isset($total)){
            echo "Total a Receber: R$ " . number_format($total,2,",",".");
        } 
    ?>
    <!-- README durante desenvolvimento -->
    <h2>Relação do que ja foi desenvolvido:</h2>
    <p>
        [✔] Sistema de Login<br>
        [✔] Controle e Criação de Usuarios<br>
        [✔] Cadastro de Produtos<br>
        [✔] Cadastro de Estoque<br>
        [✔] Cadastro de Clientes<br>
        [✔] Cadastro de Pedidos<br>
        [] Cadastro de Pagamentos<br>
        [] Styles<br>
    </p>
</body>
</html>