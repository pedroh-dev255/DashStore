<?php
    session_start();
    
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }

    if(!isset($_GET['id'])  || !is_numeric($_GET['id'])){
        header("Location: ../view/clientes.php");
    }

    require("../db.php");

    if($_SERVER["REQUEST_METHOD"] == "POST") {

        if ($_POST['pedidos'] == "") {
            $_SESSION['log'] = "Nenhum Pedido Selecionado!";
            header("Location: ./cad_pag.php?id=".$_GET['id']);
            exit;
        }

        $ids_pedidos = $_POST['pedidos'];
        $forma_pagamento = $_POST['forma_pagamento'];
        $valor_pago = floatval($_POST['valor_pago']);
        
        // Recupera valores dos pedidos selecionados
        $total_restante = 0;
        $pedidos = [];
        foreach ($ids_pedidos as $id_pedido) {

            // Busca o valor restante
            $sql_total = "SELECT SUM(preco) AS total_preco FROM pedido_produtos WHERE id_pedido = ".$id_pedido.";";
            $res_total = $conn->query($sql_total);
            $totais = $res_total->fetch_assoc();

            $sql2 = "SELECT SUM(valor_pago) AS total_pago FROM pagamentos WHERE id_pedido = ".$id_pedido.";";
            $res = $conn->query($sql2);
            $totais2 = $res->fetch_assoc();

            $restante = $totais['total_preco'] - $totais2['total_pago'];
            
            /*
            $sql_pedido = "SELECT SUM(pp.preco) - COALESCE(SUM(pg.valor_pago), 0) AS restante 
                           FROM pedido_produtos pp 
                           LEFT JOIN pagamentos pg ON pg.id_pedido = pp.id_pedido 
                           WHERE pp.id_pedido = $id_pedido";
            $result = $conn->query($sql_pedido);
            $pedido = $result->fetch_assoc();
            $restante = $pedido['restante'];
            */
            $pedidos[] = ['id' => $id_pedido, 'restante' => $restante];
            $total_restante += $restante;
        }
        
        // Verifica se o valor inserido é válido
        if ($valor_pago <= 0 || $valor_pago > $total_restante) {
            $_SESSION['log'] = "Valor de pagamento inválido!";
            header("Location: ./cad_pag.php?id=".$_GET['id']);
            exit;
        }

        // Processa o pagamento pelos pedidos, começando pelo de menor valor
        usort($pedidos, function($a, $b) {
            return $a['restante'] - $b['restante'];
        });

        foreach ($pedidos as $pedido) {
            if ($valor_pago > 0) {
                $valor_pedido = min($valor_pago, $pedido['restante']);
                $valor_pago -= $valor_pedido;

                $sql_pagamento = "INSERT INTO pagamentos (id_pedido, valor_pago, data_pagamento, forma_pagamento)
                                  VALUES (?, ?, CURDATE(), ?)";
                $stmt = $conn->prepare($sql_pagamento);
                $stmt->bind_param("ids", $pedido['id'], $valor_pedido, $forma_pagamento);
                $stmt->execute();

                // Verifica se o pedido foi completamente quitado
                if ($valor_pedido >= $pedido['restante']) {
                    $sql_atualiza_status = "UPDATE pedidos SET status = 1 WHERE id = ?";
                    $stmt_status = $conn->prepare($sql_atualiza_status);
                    $stmt_status->bind_param("i", $pedido['id']);
                    $stmt_status->execute();
                }
            }
        }
        header("Location: ./cad_pag.php?id=".$_GET['id']);
        exit;
    }

    // Seleção dos pedidos pendentes
    $sql = "SELECT *, DATE_FORMAT(data_pedido, '%d/%m/%Y') AS data_formatada  
            FROM pedidos 
            WHERE id_cliente = ? AND status = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = mysqli_num_rows($result);

    if($rows<1){
        $_SESSION['log'] = "Nenhum pedido pendente encontrado!";
        header("Location: ../view/clientes.php");
        exit;
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
    <title>Cadastrar Pagamento</title>
    <style>
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
            <a class="btn btn-info" href="../view/clientes.php">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class="container">

        <h2>Cadastro de Pagamentos</h2>
        <br><br>
        <form method="post" action="">
            <table class="table">
                <tr>
                    <th>Selecionar</th>
                    <th>Pedido</th>
                    <th>Data do Pedido</th>
                    <th>Valor Total</th>
                    <th>Valor Restante</th>
                </tr>
                <?php
                    $total_restante = 0;
                    while($row = $result->fetch_assoc()) {
                        $pedido_id = $row['id'];

                        // Busca o valor restante
                        $sql_total = "SELECT SUM(preco) AS total_preco FROM pedido_produtos WHERE id_pedido = ".$row['id'].";";
                        $res_total = $conn->query($sql_total);
                        $totais = $res_total->fetch_assoc();

                        $sql2 = "SELECT SUM(valor_pago) AS total_pago FROM pagamentos WHERE id_pedido = ".$row['id'].";";
                        $res = $conn->query($sql2);
                        $totais2 = $res->fetch_assoc();

                        $valor_restante = $totais['total_preco'] - $totais2['total_pago'];

                        echo "<tr>
                                <td><input type='checkbox' name='pedidos[]' value='".$row['id']."'></td>
                                <td>Nº ".$row['id']."</td>
                                <td>".$row['data_formatada']."</td>
                                <td>R$ ".number_format($totais['total_preco'], 2, ',', '.')."</td>
                                <td>R$ ".number_format($valor_restante, 2, ',', '.')."</td>
                            </tr>";
                        $total_restante += $valor_restante;
                    }
                ?>
            </table>
            <h3>Pagamento</h3>
            <br>
            <label for="forma_pagamento">Forma de Pagamento:</label>
            <br>
            <select class="form-select" name="forma_pagamento" id="forma_pagamento" required>
                <option value=""></option>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Pix">Pix</option>
                <option value="Cartão Crédito">Cartão Crédito</option>
                <option value="Cartão Débito">Cartão Débito</option>
            </select>
            <br>
            <label for="valor_pago">Valor a ser pago:</label>
            <br>
            <input class="form-control" type="number" name="valor_pago" id="valor_pago" step="0.01" min="0" required>
            <br><br>
            <input class="btn btn-success" type="submit" value="Cadastrar Pagamento">
        </form>
        <?php
            if(isset($_SESSION['log'])){
                echo "<b>" . $_SESSION['log'] . "</b><br><br>";
                unset($_SESSION['log']);
            }
                
        ?>
    </div>
</body>
</html>
