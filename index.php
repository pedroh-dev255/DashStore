<?php
    date_default_timezone_set('America/Araguaina');

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        $_SESSION['log'] = "Usuario não logado!";
        $_SESSION['log1'] = "error";
        header("Location: ./login.php");
        exit();
    }

    //Verifica se o usuario esta com os niveis de acesso configurado
    if(!isset($_SESSION['nivel']) || $_SESSION['nivel'] == "" || $_SESSION['nivel'] == "0" || $_SESSION['nivel'] == null){
        $_SESSION['log'] = "Usuario sem nivel de acesso definido! Recorra a um administrador do sistema!";
        $_SESSION['log1'] = "error";
        unset($_SESSION['login']);
        header("Location: ./login.php");
        exit();
    }

    if(isset($_GET['logoff']) && $_GET['logoff'] =='true' ){
        session_destroy();
        header("Location: ./login.php");
        exit();
    }
    require("./db.php");
        // Configuração do período
        $startDate = date('Y-m-d', strtotime('-30 days'));
        $endDate = date('Y-m-d');
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $startDate = $_GET['start_date'];
            $endDate = $_GET['end_date'];
        }
    
        // Query de Produtos Mais Vendidos
        $queryTopProducts = "
            SELECT p.nome, COUNT(pp.id_produto) AS quantidade
            FROM pedido_produtos pp
            JOIN estoque e ON pp.id_produto = e.id
            JOIN produtos p ON e.id_prod = p.id
            JOIN pedidos pd ON pp.id_pedido = pd.id
            WHERE pd.data_pedido BETWEEN '$startDate' AND '$endDate'
            GROUP BY p.nome
            ORDER BY quantidade DESC
            LIMIT 5";
        $resultTopProducts = $conn->query($queryTopProducts);
    
        // Query de Clientes com Valores em Aberto
        $queryClientsDebt = "
            SELECT c.nome, SUM(pp.preco) - COALESCE(SUM(pg.valor_pago), 0) AS saldo_aberto
            FROM pedidos pd
            JOIN clientes c ON pd.id_cliente = c.id
            LEFT JOIN pedido_produtos pp ON pd.id = pp.id_pedido
            LEFT JOIN pagamentos pg ON pd.id = pg.id_pedido
            GROUP BY c.nome
            HAVING saldo_aberto > 0";
        $resultClientsDebt = $conn->query($queryClientsDebt);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="./style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./style/geral.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard</title>
    <link rel="stylesheet" href="./style/popup.css">
    <script src="./js/all.js"></script>
    <script src="../js/clarity.js"></script>
