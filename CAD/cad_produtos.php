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
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <title>Cadastrar Produtos</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao dashboard -->
    <a href="../view/">Voltar</a>
    
    <h1>Cadastrar Produtos</h1>
    <form action="./cad_produtos.php" method="post">
        <h3>cadastrar novo produto</h3>
        <label for="nome">Nome:</label>
        <input type="input" id="nome" name="nome" maxlength="200" required><br><br>
        <button type="submit">Cadastrar</button>
    </form>
    
    
    <table>
        <caption>Produtos já cadastrados</caption>
        <thead>
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
    
</body>
</html>