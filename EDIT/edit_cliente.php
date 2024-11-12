<?php

    //Inicia as sessoes e verifica se o usuario esta logado
    session_start();
    
    //Se não logado, redireciona para a tela de login
    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }

    //Verifica se o usuario tem permissão de admin
    if($_SESSION['nivel'] !== 3 && $_SESSION['nivel'] !== 2){
        $_SESSION['log'] = "Usuario sem permissão para essa area!";
        $_SESSION['log1'] = "warning";
        header("Location: ../view/perfil.php?id=".$_GET['id']);
        exit();
    }

    if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
        $_SESSION['log'] = "Erro no redirecionamento";
        $_SESSION['log1'] = "error";
        header("Location: ../view/perfil.php?id=".$_GET['id']);
        exit();
    }

    require("../db.php");
    checkConnection($conn, '..');

    $sql = "SELECT * FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_GET['id']);

    $stmt->execute();
    $result = $stmt->get_result();

    $rows = mysqli_num_rows($result);

    //caso o resultado seja diferente de 1 ele retorna para a view dos clientes
    if($rows != 1){
        header("Location: ../view/clientes.php");
        $_SESSION['log'] = "Cliente não encontrado ou Duplicado!";
        $_SESSION['log1'] = "error";
        exit();
    }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Editar Usuarios</title>
    <link rel="stylesheet" href="../style/popup.css">
    <script src="../js/all.js"></script>
</head>
<body style="background-color: #cedbd7;">

    <!-- POPUP -->
    <div class="popin-notification" id="popin">
        <p id="popin-text"></p>
        <button onclick="closePopin()">Fechar</button>
    </div>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
             <!-- Voltar ao dashboard -->
            <a class="btn btn-info" href="../view/perfil.php?id=<?php echo $_GET['id'];?>">Voltar</a>

            <!-- Botão para deslogar -->
            <form class="d-flex ms-auto" action="../" method="get">
                <input type="hidden" name="logoff" value='true'>
                <input type="submit" class="btn btn-danger" value="Deslogar">
            </form>
        </div>
    </nav>

    <div class = "container">
        <h1>Editar Informações do Cliente</h1>
        <?php
            $row = mysqli_fetch_assoc($result);
        ?>
        <br><br>
        <form action="./edit_cliente.php?id=<?php echo $_GET['id'];?>" method="post">
            <label for="nome">Nome:*</label>
            <input type="text" class="form-control" id="nome" name="nome" maxlength="200" placeholder='nome' value="<?php echo $row['nome']; ?>" required><br><br>
            
            <label for="cpf">CPF:</label>
            <input type="text" class="form-control" id='cpf' name="cpf" minlength="14" maxlength="14" value= "<?php if($row['cpf'] != null){ echo $row['cpf']; }?>"placeholder='CPF'><br><br>
            
            <label for="endereco">Endereço:</label>
            <input type="text" class="form-control" id="endereco" name="endereco" maxlength="400" value="<?php if($row['endereco'] != null){ echo $row['endereco']; } ?>" placeholder='Endereço'><br><br>
            
            <label for="tel">Telefone:*</label>
            <input type="text" class="form-control" id='tel' name="telefone" minlength="15" maxlength="15" placeholder='Telefone' value="<?php echo $row['telefone'];?>" required><br><br>
        
            <button class="btn btn-success">Salvar Edição</button>
        </form>
    </div>

    <?php
        $conn->close();
        if(isset($_SESSION['log'])){
            echo "<script >showPopin('".$_SESSION['log']."', '".$_SESSION['log1']."');</script>";
            unset($_SESSION['log'], $_SESSION['log1']);
        }
    ?>

    <!-- Mascara para CPF, NOME e telefone -->
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