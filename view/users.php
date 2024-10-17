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
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <link rel="stylesheet" href="./style/geral.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <title>Usuarios</title>
</head>
<body style="background-color: #cedbd7;">
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
        <h1>Usuarios</h1>
        <br><br>

        <!-- Lista de Produtos  -->
        <table class="table">
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Editar Perfil</th>
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
                            <td><a href='./users.php?edit=".$row['id']."'>Editar</a></td>";
                    if($rows>1){
                        echo "<td><a href='./users.php?del=".$row['id']."'>Deletar</a></td>";
                    }else{
                        echo "<td>❌</td>";
                    }
                    echo "</tr>";
                }
                echo "  <tr>
                            <td colspan='9'><a href='./users.php?novo=0'> Adicionar Novo usuario</a> </td>
                        </tr>";

            ?>
        </table>
        <br>
        
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
                        <button class='btn btn-danger' type='submit'>Deletar Conta</button>
                        <a class='btn btn-warning' href='./users.php'>Cancelar Delete</a>
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
                    <br>
                    <form action='./users.php?edit=".$row1['id']."' method='post'>
                        <label for='nome' class='form-label'>Nome:</label>
                        <input id='nome' class='form-control' type='text' name='nome' value='".$row1['nome']."' maxlength='40' required><br>
                        <label for='email' class='form-label'>Email:</label>
                        <input id='email' class='form-control' type='email' name='email' value='".$row1['email']."' maxlength='200' required><br>
                        <label for='senha1' class='form-label'>Senha:</label>
                        <input id='senha1' class='form-control' type='password' name='senha1' placeholder='Senha' maxlength='40' required><br>
                        <label for='senha2' class='form-label'>Repitir a senha:</label>
                        <input id='senha2' class='form-control' type='password' name='senha2' placeholder='Repedir a senha' maxlength='40' required><br><br>
                        <button class='btn btn-success' type='submit'>Salvar Edição</button>
                        <a class='btn btn-warning' href='./users.php'>Cancelar Edição</a>
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
                        <label for='nome' class='form-label'>Nome:</label>
                        <input id='nome' class='form-control' type='text' name='nome' placeholder='Nome' maxlength='40' required><br>
                        <label for='email' class='form-label'>Email:</label>
                        <input id='email' class='form-control' type='email' name='email' placeholder='Email' maxlength='200' required><br>
                        <label for='senha1' class='form-label'>Senha:</label>
                        <input id='senha1' class='form-control' type='password' name='senha1' placeholder='Senha' maxlength='40' required><br>
                        <label for='senha2' class='form-label'>Repitir a senha:</label>
                        <input id='senha2' class='form-control' type='password' name='senha2' placeholder='Repedir a senha' maxlength='40' required><br><br>
                        <button class='btn btn-success' type='submit'>Cadastrar</button>
                        <a class='btn btn-warning' href='./users.php'>Cancelar Adição</a>
                    </form>
            ";
            
        }

        if(isset($_SESSION['log'])){
            echo "<br><b>" . $_SESSION['log'] . "</b><br>";
            unset($_SESSION['log']);
        }

        ?>
    </div>
</body>
</html>