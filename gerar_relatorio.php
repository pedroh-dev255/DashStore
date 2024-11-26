<?php
session_start();

if (!isset($_SESSION['login'])) {
    $_SESSION['log'] = "Usuário não logado!";
    $_SESSION['log1'] = "error";
    header("Location: ./login.php");
    exit();
}

if (isset($_GET['data1']) && isset($_GET['data2'])) {
    require('fpdf/fpdf.php');
    require('db.php');

    $startDate = $_GET['data1'];
    $endDate = $_GET['data2'];

    // Verificar se as datas são válidas
    $startDateObj = DateTime::createFromFormat('Y-m-d', $startDate);
    $endDateObj = DateTime::createFromFormat('Y-m-d', $endDate);

    if ($startDateObj === false || $endDateObj === false) {
        $_SESSION['log'] = "Datas fornecidas são inválidas!";
        $_SESSION['log1'] = "error";
        header("Location: ./");
        exit();
    }

    // Definir a classe do PDF
    class PDF extends FPDF
    {
        private $startDate;
        private $endDate;

        function __construct($startDate, $endDate)
        {
            parent::__construct();
            $this->startDate = $startDate;
            $this->endDate = $endDate;
        }

        function Header()
        {
            $this->SetFont('Helvetica', 'B', 18);
            $this->Cell(0, 10, 'Relatório de Vendas e Pagamentos DashStore', 0, 1, 'C');
            $this->Ln(10);

            $this->SetFont('Helvetica', '', 10);
            $this->Cell(0, 10, 'Período dos dados: ' . $this->startDate->format('d/m/Y') . ' a ' . $this->endDate->format('d/m/Y'), 0, 1, 'R');
            $this->Ln(10);
        }

        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Helvetica', 'I', 8);
            $this->Cell(0, 10, 'Página ' . $this->PageNo(), 0, 0, 'C');
        }

        function Table($header, $data)
        {
            // Cabeçalho
            $this->SetFont('Helvetica', 'B', 10);
            foreach ($header as $col) {
                $this->Cell(40, 10, $col, 1);
            }
            $this->Ln();

            // Dados
            $this->SetFont('Helvetica', '', 10);
            foreach ($data as $row) {
                // Verifica se há espaço suficiente na página
                if ($this->GetY() + 10 > $this->PageBreakTrigger) {
                    $this->AddPage();
                    // Reimprimir cabeçalho da tabela na nova página
                    $this->SetFont('Helvetica', 'B', 10);
                    foreach ($header as $col) {
                        $this->Cell(40, 10, $col, 1);
                    }
                    $this->Ln();
                }

                foreach ($row as $col) {
                    $this->Cell(40, 10, $col, 1);
                }
                $this->Ln();
            }
        }
    }

    $pdf = new PDF($startDateObj, $endDateObj);
    $pdf->AddPage();

    // Seção 1: Pagamentos por Forma de Pagamento
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Pagamentos por Forma de Pagamento:', 0, 1);
    $queryPayments = "
        SELECT forma_pagamento, SUM(valor_pago) AS total_pago
        FROM pagamentos
        WHERE data_pagamento BETWEEN '$startDate' AND '$endDate'
        GROUP BY forma_pagamento";
    $resultPayments = $conn->query($queryPayments);

    $paymentsData = [];
    while ($payment = $resultPayments->fetch_assoc()) {
        $paymentsData[] = [$payment['forma_pagamento'], "R$ " . number_format($payment['total_pago'], 2, ',', '.')];
    }
    $pdf->Table(['Forma de Pagamento', 'Total'], $paymentsData);
    $pdf->Ln(10);

    // Seção 2: Clientes Pendentes de Pagamento
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Clientes com Pagamentos Pendentes:', 0, 1);
    $queryDebts = "
        SELECT c.nome, SUM(pp.preco) - COALESCE(SUM(pg.valor_pago), 0) AS saldo_aberto
        FROM pedidos pd
        JOIN clientes c ON pd.id_cliente = c.id
        LEFT JOIN pedido_produtos pp ON pd.id = pp.id_pedido
        LEFT JOIN pagamentos pg ON pd.id = pg.id_pedido
        GROUP BY c.nome
        HAVING saldo_aberto > 0";
    $resultDebts = $conn->query($queryDebts);

    $debtsData = [];
    while ($debt = $resultDebts->fetch_assoc()) {
        $debtsData[] = [$debt['nome'], "R$ " . number_format($debt['saldo_aberto'], 2, ',', '.')];
    }
    $pdf->Table(['Cliente', 'Saldo Pendente'], $debtsData);
    $pdf->Ln(10);

    // Seção 3: Pedidos Vendidos
    $pdf->SetFont('Helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Produtos Vendidos:', 0, 1);
    $queryOrders = "
        SELECT p.id, pr.nome AS produto, c.nome AS cliente, pp.preco AS valor 
        FROM pedidos p 
        JOIN clientes c ON p.id_cliente = c.id 
        JOIN pedido_produtos pp ON p.id = pp.id_pedido 
        JOIN produtos pr ON pp.id_produto = pr.id 
        WHERE p.status = 1 && data_pedido BETWEEN '$startDate' AND '$endDate'
        ORDER BY id ASC";
    $resultOrders = $conn->query($queryOrders);

    $ordersData = [];
    while ($order = $resultOrders->fetch_assoc()) {
        $ordersData[] = ["Pedido #{$order['id']}", $order['cliente'], $order['produto'], "R$ " . number_format($order['valor'], 2, ',', '.')];
    }
    $pdf->Table(['ID do Pedido', 'Cliente', 'Produto', 'Valor'], $ordersData);

    // Saída do PDF
    $pdf->Output('D', 'Relatorio de Vendas e Pagamentos ' . (DateTime::createFromFormat('Y-m-d', $startDate))->format('d/m/Y') . ' a ' . (DateTime::createFromFormat('Y-m-d', $endDate))->format('d/m/Y') . ' .pdf');

    $conn->close();
} else {
    $_SESSION['log'] = "Para de querer hacker, é feio!";
    $_SESSION['log1'] = "error";
    header("Location: ./");
    exit();
}
?>
