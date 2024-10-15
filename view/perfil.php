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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao dashboard -->
    <a href="./clientes.php">Voltar</a>

    <div class = "box">
        <!-- Informações do Perfil -->
        <?php
            $row = mysqli_fetch_assoc($result);

            echo "<table>
                    <tr>
                        <thcolspan='2'><h2>".$row['nome']."</h2></th>
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
                <br>
                <a href='seila?id=".$_GET['id']."'>Editar Perfil do Cliente</a><br><br>";
            
            if(isset($total)){
                echo "Valor Total em Aberto: R$ " . number_format($total,2,",",".");
            }

            echo "<br><br><h2>Pedidos:</h2>";
            echo "<a href='../CAD/cad_pedido.php?id=".$_GET['id']."'>Adicionar Pedido</a>";
            echo "<br><br>";

             

            //lista de pedidos
            $sql = "SELECT *, DATE_FORMAT(data_pedido, '%d/%m/%Y') AS data_formatada FROM pedidos WHERE id_cliente = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_GET['id']);
            
            $stmt->execute();
            $result = $stmt->get_result();
        
            $rows = mysqli_num_rows($result);

            while($row = mysqli_fetch_assoc($result)){
                if($row['status'] == 1){
                    $status = "Pedido Pago";
                }else{
                    $status = "Valor em Aberto";
                }
                echo "<a href='./pedidos.php?id_p=".$row['id']."'> Pedido N° " . $row['id'] . " | " . $row['data_formatada'] . " | " . $status . "</a>";
                echo "<br>";
            }

        ?>
    </div>
</body>
</html>