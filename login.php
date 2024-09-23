<?php
    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();

    //Se logado, direciona para o dashboard
    if(isset($_SESSION['login'])){
        header("Location: ./");
        exit();
    }

    if(isset($_POST['login']) && isset($_POST['pass'])){
        // Carrega conexão com banco de dados
        require("./db.php");

        // Prepara a consulta SQL para evitar SQL Injection
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $_POST['login']);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verifica se o usuário foi encontrado
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verifica a senha usando password_verify (senha é hash no banco de dados)
            if (password_verify($_POST['pass'], $user['senha'])) {
                // Salva os dados do usuário na sessão
                $_SESSION['login'] = $user['id'];
                
                // Redireciona para o dashboard
                header("Location: ./");
                exit();
            } else {
                $_SESSION['log'] = "Senha incorreta";
            }
        } else {
            $_SESSION['log'] = "Usuário não encontrado";
        }
        
    }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="./style/favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>LOGIN</h1>
    <form action="./login.php" method="post">
        <label for="login">Email</label><br>
        <input type="email" name="login" required><br>
        <label for="pass">Senha</label><br>
        <input type="password" name="pass" required><br><br>
        <button type="submit">Entrar</button>
        <?php
            if(isset($_SESSION['log'])){
                echo $_SESSION['log'];
                unset($_SESSION['log']);
            }
        ?>
    </form>
</body>
</html>
