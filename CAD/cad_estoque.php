<?php
    session_start();

    if(!isset($_SESSION['login'])){
        header("Location: ../login.php");
    }
    include("../db.php");

    $sql="select * from produtos";
    $result = $conn->query($sql);
    $rows = mysqli_num_rows($result);

    if(isset($_POST['nomes'])){
        if($_POST['nomes'] == "outro"){
            //verificar se o novo produto que vai se inserido ja não existe no banco
            $existe = "";
            while($row = mysqli_fetch_assoc($result)){
                if($_POST['nome'] == $row['nome']){
                    $existe="sim";
                }
            }

            if($existe != "sim"){
                //primeiro vamos inserir o novo produto na tabela
                $sql_ = "INSERT INTO produtos(nome) values (?)";
                $stmt = $conn->prepare($sql_);
                $stmt->bind_param('s', $_POST['nome']);
                $stmt->execute();
                $stmt->close();

                //Verificar qual o ID do novo produto
                //Sim eu sei que to fazendo o SQL errado, preguiça KLSDKKSKSKSKKS
                $sql1="select * from produtos WHERE nome = '".$_POST['nome']."' LIMIT 1";
                $result1 = $conn->query($sql1);
                //$rows1 = mysqli_num_rows($result1);
                $row1 = mysqli_fetch_assoc($result1);

                //inserir no estoque os dados cadastrados.
                for($i=0;$i<$_POST['quantidade'];$i++){
                    //echo $row1['id'] . " - " . $_POST['quantidade'] . "<br>";
                    
                    $sql_ins = "INSERT INTO `estoque`(`id_prod`, `vlr_compra`, `status`, `vlr_efetivo`, `vlr_venda`, `dt_compra`) VALUES (?,?,0,?,?,?)";
                    $stmt0 = $conn->prepare($sql_ins);
                    
                    $efetivo = $_POST['compra'] + $_POST['frete'];
                    $stmt0->bind_param('iddds', $row1['id'], $_POST['compra'], $efetivo, $_POST['venda'], $_POST['dt_compra']);

                    $stmt0->execute();
                    $stmt0->close();

                    $_SESSION['log'] = "Produto inserido";
                    header("Location: ./cad_estoque.php");
                    exit();
                }

            }else{
                $_SESSION['log'] = "Produto já existe BAGUAU, LÊ DIREITO";
                header("Location: ./cad_estoque.php");
                exit();
            }
        }else{
            for($i=0;$i<$_POST['quantidade'];$i++){
                //echo $_POST['nomes'] . " - " . $_POST['quantidade'] . "<br>";
                $sql_ins = "INSERT INTO `estoque`(`id_prod`, `vlr_compra`, `status`, `vlr_efetivo`, `vlr_venda`, `dt_compra`) VALUES (?,?,0,?,?,?)";
                $stmt0 = $conn->prepare($sql_ins);

                $efetivo = $_POST['compra'] + $_POST['frete'];
                $stmt0->bind_param('iddds', $_POST['nomes'], $_POST['compra'], $efetivo, $_POST['venda'], $_POST['dt_compra']);

                $stmt0->execute();
                $stmt0->close();
            }
        }
    }


    $conn->close();

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./style/geral.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "ovixemoovg");
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Estoque</title>
    <style>
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

        <h2>Cadastro de Estoque</h2>
        <br><br><br>
        <form action="./cad_estoque.php" method="post">
            <label for="nomes">Nome:</label>
            <select class="form-select" name="nomes" id="nomes" required>
                <option value="" selected>Selecione um Produto</option>
                <?php
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<option value=".$row['id'].">".$row['nome']."</option>";
                    }
                ?>
                <option value="outro">Adicionar Novo</option>
            </select><br>

            <!-- Caso Usuario selecionar Adicionar novo abilita o input para novo produto -->
            <input type="text" class="form-control" name="nome" id="nome" style="display:none;" maxlength="200" placeholder="Digite o novo Produto"><br>
            Valor de compra do produto <br>
            <input type="number" class="form-control" step="0.01" name="compra" maxlength="200" placeholder="Valor de compra do produto" required><br><br>
            Valor do frete <br>
            <input type="number" class="form-control" step="0.01" name="frete" placeholder="Valor do frete se houver" required><br><br>
            Valor sugerido para venda <br>
            <input type="number" class="form-control" step="0.01" name="venda" placeholder="Valor de venda" required><br><br>
            Data da compra <br>
            <input type="date" class="form-control" name="dt_compra" required><br><br>
            Qnt de itens comprados <br>
            <input type="number" class="form-control" name="quantidade" placeholder="Quantidade comprada" required><br><br><br>
            <?php
                if(isset($_SESSION['log'])){
                    echo "<b>" . $_SESSION['log'] . "</b><br><br>";
                    unset($_SESSION['log']);
                }
                
            ?>
            <button class="btn btn-success" type="submit">Salvar</button>
        </form>
    </div>

    <script>
        const select = document.getElementById('nomes');
        const inputNome = document.getElementById('nome');

        select.addEventListener('change', function() {
            if (select.value === 'outro') {
                inputNome.style.display = 'inline';
                inputNome.required = true; // Torna o input obrigatório quando habilitado
            } else {
                inputNome.style.display = 'none';
                inputNome.required = false; // Remove o requisito de obrigatoriedade quando desabilitado
                inputNome.value = ''; // Limpa o valor do input quando desabilitado
            }
        });
    </script>
</body>
</html>