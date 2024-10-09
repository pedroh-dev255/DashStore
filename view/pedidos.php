<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
        exit();
    }

    if(!isset($_GET['id_p']) || !is_numeric($_GET['id_p'])){
        header("Location: ./clientes.php");
        exit();
    }

    //carregar informações do pedido
    require("../db.php");
    $sql="SELECT * FROM pedidos INNER JOIN clientes ON pedidos.id_cliente = clientes.id WHERE pedidos.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $_GET['id_p']);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao perfil -->
    <a href="./perfil.php?id=<?php echo $row['id'];?>">Voltar</a>


    <h1>Pedido Numero: <?php echo $_GET['id_p'];?></h1>
    <h2>Cliente: <?php echo $row['nome'];?></h1>
    <h3>Status: <?php if($row['status'] == 0){ echo "Pagamento Pendente"; }else { echo "Pago";}?></h3>
    <br>

    <?php
        $sql2="SELECT * FROM pedido_produtos INNER JOIN estoque JOIN produtos ON pedido_produtos.id_produto = estoque.id AND estoque.id_prod = produtos.id WHERE pedido_produtos.id_pedido = ?;";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param('s', $_GET['id_p']);
        $stmt2->execute();

        $result2 = $stmt2->get_result();
        $rows = mysqli_num_rows($result2);
        

    
    ?>
    <table>
        <tr>
            <th colspan="4">Lista de Produtos</th>
        </tr>
        <tr>
            <td>Produto</td>
            <!-- <td>Quantidade</td> -->
            <td>Valor</td>
            <td>Devolução</td>
        </tr>
        
            <?php
            $total=0;
                while($row = mysqli_fetch_assoc($result2)){
                    $total += $row['preco'];
                    echo "<tr>";
                    echo "<td>".$row['nome']."</td>";
                    //echo "<td>".$row['quantidade']."</td>";
                    echo "<td> R$ ".number_format($row['preco'],2,",",".")."</td>";
                    echo "<td>Devolver?</td>";
                    echo "</tr>";
                }
            ?>
            
        
        <tr>
            <td colspan="4">Valor Total: <?php echo $total?></td>
        </tr>
    </table>
</body>
</html>