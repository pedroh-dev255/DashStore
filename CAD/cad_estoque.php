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
                echo $_POST['nomes'] . " - " . $_POST['quantidade'] . "<br>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Estoque</title>
</head>
<body>
    <!-- Botão para deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>

    <a href="../view/">Voltar</a>
    <br><br><br>
    <form action="./cad_estoque.php" method="post">
        <label for="nomes">Nome:</label>
        <select name="nomes" id="nomes" required>
            <option value="" selected></option>
            <?php
                while($row = mysqli_fetch_assoc($result)){
                    echo "<option value=".$row['id'].">".$row['nome']."</option>";
                }
            ?>
            <option value="outro">Adicionar Novo</option>
        </select><br>

        <!-- Caso Usuario selecionar Adicionar novo abilita o input para novo produto -->
        <input type="text" name="nome" id="nome" style="display:none;" maxlength="200" placeholder="Digite o novo Produto"><br>
        Valor de compra do produto <br>
        <input type="number" step="0.01" name="compra" maxlength="200" placeholder="Valor de compra do produto" required><br><br>
        Valor do frete <br>
        <input type="number" step="0.01" name="frete" placeholder="Valor do frete se houver" required><br><br>
        Valor sugerido para venda <br>
        <input type="number" step="0.01" name="venda" placeholder="Valor de venda" required><br><br>
        Data da compra <br>
        <input type="date" name="dt_compra" required><br><br>
        Qnt de itens comprados <br>
        <input type="number" name="quantidade" placeholder="Quantidade comprada" required><br><br><br>
        <?php
            if(isset($_SESSION['log'])){
                echo "<b>" . $_SESSION['log'] . "</b><br><br>";
                unset($_SESSION['log']);
            }
            
        ?>
        <button type="submit">Salvar</button>
    </form>

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