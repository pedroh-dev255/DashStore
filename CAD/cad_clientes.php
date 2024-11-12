<?php
    date_default_timezone_set('America/Araguaina');
    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }
    //Verifica se o usuario tem permissão
    if($_SESSION['nivel'] !== 3 && $_SESSION['nivel'] !== 2){
        $_SESSION['log'] = "Usuario sem permissão para essa area!";
        $_SESSION['log1'] = "warning";
        header("Location: ../view/clientes.php");
        exit();
    }


    if (isset($_POST['nome'])) {
        require("../db.php");
        checkConnection($conn, '..');
    
        // Verifique a conexão
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }
        //caso o usuario tenha adicionado cpf e endereco
        if ($_POST['cpf'] != "" && $_POST['endereco'] != "") {
            $sql = "INSERT INTO clientes(nome, cpf, status, endereco, telefone) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('ssiss', $_POST['nome'], $_POST['cpf'], $status, $_POST['endereco'], $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
                $_SESSION['log1'] = "success"; // success , warning, error
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
                $_SESSION['log1'] = "error"; // success , warning, error
            }
        //caso o usuario tenha adicionado somente cpf
        } else if($_POST['cpf'] != "" && $_POST['endereco'] == "") {
            $sql = "INSERT INTO clientes(nome, cpf, status, telefone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('ssis', $_POST['nome'], $_POST['cpf'], $status, $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
                $_SESSION['log1'] = "success"; // success , warning, error
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
                $_SESSION['log1'] = "error"; // success , warning, error
            }
        //caso o usuario tenha adicionado somente endereco
        } else if($_POST['cpf'] == "" && $_POST['endereco'] != "") {
            $sql = "INSERT INTO clientes(nome, endereco, status, telefone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('ssis', $_POST['nome'], $_POST['endereco'], $status, $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
                $_SESSION['log1'] = "success"; // success , warning, error
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
                $_SESSION['log1'] = "error"; // success , warning, error
            }
        //caso não tenha inserido nenhum deles
        } else{
            $sql = "INSERT INTO clientes(nome, status, telefone) VALUES ( ?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            // Verifique se a consulta foi preparada corretamente
            if ($stmt === false) {
                die('Erro ao preparar a consulta: ' . $conn->error);
            }
    
            $status = 0;
    
            // Bind dos parâmetros
            $stmt->bind_param('sis', $_POST['nome'], $status, $_POST['telefone']);
    
            // Execute a query
            if ($stmt->execute()) {
                $_SESSION['log'] = "Cliente inserido com sucesso!";
                $_SESSION['log1'] = "success"; // success , warning, error
            } else {
                $_SESSION['log'] = "Erro ao executar: " . $stmt->error;
                $_SESSION['log1'] = "error"; // success , warning, error
            }
        }

        header("Location: ./cad_clientes.php");
        exit();
    }
    


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../style/popup.css">
    <script src="../js/all.js"></script>
    <script src="../js/clarity.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Cliente</title>
    <style>
        .bg-body-tertiary {
            --bs-bg-opacity: 1;
            background-color: rgb(255 255 255 / 0%) !important;
        }
        body{
            background-color: #ffd4e5;
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
            <a class="btn btn-info" href="../view/clientes.php">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>
    <div class="container">
        <h2>Cadastrar Cliente</h2>
        <br><br>

        <form action="./cad_clientes.php" method="POST">
            Nome:*
            <input type="text" class="form-control" name="nome" maxlength="200" placeholder='nome' required><br><br>
            CPF:
            <input type="text" class="form-control" id='cpf' name="cpf" minlength="14" maxlength="14" placeholder='CPF'><br><br>
            Endereço:
            <input type="text" class="form-control" name="endereco" maxlength="400" placeholder='Endereço'><br><br>
            Telefone:*
            <input type="text" class="form-control" id='tel' name="telefone" minlength="15" maxlength="15" placeholder='Telefone' required><br><br>

            <button class="btn btn-success" type="submit">Cadastrar</button>
        </form>
        <?php
            if(isset($_SESSION['log'])){
                echo "<script >showPopin('".$_SESSION['log']."', '".$_SESSION['log1']."');</script>";
                unset($_SESSION['log'], $_SESSION['log1']);
            }
        ?>
    </div>
    <!-- Mascara para CPF e telefone -->
    <script>
        function capitalizeWords(nome) {
            return nome.replace(/\b\w/g, function(letra) {
                return letra.toUpperCase();
            });
        }


        // Função para aplicar máscara no CPF
        function mascaraCPF(cpf) {
            cpf = cpf.replace(/\D/g, ""); // Remove tudo o que não é dígito
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2"); // Coloca ponto após os 3 primeiros dígitos
            cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2"); // Coloca ponto após os 6 primeiros dígitos
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2"); // Coloca hífen entre o terceiro bloco e os dois últimos dígitos
            return cpf;
        }

        // Função para aplicar máscara no telefone
        function mascaraTelefone(telefone) {
            telefone = telefone.replace(/\D/g, ""); // Remove tudo o que não é dígito
            telefone = telefone.replace(/(\d{2})(\d)/, "($1) $2"); // Coloca parênteses em torno dos 2 primeiros dígitos
            telefone = telefone.replace(/(\d{5})(\d)/, "$1-$2"); // Coloca hífen após os 5 primeiros dígitos
            return telefone;
        }

        // Função para adicionar as máscaras automaticamente nos campos
        window.onload = function() {
            // Campo CPF
            var cpfInput = document.getElementById('cpf');
            cpfInput.addEventListener('input', function() {
                this.value = mascaraCPF(this.value);
            });

            // Campo Telefone
            var telefoneInput = document.getElementById('tel');
            telefoneInput.addEventListener('input', function() {
                this.value = mascaraTelefone(this.value);
            });

            // Campo Nome
            var nomeInput = document.getElementsByName('nome')[0];
            nomeInput.addEventListener('input', function() {
                this.value = capitalizeWords(this.value);
            });
        };
    </script>
</body>
</html>