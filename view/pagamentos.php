<?php
    date_default_timezone_set('America/Araguaina');
    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        $_SESSION['log'] = "Realize o login para ter acesso ao sistema!";
        $_SESSION['log1'] = "warning";
        header("Location: ../login.php");
        exit();
    }

    if(!isset($_GET['id'])  || !is_numeric($_GET['id'])){
        $_SESSION['log'] = "Erro de redirecionamento!";
        $_SESSION['log1'] = "error";
        header("Location: ./clientes.php");
        exit();
    }

    require("../db.php");
    checkConnection($conn, '..');
    if (isset($_GET['id'])) {
        $sql = "SELECT * FROM clientes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $_GET['id']);
    } else {
        $_SESSION['log'] = "Erro de redirecionamento!";
        $_SESSION['log1'] = "error";
        header("Location: clientes.php");
        exit();
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = mysqli_num_rows($result);
    $row = mysqli_fetch_assoc($result);

    //caso o resultado seja diferente de 1 ele retorna para a view dos clientes
    if($rows != 1){
        header("Location: clientes.php");
        $_SESSION['log'] = "Cliente não encontrado ou Duplicado!";
        $_SESSION['log1'] = "error";
        exit();
    }

    $ss = "SELECT *, DATE_FORMAT(data_pagamento, '%d/%m/%Y') AS data_formatada FROM pagamentos WHERE id_cliente = ?";
    $stmtss = $conn->prepare($ss);
    $stmtss->bind_param('i', $_GET['id']);
    $stmtss->execute();
    $resultss = $stmtss->get_result();
    $rowss = mysqli_num_rows($resultss);

    
        

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente</title>
    <link rel="stylesheet" href="../style/popup.css">
    <script src="../js/all.js"></script>
    <script src="../js/clarity.js"></script>
    <style>
        .botoes {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
        }
        .botoes li {
            margin-right: 10px;
        }

        .botoes li a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e7d8e9;
            color: rgb(46, 46, 46);
            text-decoration: none;
            border-radius: 20px;
            position: relative;
            transition: background-color 0.3s ease;
            background-repeat: no-repeat;
            background-position: 10px center; /* Ajusta a posição do ícone */
            background-size: 20px; /* Ajusta o tamanho do ícone */
            padding-left: 40px; /* Espaço para o ícone */
        }


        .botoes li:nth-child(1) a {
            background-image: url('../style/img/pagamento.png'); /* URL do primeiro ícone */
            background-color: #69DB65;
        }

        .botoes li:nth-child(1) a:hover {
            background-color: #9ADB65;
        }

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
    <!-- POPUP -->
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
             <!-- Voltar ao dashboard -->
            <a class="btn btn-info" href="./clientes.php">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class = "container">
        <h2>Pagamentos de <?php echo $row['nome'];?></h2>
        <h5>Id do Cliente: <?php echo $row['id'];?></h5>
        <br><br>
        
        <?php
            echo "<ol class='botoes'>
            <li><a href='../CAD/cad_pag.php?id=".$_GET['id']."'>Cadastrar novo Pagamento</a></li>
            </ol>";

        echo "<br><br><h2>Pagamentos:</h2>";
        ?>

        <table class="table">
            <tr>
                <td>Id pagamento</td>
                <td>Data do Pagamento</td>
                <td>Forma de Pagamento</td>
                <td>Valor Pago</td>
            </tr>

        

            <?php
            if($rowss>0){
                while($rowsss = mysqli_fetch_assoc($resultss)){
                   echo "<tr>
                            <td>".$rowsss['id']."</td>
                            <td>".$rowsss['data_formatada']."</td>
                            <td>".$rowsss['forma_pagamento']."</td>
                            <td>R$ ".number_format($rowsss['valor_pago'], 2, ',', '.')."</td>
                        </tr>";
                }
            }else {
                
                $_SESSION['log'] = $row['nome']." ainda não realizou nenhum pagamento";
                $_SESSION['log1'] = "warning";
                header("Location: perfil.php?id=".$_GET['id']);
                exit();
            }
        ?>
        </table> 
        
    </div>
    <?php
        if(isset($_SESSION['log'])){
            echo "<script >showPopin('".$_SESSION['log']."', '".$_SESSION['log1']."');</script>";
            unset($_SESSION['log'], $_SESSION['log1']);
        }
    ?>
</body>
</html>