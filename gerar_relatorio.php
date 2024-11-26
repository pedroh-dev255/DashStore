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

    // Função para corrigir caracteres especiais
    function corrigirCaracteres($texto)
    {
        $replace = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
        ];
        return strtr($texto, $replace);
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
            $this->Cell(0, 10, 'Período dos dados: ' . corrigirCaracteres($this->startDate->format('d/m/Y')) . ' a ' . corrigirCaracteres($this->endDate->format('d/m/Y')), 0, 1, 'R');
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
                $this->Cell(40, 10, corrigirCaracteres($col), 1);
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
                        $this->Cell(40, 10, corrigirCaracteres($col), 1);
                    }
                    $this->Ln();
                }

                foreach ($row as $col) {
                    $this->Cell(40, 10, corrigirCaracteres($col), 1);
                }
                $this->Ln();
            }
        }
    }

    $pdf = new PDF($startDateObj, $endDateObj);
    $pdf->AddPage();

    // Seções com aplicação da função corrigirCaracteres
    // ...

    // Gerar o PDF
    $pdf->Output();
}
?>
