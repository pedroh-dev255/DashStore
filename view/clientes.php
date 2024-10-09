<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao dashboard -->
    <a href="../">Voltar</a>
    
    <h1>Clientes</h1>

    <a href="../CAD/cad_clientes.php">Adicionar Clientes</a>

      <!-- Barra de pesquisa -->
      <form action="./clientes.php" method="GET">
        <input type="text" name="busca">
        <button type="submit">Pesquisar</button>
     </form>

    <!-- Lista de Produtos  -->
     <table>
        <tr>
            <th>Nome</th>
            <th>Status</th>
            <th>Pedidos em Aberto</th>
            <th>Valor em Aberto</th>
            <th>Adicionar Pagamento</th>
            <th>Novo Pedido</th>
        </tr>
        <?php
            require("../db.php");
            if (isset($_GET['busca'])) {
                $sql = "SELECT * FROM clientes WHERE nome LIKE ? ORDER BY nome ASC";
                $stmt = $conn->prepare($sql);
                $b = "%" . $_GET['busca'] . "%";
                $stmt->bind_param('s', $b);
            } else {
                $sql = "SELECT * FROM clientes ORDER BY nome ASC LIMIT 20";
                $stmt = $conn->prepare($sql);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = mysqli_num_rows($result);

            while($row = mysqli_fetch_assoc($result)){

                $ss = "SELECT * FROM pedidos WHERE id_cliente = ".$row['id']." AND status = 0";
                $stmtss = $conn->prepare($ss);
                $stmtss->execute();
                $resultss = $stmtss->get_result();
                $rowss = mysqli_num_rows($resultss);
                $total=0;
                while($rowsss = mysqli_fetch_assoc($resultss)){
                    $sss = "SELECT SUM(preco) AS total_preco FROM `pedido_produtos` WHERE `id_pedido` = ".$rowsss['id'].";";
                    $stmtsss = $conn->prepare($sss);
                    $stmtsss->execute();
                    $resultsss = $stmtsss->get_result();
                    $rowssss = mysqli_fetch_assoc($resultsss);
                    $total+=$rowssss['total_preco'];
                }


                if($rowss>0){
                    $status = "Valor em aberto";
                }else {
                    $status = "Tudo Pago";
                }

                if(!isset($row['cpf']) || $row['cpf'] == ""){
                    $row['cpf'] = "Não informado";
                }

                if(!isset($row['endereco']) || $row['endereco'] == ""){
                    $row['endereco'] = "Não informado";
                }



                echo "<tr onclick=\"window.location.href='./perfil.php?id=".$row['id']."';\" style='cursor:pointer;'>
                        <td>".$row['nome']."</td>
                        <td>".$status."</td>
                        <td>".$rowss."</td>
                        <td>R$ ".number_format($total,2,",",".")."</td>
                        <td><a href='../CAD/cad_pag.php?id=".$row['id']."'>Adicionar Pagamento</a></td>
                        <td><a href='../CAD/cad_pedido.php?id=".$row['id']."'>Novo Pedido</a></td>
                    </tr>";
            }
            echo "  <tr>
                        <td colspan='9'> Total de Resultados: ". $rows ."</td>
                    </tr>";

        ?>

     </table>

</body>
</html>