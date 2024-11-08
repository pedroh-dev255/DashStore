<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }

    //importa as configurações do banco de dados
    include("../db.php");

    //Verifica se o usuario fez um novo cadastro, se sim salva o novo produto no banco
    if(isset($_POST['nome']) && $_POST['nome'] != null && $_POST['nome'] != ""){
        $sql_ = "INSERT INTO produtos(nome) values (?)";
        $stmt = $conn->prepare($sql_);
        $stmt->bind_param('s', $_POST['nome']);
        $stmt->execute();
        $stmt->close();
        header("Location: ./cad_produtos.php");
    }
    

    //carrega os dados ja salvos no banco
    $sql="select * from produtos";
    $result = $conn->query($sql);
    $rows = mysqli_num_rows($result);

    //fecha a conexão com o banco de dados
    $conn->close();
    
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <link rel="stylesheet" href="./style/geral.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <title>Cadastrar Produtos</title>
    <style>
        .bg-body-tertiary {
            --bs-bg-opacity: 1;
            background-color: rgb(255 255 255 / 0%) !important;
        }
        body{
            background-color: #dbdcff;
        }
    </style>
</head>
<body background-color="#f5f5f5">
    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
             <!-- Voltar ao dashboard -->
            <a class="btn btn-info" href="../view/">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>
    
    <div class="container">
        <h1>Cadastrar Produtos</h1>
        <br>

        <form action="./cad_produtos.php" method="post">
            <h3>Cadastrar Novo Produto</h3>
            <br>
            <label for="nome">Nome do Produto:</label>
            <input class="form-control" style="background-color: white;" type="input" id="nome" name="nome" maxlength="200" required><br><br>
            <button class="btn btn-success" type="submit">Cadastrar</button>
        </form>
        
        <br><br>
        <table class="table">
            
            <thead>
                <tr><td colspan="2"><h4>Produtos já cadastrados</h4></td></tr>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while($row = mysqli_fetch_assoc($result)){
                    echo "<tr>
                            <td>".$row['id']."</td>
                            <td>".$row['nome']."</td>
                        </tr>";
                }
                echo "  <tr>
                            <td colspan='2'> Total de Resultados: ". $rows ."</td>
                        </tr>";
                ?>
            </tbody>
        </table>
    </div>
    
    
</body>
</html>