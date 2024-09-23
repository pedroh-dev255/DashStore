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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <title>Usuarios</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>
    <!-- Voltar ao dashboard -->
    <a href="../">Voltar</a>

    <h1>Usuarios</h1>

    <!-- Lista de Produtos  -->
    <table>
        <tr>
            <th>Nome</th>
            <th>email</th>
            <th>Alterar Senha</th>
            <th>Deletar</th>
        </tr>
        <?php
            require("../db.php");
            
            $sql = "SELECT * FROM usuarios ORDER BY nome";
            $stmt = $conn->prepare($sql);
            
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = mysqli_num_rows($result);
            while($row = mysqli_fetch_assoc($result)){
                echo "<tr>
                        <td>".$row['nome']."</td>
                        <td>".$row['email']."</td>
                        <td><a href='./users.php?edit=".$row['id']."'>Editar</a></td>
                        <td><a href='./users.php?del=".$row['id']."'>Deletar</a></td>
                    </tr>";
            }
            echo "  <tr>
                        <td colspan='9'><a href='./users.php?novo=0'> Adicionar Novo usuario</a> </td>
                    </tr>";

        ?>
    </table>
    <br><br>
    
    <?php
    //modo de edição
    if(isset($_GET['del'])){
        $sql1 = "SELECT * FROM usuarios WHERE id = ".$_GET['del']." ORDER BY nome";
        $stmt1 = $conn->prepare($sql1);
        
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = mysqli_fetch_assoc($result1);
        if(isset($_POST['confirmacao']) && $_POST['confirmacao'] == "yes"){
            $dell = "DELETE FROM `usuarios` WHERE `id` = ?";
            $stmt3 = $conn->prepare($dell);
            $stmt3->bind_param('s', $row1['id']);
            $stmt3->execute();

            $_SESSION['log'] = "Usuario deletado!";
            
            header("Location: users.php");
            exit();
        }
        echo  "<h2>Deletar Usuario?</h2>
                <form action='./users.php?del=".$row1['id']."' method='post'>
                    <b>Deletar usuario ".$row1['nome']."</b><br><br>
                    <input type='hidden' name='confirmacao' value='yes'>
                    <button type='submit'>Deletar</button>
                    <a class='button' href='./users.php'>Cancelar Delete</a>
                </form>
        ";
        
    }

    //modo de edição
    if(isset($_GET['edit'])){
        $sql1 = "SELECT * FROM usuarios WHERE id = ".$_GET['edit']." ORDER BY nome";
        $stmt1 = $conn->prepare($sql1);
        
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $row1 = mysqli_fetch_assoc($result1);

        //caso a senha esteja correta. faz o update no banco.
        if(isset($_POST['senha1']) && isset($_POST['senha2']) && $_POST['senha1'] == $_POST['senha2']){
            //Encriptação da senha
            $senhaHash = password_hash($_POST['senha1'], PASSWORD_BCRYPT);

            $update = "UPDATE `usuarios` SET `nome`=?,`email`=?,`senha`=? WHERE id =" . $_GET['edit'];
            $stmt2 = $conn->prepare($update);
            $stmt2->bind_param('sss', $_POST['nome'], $_POST['email'], $senhaHash);
            $stmt2->execute();

            $_SESSION['log'] = "Usuario atualizado";
            
            header("Location: users.php");
            exit();

        }else if(isset($_POST['senha1']) && isset($_POST['senha2']) && $_POST['senha1'] != $_POST['senha2']){
             $_SESSION['log'] = "senhas não Coincidem!";
        }
        
        echo  "<h2>Modo Edição</h2>
                <form action='./users.php?edit=".$row1['id']."' method='post'>
                    <input type='text' name='nome' value='".$row1['nome']."' maxlength='40' required><br><br>
                    <input type='email' name='email' value='".$row1['email']."' maxlength='200' required><br><br>
                    <input type='password' name='senha1' placeholder='senha' maxlength='40' required><br><br>
                    <input type='password' name='senha2' placeholder='Repedir a senha' maxlength='40' required><br><br><br>
                    <button type='submit'>Salvar Edição</button>
                    <a class='button' href='./users.php'>Cancelar Edição</a>
                </form>
        ";
        
    }
    
    //Novo usuario
    if(isset($_GET['novo'])){
        if(isset($_POST['nome']) && isset($_POST['email'])){
            $result00 = $conn->query("select * from usuarios");
            $email="";
            while($row00 = $result00->fetch_assoc()){
                if($row00['email'] == $_POST['email']){
                    $email = "existe";
                }
            }
            if($email != "existe"){
                if(isset($_POST['senha1']) && isset($_POST['senha2']) && $_POST['senha1'] == $_POST['senha2']){
                    //Encriptação da senha
                    $senhaHash = password_hash($_POST['senha1'], PASSWORD_BCRYPT);

                    $update = "INSERT INTO usuarios(nome,email,senha) VALUES (?,?,?)";
                    $stmt2 = $conn->prepare($update);
                    $stmt2->bind_param('sss', $_POST['nome'], $_POST['email'], $senhaHash);
                    $stmt2->execute();

                    $_SESSION['log'] = "Usuario cadastrado";
                    
                    header("Location: users.php");
                    exit();

                }else if(isset($_POST['senha1']) && isset($_POST['senha2']) && $_POST['senha1'] != $_POST['senha2']){
                    $_SESSION['log'] = "senhas não Coincidem!";
                }
            }else {
                $_SESSION['log'] = "Email já cadastrado!";
            }
        }
        
        echo  "<h2>Cadastrar Novo Usuario</h2>
                <form action='./users.php?novo=0' method='post'>
                    <input type='text' name='nome' placeholder='Nome' maxlength='40' required><br><br>
                    <input type='email' name='email' placeholder='email' maxlength='200' required><br><br>
                    <input type='password' name='senha1' placeholder='senha' maxlength='40' required><br><br>
                    <input type='password' name='senha2' placeholder='Repedir a senha' maxlength='40' required><br><br><br>
                    <button type='submit'>Cadastrar</button>
                    <a class='button' href='./users.php'>Cancelar adição</a>
                </form>
        ";
        
    }

    if(isset($_SESSION['log'])){
        echo "<br><b>" . $_SESSION['log'] . "</b><br>";
        unset($_SESSION['log']);
    }

    ?>

</body>
</html>