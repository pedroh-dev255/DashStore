<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../view/clientes.php");
}

require("../db.php");

// Obtém dados do cliente
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
$rows = mysqli_num_rows($result);

if ($rows != 1) {
    header("Location: clientes.php");
    exit();
}
$row = mysqli_fetch_assoc($result);

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'listar_produtos') {
        // Listar produtos não vendidos (status = 0)
        $sql = "SELECT e.id, p.nome, e.vlr_venda 
                FROM estoque e
                JOIN produtos p ON e.id_prod = p.id
                WHERE e.status = 0";
        $result = $conn->query($sql);

        $produtos = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $produtos[] = $row;
            }
        }

        // Retorna os produtos como JSON
        echo json_encode($produtos);
        exit();
    }

    if ($_GET['action'] == 'fechar_pedido') {
        // Inicia a transação
        $conn->begin_transaction();
        
        try {
            // Dados recebidos
            $data = json_decode(file_get_contents('php://input'), true);
            $id_cliente = $data['id_cliente'];
            $produtos = $data['produtos'];
    
            // Insere o pedido
            $sql = "INSERT INTO pedidos (id_cliente, data_pedido, status) VALUES (?, CURDATE(), 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id_cliente);
            $stmt->execute();
            $id_pedido = $conn->insert_id;
    
            // Insere os produtos no pedido
            if ($id_pedido) {
                foreach ($produtos as $produto) {
                    // Insere os produtos no pedido
                    $sql = "INSERT INTO pedido_produtos (id_pedido, id_produto, preco) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('iid', $id_pedido, $produto['id'], $produto['preco']);
                    $stmt->execute();

                    echo $stmt->error;
        
                    // Verifica se a inserção ocorreu corretamente
                    if ($stmt->affected_rows > 0) {
                        // Atualiza o status do produto para vendido
                        $sql = "UPDATE estoque SET status = 1 WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('i', $produto['id']);
                        $stmt->execute();
                    } else {
                        echo "Erro ao salvar o produto no pedido.";
                        exit();
                    }
                }
                echo "Pedido cadastrado com sucesso!";
            } else {
                echo "Erro ao gerar o pedido.";
            }
    
            // Commit da transação
            $conn->commit();
    
            echo "Pedido cadastrado com sucesso!";
        } catch (Exception $e) {
            // Em caso de erro, desfaz as alterações
            $conn->rollback();
            echo "Erro ao cadastrar o pedido: " . $e->getMessage();
        }
    
        exit();
    }
    
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../style/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style/cad_produtos.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Pedido</title>
    <script>
        let produtosSelecionados = {};

        // Carrega produtos não vendidos e lista em uma tabela
        function loadProdutos() {
            fetch(window.location.href + '&action=listar_produtos')
            .then(response => response.json())
            .then(data => {
                let tabela = document.getElementById('produtosDisponiveis').getElementsByTagName('tbody')[0];
                tabela.innerHTML = '';

                data.forEach(produto => {
                    let row = tabela.insertRow();
                    row.innerHTML = `
                        <td>${produto.nome}</td>
                        <td>${produto.vlr_venda}</td>
                        <td><button onclick="addProduto(${produto.id}, '${produto.nome}', ${produto.vlr_venda})">Adicionar</button></td>`;
                });
            });
        }

        // Adiciona o produto selecionado à tabela de pedidos
        function addProduto(id, nome, precoVenda) {
            produtosSelecionados[id] = { nome: nome, preco: parseFloat(precoVenda) };
            atualizarTabela();

            // Remove o produto da lista de disponíveis
            let tabelaDisponiveis = document.getElementById('produtosDisponiveis').getElementsByTagName('tbody')[0];
            for (let i = 0; i < tabelaDisponiveis.rows.length; i++) {
                if (tabelaDisponiveis.rows[i].cells[0].innerText === nome) {
                    tabelaDisponiveis.deleteRow(i);
                    break;
                }
            }
        }

        // Atualiza a tabela de produtos selecionados
        function atualizarTabela() {
            let tabela = document.getElementById('tabelaPedidos').getElementsByTagName('tbody')[0];
            tabela.innerHTML = '';
            let total = 0;

            for (let id in produtosSelecionados) {
                let produto = produtosSelecionados[id];
                let subtotal = produto.preco;
                total += subtotal;

                let row = tabela.insertRow();
                row.innerHTML = `
                    <td>${produto.nome}</td>
                    <td><input type="number" value="${produto.preco}" onchange="alterarPreco(${id}, this.value)"></td>
                    <td>${subtotal.toFixed(2)}</td>
                    <td><button onclick="removeProduto(${id})">Remover</button></td>`;
            }

            document.getElementById('valorTotal').innerText = total.toFixed(2);
        }

        // Altera o preço de um produto na tabela
        function alterarPreco(id, novoPreco) {
            produtosSelecionados[id].preco = parseFloat(novoPreco);
            atualizarTabela();
        }

        // Remove produto da tabela
        function removeProduto(id) {
            delete produtosSelecionados[id];
            atualizarTabela();

            // Atualiza o status do produto no banco para 'disponível' novamente
            fetch(window.location.href + '&action=remover_produto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_produto: id })
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Confirmação ou erro
                loadProdutos(); // Recarrega a lista de produtos disponíveis
            });
        }

        // Fecha o pedido e salva no banco de dados
        function fecharPedido() {
            let produtos = [];

            for (let id in produtosSelecionados) {
                produtos.push({
                    id: id,
                    preco: produtosSelecionados[id].preco
                });
            }

            // Envia os dados via AJAX
            fetch(window.location.href + '&action=fechar_pedido', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({produtos: produtos, id_cliente: <?php echo $_GET['id']; ?>})
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload(); // Recarrega a página
            });
        }
    </script>
</head>
<body onload="loadProdutos()">
    <!-- Deslogar -->
    <form action="../" method="get">
        <input type="hidden" name="logoff" value='true'>
        <input type="submit" value="Deslogar">
    </form>

    <!-- Voltar ao dashboard -->
    <a href="../view/clientes.php">Voltar</a>
    
    <h2>Cadastrando novo pedido para: <?php echo $row['nome']; ?></h2>

    <!-- Tabela de produtos disponíveis (não vendidos) -->
    <h3>Produtos Disponíveis</h3>
    <a href="./cad_estoque.php">Ir para cadastro de produtos</a>
    <table id="produtosDisponiveis" border="1">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço de Venda</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Tabela de produtos adicionados -->
    <h3>Produtos Selecionados</h3>
    <table id="tabelaPedidos" border="1">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Valor total -->
    <h3>Total: R$ <span id="valorTotal">0.00</span></h3>

    <!-- Botão para fechar o pedido -->
    <button onclick="fecharPedido()">Fechar Pedido</button>
</body>
</html>
