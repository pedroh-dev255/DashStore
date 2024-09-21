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
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao dashboard -->
    <a href="../">Voltar</a>
    
    <h1>Produtos</h1>
    <a href="../CAD/cad_produtos.php">
        Cadastrar Produtos
    </a>
    <a href="../CAD/cad_estoque.php">
        Cadastrar Estoque
    </a>

    <!-- Barra de pesquisa -->
     <form action="./" method="GET">
        <input type="text" name="busca">
        <button type="submit">Pesquisar</button>
     </form>

    <!-- Lista de Produtos  -->
     <table>
        <tr>
            <th>Nome</th>
            <th>Status</th>
            <th>Preço Produto</th>
            <th>Frete</th>
            <th>Preço de compra</th>
            <th>Quantidade em Estoque</th>
            <th>Valor sujestivo para venda</th>
            <th colapse="5">Data Compra</th>
        </tr>
        <?php
            require("../db.php");
            if (isset($_GET['busca']) && $_GET['busca'] != "") {
                $sql = "SELECT produtos.nome, estoque.status, estoque.vlr_compra, estoque.vlr_efetivo, estoque.vlr_venda, estoque.dt_compra, COUNT(*) as qtd
                        FROM estoque
                        INNER JOIN produtos ON produtos.id = estoque.id_prod
                        WHERE estoque.status = 0 AND produtos.nome LIKE ?
                        GROUP BY produtos.nome, estoque.status, estoque.vlr_compra, estoque.vlr_efetivo, estoque.vlr_venda, estoque.dt_compra
                        ORDER BY estoque.dt_compra ASC";
                $stmt = $conn->prepare($sql);
                $b = "%" . $_GET['busca'] . "%";
                $stmt->bind_param('s', $b);
            } else if (isset($_GET['busca']) && $_GET['busca'] == "") {
                $sql = "SELECT produtos.nome, estoque.status, estoque.vlr_compra, estoque.vlr_efetivo, estoque.vlr_venda, estoque.dt_compra, COUNT(*) as qtd
                        FROM estoque
                        INNER JOIN produtos ON produtos.id = estoque.id_prod
                        WHERE estoque.status = 0
                        GROUP BY produtos.nome, estoque.status, estoque.vlr_compra, estoque.vlr_efetivo, estoque.vlr_venda, estoque.dt_compra
                        ORDER BY estoque.dt_compra ASC";
                $stmt = $conn->prepare($sql);
            } else {
                $sql = "SELECT produtos.nome, estoque.status, estoque.vlr_compra, estoque.vlr_efetivo, estoque.vlr_venda, estoque.dt_compra, COUNT(*) as qtd
                        FROM estoque
                        INNER JOIN produtos ON produtos.id = estoque.id_prod
                        WHERE estoque.status = 0
                        GROUP BY produtos.nome, estoque.status, estoque.vlr_compra, estoque.vlr_efetivo, estoque.vlr_venda, estoque.dt_compra
                        ORDER BY estoque.dt_compra ASC LIMIT 10";
                $stmt = $conn->prepare($sql);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = mysqli_num_rows($result);
            $vlr_estoque=0;
            $vlr_vendido=0;
            while($row = mysqli_fetch_assoc($result)){
                if($row['status'] == 1){
                    $status = "Vendido";
                }else if($row['status'] == 0){
                    $status = "Em estoque";
                }else {
                    $status = "erro";
                }

                $data = DateTime::createFromFormat('Y-m-d', $row['dt_compra'])->format('d/m/Y');
                //Por algum motivo n consegui fazer o calculo do jeito mais simples, essa gambiarra deve funcionar :)
                $c = $row['vlr_efetivo'];
                $e = $row['vlr_compra'];
                $frete = 0;
                $frete = $c - $e;
                echo "<tr>
                        <td>".$row['nome']."</td>
                        <td>".$status."</td>
                        <td> R$".number_format($row['vlr_compra'],2,",",".")."</td>
                        <td> R$".number_format($frete,2,",",".") ."</td>
                        <td> R$".number_format($row['vlr_efetivo'],2,",",".")."</td>
                        <td>".$row['qtd']."</td>
                        
                        <td> R$".number_format($row['vlr_venda'],2,",",".")."</td>
                        
                        <td>".$data."</td>
                    </tr>";
            }
            echo "  <tr>
                        <td colspan='9'> Total de Resultados: ". $rows ."</td>
                    </tr>";

        ?>

     </table>
</body>
</html>