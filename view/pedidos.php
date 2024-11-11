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
    $sql="SELECT *,pedidos.status AS status_pedido FROM pedidos INNER JOIN clientes ON pedidos.id_cliente = clientes.id WHERE pedidos.id = ?";
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
    <link rel="stylesheet" href="./style/geral.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido</title>
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "ovixemoovg");
    </script>
</head>
<body>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
            <!-- Voltar ao perfil -->
            <a class="btn btn-info" href="./perfil.php?id=<?php echo $row['id'];?>">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class="container">
        <h2>Pedido Nº <?php echo $_GET['id_p'];?></h2>
        <h4>Cliente: <?php echo $row['nome'];?></h4>
        <h4>Status: <?php if($row['status_pedido'] == 0){ echo "Pagamento Pendente <br><br><a href='../CAD/cad_pag.php?id=".$row['id_cliente']."'>Adicionar Pagamento</a>"; }else { echo "Pago";}?></h4>
        <br>

        <?php
            $sql2="SELECT * FROM pedido_produtos INNER JOIN estoque JOIN produtos ON pedido_produtos.id_produto = estoque.id AND estoque.id_prod = produtos.id WHERE pedido_produtos.id_pedido = ?;";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param('s', $_GET['id_p']);
            $stmt2->execute();

            $result2 = $stmt2->get_result();
            $rows = mysqli_num_rows($result2);
            


        ?>
        <table class="minha-tabela">
            <tr>
                <th colspan="4">Lista de Produtos</th>
            </tr>
            <tr>
                <td><b>Item</b></td>
                <!-- <td>Quantidade</td> -->
                <td><b>Valor</b></td>
            </tr>
            
                <?php
                $total=0;
                    while($row = mysqli_fetch_assoc($result2)){
                        $total += $row['preco'];
                        echo "<tr>";
                        echo "<td>".$row['nome']."</td>";
                        //echo "<td>".$row['quantidade']."</td>";
                        echo "<td> R$ ".number_format($row['preco'],2,",",".")."</td>";
                        echo "</tr>";
                    }
                ?>
                
            
            <tr>
                <td colspan="4">Valor Total: R$<?php echo number_format($total,2,",",".");?></td>
            </tr>
        </table>
    </div>
</body>
</html>