</head>
<body>
    <!-- POPUP -->
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>
    <nav class="navbar">
        <div class="container-fluid">
            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="./" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class="container">
        <h1>Dashboard</h1>

        <ol class="botoes">
            <li>
                <a href="./view/">Produtos</a>
            </li>
            <li>
                <a href="./view/clientes.php">Clientes</a>
            </li>
            <li>
                <a href="./view/users.php">Usuarios</a>
            </li>
        </ol>

        <br>
        <div class="box">
            <!-- Filtro de Período -->
            <form class="row--" method="GET" action="">
                <div class="row">
                    <div class="col-md-5">
                        <label for="start_date">Data Inicial:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="end_date">Data Final:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </div>
            </form>


            <br>
                <ol class="botoes2">
                    <li>
                        <a href="./gerar_relatorio.php?data1=<?php echo $startDate;?>&data2=<?php echo $endDate;?>">Gerar Relatorio</a>
                        <!-- target="_blank" -->
                    </li>
                </ol>
            <br>

            <div class="row mt-4">
            <!-- Produtos Mais Vendidos -->
            <div class="col-md-6">
                <h4>Produtos Mais Vendidos</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $resultTopProducts->fetch_assoc()): ?>
                            <tr>
                                <td><?= $product['nome'] ?></td>
                                <td><?= $product['quantidade'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <!-- Clientes com Valores em Aberto -->
            <div class="col-md-6">
                <h4>Clientes com Valores em Aberto</h4>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Saldo em Aberto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($client = $resultClientsDebt->fetch_assoc()): ?>
                            <tr>
                                <td><?= $client['nome'] ?></td>
                                <td>R$ <?= number_format($client['saldo_aberto'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Gráfico de pizza (rosquinha) para formas de pagamento -->
            <div class="row">
                <div class="col-md-6">
                    <h4>Formas de Pagamento</h4>
                    <canvas id="paymentChart"></canvas>
                </div>

                <!-- Gráfico de pizza (rosquinha) para valores recebidos vs. valores a receber -->
                <div class="col-md-6">
                    <h4>Recebidos vs. A Receber</h4>
                    <canvas id="totalChart"></canvas>
                </div>

            </div>

            <br><br>
            <?php
                checkConnection($conn,'.');

                $ss = "SELECT * FROM pedidos WHERE status = 0 && data_pedido BETWEEN '$startDate' AND '$endDate'";
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
                $sql3 = "SELECT SUM(valor_pago) AS total_pago FROM pagamentos WHERE data_pagamento BETWEEN '$startDate' AND '$endDate';";
                $res3 = $conn->query($sql3);
                $totais3 = $res3->fetch_assoc();

                if(isset($total)){
                    //echo "Total a Receber: R$ " . number_format($total,2,",","."). "<br>";
                }
                if(isset($totais3['total_pago']) && is_numeric($totais3['total_pago'])){
                    //echo "Total já recebido: R$ ". number_format($totais3['total_pago'],2,",",".") . "<br>";

                    $sql4 = "SELECT forma_pagamento, SUM(valor_pago) AS total_pago FROM pagamentos GROUP BY forma_pagamento;";
                    $res4 = $conn->query($sql4);
                    //$totais3 = $res3->fetch_assoc();
                    while($row2 = mysqli_fetch_assoc($res4)){
                        //echo "Forma: " . $row2['forma_pagamento'] . " Total: R$ ". number_format($row2['total_pago'],2,",",".") . "<br>";
                    }
                }

                $conn->close();
            ?>
        </div>
    </div>
    <?php
        if(isset($_SESSION['log'])){
            echo "<script >showPopin('".$_SESSION['log']."', '".$_SESSION['log1']."');</script>";
            unset($_SESSION['log'], $_SESSION['log1']);
        }
    ?>

    <script>
        // Gráfico 1: Formas de Pagamento (Rosquinha)
        const paymentData = {
            labels: [
                <?php
                    $formas = [];
                    $totais = [];

                    $res4->data_seek(0); // Reseta o ponteiro do resultado

                    while($row2 = mysqli_fetch_assoc($res4)) {
                        $formas[] = "'" . $row2['forma_pagamento'] . "'";
                        $totais[] = $row2['total_pago'];
                    }

                    if (!empty($formas)) {
                        echo implode(", ", $formas);
                    }
                ?>
            ],
            datasets: [{
                label: 'Formas de Pagamento',
                data: [
                    <?php
                        if (!empty($totais)) {
                            echo implode(", ", $totais);
                        }
                    ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1,
                hoverOffset: 4
            }]
        };

        const paymentChartConfig = {
            type: 'doughnut',
            data: paymentData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            generateLabels: function(chart) {
                                const dataset = chart.data.datasets[0];
                                return chart.data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    return {
                                        text: `${label}: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
                                        fillStyle: dataset.backgroundColor[i],
                                        hidden: false,
                                    };
                                });
                            }
                        }
                    }
                }
            }
        };

        const paymentChart = new Chart(
            document.getElementById('paymentChart'),
            paymentChartConfig
        );

        // Gráfico 2: Total Recebido vs. Total a Receber (Rosquinha)
        const totalData = {
            labels: ['Total Recebido', 'Total a Receber'],
            datasets: [{
                label: 'Total',
                data: [
                    <?php
                        // Valores recebidos e a receber
                        $total_recebido = isset($totais3['total_pago']) ? $totais3['total_pago'] : 0;
                        $total_a_receber = isset($total) ? $total : 0;
                        echo $total_recebido . ', ' . $total_a_receber;
                    ?>
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1,
                hoverOffset: 4
            }]
        };

        const totalChartConfig = {
            type: 'doughnut',
            data: totalData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            generateLabels: function(chart) {
                                const dataset = chart.data.datasets[0];
                                return chart.data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    return {
                                        text: `${label}: R$ ${value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`,
                                        fillStyle: dataset.backgroundColor[i],
                                        hidden: false,
                                    };
                                });
                            }
                        }
                    }
                }
            }
        };

        const totalChart = new Chart(
            document.getElementById('totalChart'),
            totalChartConfig
        );
    </script>

    
</body>
</html>