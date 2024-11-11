<?php
    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();

    //Se logado, direciona para o dashboard
    if(isset($_SESSION['login'])){
        header("Location: ./");
        exit();
    }

    function logAttempt($email) {
        $logFile = 'login_attempts.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] Email: $email\n";
    
        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
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

        logAttempt($_POST['login']);

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
    <link rel="stylesheet" href="./style/geral.css">
    <link rel="stylesheet" href="./style/login.css">
    <title>Login</title>
</head>
<body>

        <div class="page">
            
            <form action="./login.php" method="post" class="formLogin">
                <h1>LOGIN</h1>
                <label for="login">Email</label>
                <input type="email" name="login" required>
                <label for="pass">Senha</label>
                <input type="password" name="pass" required>
                <button type="submit" class="btn">Entrar</button>
                <?php
                    if(isset($_SESSION['log'])){
                        echo $_SESSION['log'];
                        unset($_SESSION['log']);
                    }
                ?>
            </form>
        </div>
    
</body>
</html>
