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
    <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
-->
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <link rel="stylesheet" href="../style/produtos.css">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
             <!-- Voltar ao dashboard -->
            <a class="btn btn-info" href="../">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>
    <div class="container">
        <h1>Produtos</h1>
        <ol class="botoes">
            <li>
                <a href="../CAD/cad_produtos.php">
                    Cadastrar Produtos
                </a>
            </li>
            <li>
                <a href="../CAD/cad_estoque.php">
                    Cadastrar Estoque
                </a>
            </li>
        </ol>
        
        <br><br>

        <!-- Barra de pesquisa -->
        <form action="./" method="GET">
            <input type="search" id="search" name="busca">
            <button class="btn btn-primary" type="submit">Pesquisar</button>
        </form>

        <div class="wrapper">
            <div class="table">
                
                <div class="row header green">
                <div class="cell">
                    Nome
                </div>
                <div class="cell">
                    Status
                </div>
                <div class="cell">
                    Preço Produto
                </div>
                <div class="cell">
                    Frete
                </div>
                <div class="cell">
                    Preço de compra
                </div>
                <div class="cell">
                    Quantidade em Estoque
                </div>
                <div class="cell">
                    Valor sujestivo para venda
                </div>
                <div class="cell">
                    Data Compra
                </div>
            </div>
                
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
                    /*echo "<tr>
                            <td>".$row['nome']."</td>
                            <td>".$status."</td>
                            <td> R$".number_format($row['vlr_compra'],2,",",".")."</td>
                            <td> R$".number_format($frete,2,",",".") ."</td>
                            <td> R$".number_format($row['vlr_efetivo'],2,",",".")."</td>
                            <td>".$row['qtd']."</td>
                            
                            <td> R$".number_format($row['vlr_venda'],2,",",".")."</td>
                            
                            <td>".$data."</td>
                        </tr>";*/

                    echo '<div class="row ">
                            <div class="cell" data-title="Nome">
                                '.$row['nome'].'
                            </div>
                            <div class="cell" data-title="Status">
                                '.$status.'
                            </div>
                            <div class="cell" data-title="Preço Produto">
                                R$ '.number_format($row['vlr_compra'],2,",",".").'
                            </div>
                            <div class="cell" data-title="Frete">
                                R$ '.number_format($frete,2,",",".") .'
                            </div>
                            <div class="cell" data-title="Preço de Compra">
                                R$ '.number_format($row['vlr_efetivo'],2,",",".").'
                            </div>
                            <div class="cell" data-title="Quantidade em Estoque">
                                '.$row['qtd'].'
                            </div>     
                            <div class="cell" data-title="Valor de venda">
                                R$ '.number_format($row['vlr_venda'],2,",",".").'
                            </div>
                            <div class="cell" data-title="Valor de venda">
                                '.$data.'
                            </div>
                                
                        </div>';
                }
                

            ?>
        </div> 
    </div>
    
</body>
</html>