<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }

    if(!isset($_GET['id'])  || !is_numeric($_GET['id'])){
        header("Location: ./clientes.php");
    }

    require("../db.php");
    if (isset($_GET['id'])) {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_GET['id']);
    } else {
        header("Location: clientes.php");
        exit();
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = mysqli_num_rows($result);

    //caso o resultado seja diferente de 1 ele retorna para a view dos clientes
    if($rows != 1){
        header("Location: clientes.php");
        exit();
    }

    $ss = "SELECT * FROM pedidos WHERE id_cliente = ? AND status = 0";
    $stmtss = $conn->prepare($ss);
    $stmtss->bind_param('i', $_GET['id']);
    $stmtss->execute();
    $resultss = $stmtss->get_result();
    $rowss = mysqli_num_rows($resultss);

    if($rowss>0){
        $total=0;
        while($rowsss = mysqli_fetch_assoc($resultss)){
            $sql_total = "SELECT SUM(preco) AS total_preco FROM pedido_produtos WHERE id_pedido = ".$rowsss['id'].";";
            $res_total = $conn->query($sql_total);
            $totais = $res_total->fetch_assoc();

            $sql2 = "SELECT SUM(valor_pago) AS total_pago FROM pagamentos WHERE id_pedido = ".$rowsss['id'].";";
            $res = $conn->query($sql2);
            $totais2 = $res->fetch_assoc();

            $total += $totais['total_preco'] - $totais2['total_pago'];
        }
    }
        

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./style/geral.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente</title>
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "ovixemoovg");
    </script>
    <style>
        .botoes li {
            margin-right: 10px;
        }

        .botoes li a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e7d8e9;
            color: rgb(46, 46, 46);
            text-decoration: none;
            border-radius: 20px;
            position: relative;
            transition: background-color 0.3s ease;
            background-repeat: no-repeat;
            background-position: 10px center; /* Ajusta a posição do ícone */
            background-size: 20px; /* Ajusta o tamanho do ícone */
            padding-left: 40px; /* Espaço para o ícone */
        }

        .botoes li:nth-child(1) a {
            background-image: url('../style/img/produto.png'); /* URL do primeiro ícone */
        }

        .botoes li a:hover {
            background-color: #af26ff;
        }

        .bg-body-tertiary {
            --bs-bg-opacity: 1;
            background-color: rgb(255 255 255 / 0%) !important;
        }
        body{
            background-color: #d4ffea;
        }

    </style>
</head>
<body>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
             <!-- Voltar ao dashboard -->
            <a class="btn btn-info" href="./clientes.php">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class = "container">
        <!-- Informações do Perfil -->
        <?php
            $row = mysqli_fetch_assoc($result);

            echo "<table class='table'>
                    <tr>
                        <thcolspan='2'><h2>".$row['nome']."</h2></th>
                        <br>    
                    </tr>
                    <tr>
                        <td>CPF:</td>
                        <td>";
                        if($row['cpf'] != null){
                            echo $row['cpf'];
                         }else{
                             echo "Não Informado";
                         }
            echo        "</td>
                    </tr>
                    <tr>
                        <td>Endereço:</td>
                        <td>";
                        if($row['endereco'] != null){
                            echo $row['endereco'];
                        }else{
                            echo "Não Informado</h2>";
                        }
                        
            echo        "</td>
                    </tr>
                     <tr>
                        <td>Telefone:</td>
                        <td>".$row['telefone']."</td>
                    </tr>
                </table>
                ";
            
            if(isset($total)){
                echo "<b>Valor Total em Aberto: R$ " . number_format($total,2,",",".") . "</b>";
            }

            echo "<br><br><h2>Pedidos:</h2>";
            echo "<ol class='botoes'>
                    <li><a href='../CAD/cad_pedido.php?id=".$_GET['id']."'>Adicionar Pedido</a></li>
                    </ol>";
            echo "<br><br>";

             

            //lista de pedidos
            $sql = "SELECT *, DATE_FORMAT(data_pedido, '%d/%m/%Y') AS data_formatada FROM pedidos WHERE id_cliente = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_GET['id']);
            
            $stmt->execute();
            $result = $stmt->get_result();
        
            $rows = mysqli_num_rows($result);
            ?>
        <table class="table">
            <tr><td colspan="3">Pedidos</td></tr>
            <tr>
                <td>Id Pedido</td>
                <td>Data do Pedido</td>
                <td>Status</td>
            </tr>

        

            <?php
            while($row = mysqli_fetch_assoc($result)){
                if($row['status'] == 1){
                    $status = "Pedido Pago";
                }else{
                    $status = "Valor em Aberto";
                }

                echo "<tr onclick=\"window.location.href='./pedidos.php?id_p=".$row['id']."';\" style='cursor:pointer;'>
                            <td>".$row['id']."</td>
                            <td>".$row['data_formatada']."</td>
                            <td>".$status."</td>
                        </tr>";
                //echo "<a href='./pedidos.php?id_p=".$row['id']."'> Pedido N° " . $row['id'] . " | " . $row['data_formatada'] . " | " . $status . "</a>";
                //echo "<br>";
            }

        ?>
        </table>    
    </div>
</body>
</html>