<?php

require_once("../Server/Server.php");

$estoque = Estoque_lista();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/estoque.css?v=<?php echo time(); ?>">
    <title>Estoque</title>
    <script>
    // Função para carregar os dados do produto quando um produto é selecionado
    function carregarDadosProduto() {
        var produtoId = document.getElementById("produto").value;
        if (produtoId) {
            // Exibe os campos de edição
            document.getElementById("produtoDados").style.display = "block";

            // Faz uma requisição para obter os dados do produto
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "../Server/Estoque.php?id=" + produtoId, true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    var produto = JSON.parse(xhr.responseText);
                    document.getElementById("nome").value = produto.nome;
                    document.getElementById("quantidade").value = produto.quantidade;
                    document.getElementById("entrega").value = produto.entrega;
                    document.getElementById("preco").value = produto.preco;
                }
            };
            xhr.send();
        } else {
            // Se nenhum produto foi selecionado, esconde os campos
            document.getElementById("produtoDados").style.display = "none";
        }
    }
    </script>
</head>
<body>
    <div class = "Top_bar">
        <a href="../index.html">Home</a>
        <a href="estoque.php">Estoque</a>
        <a href="../Vendas/vendas.php">Vendas</a>
    </div>
    <div class="Container">
        <h1>Estoque</h1>
        <div class="Estoque_adicionar">
            <h2>Adicionar produto</h2>
            <form action="../Server/Estoque.php" method="POST">         
                <input type="text" name="nome" placeholder="Nome do produto" required>
                <input type="number" name="quantidade" placeholder="Quantidade" required>
                <input type="date" name="entrega" placeholder="Entrega">
                <input type="number" name="preco" placeholder="Preço" step="0.01" required>
                <input type="hidden" name="acao" value="adicionar">
                <button type="submit" name="adicionar_estoque">Adicionar</button>
            </form>
        </div>
        <div class="Editar_produto">
        <h2>Editar produto</h2>
            <form action="../Server/Estoque.php" method="POST">
                <!-- Dropdown para selecionar o nome do produto -->
                <label for="produto">Selecione o produto:</label>
                <select name="id_produto" id="produto" onchange="carregarDadosProduto()" required>
                    <option value="">Selecione...</option>
                    <?php
                    // Aqui você consulta o banco e lista os produtos
                    include_once "../Server/Conexao.php";
                    $conn = Conexao();
                    $sql = "SELECT id, nome FROM estoque";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                    }
                    $conn->close();
                    ?>
                </select>

                <!-- Campos de edição que só são exibidos após seleção -->
                <div id="produtoDados" style="display:none;">
                    <input type="text" name="nome" id="nome" placeholder="Nome do produto" required readonly>
                    <input type="number" name="quantidade" id="quantidade" placeholder="Quantidade">
                    <input type="date" name="entrega" id="entrega" placeholder="Entrega">
                    <input type="number" name="preco" id="preco" placeholder="Preço" step="0.01">
                    <input type="hidden" name="acao" value="editar">
                    <button type="submit" name="editar_estoque">Editar</button>
                </div>
            </form>
        </div>
        <div class="Estoque_remover">
            <h2>Remover produto</h2>
            <form action="../Server/Estoque.php" method="POST">
                <!-- Dropdown para selecionar o nome do produto -->
                <label for="produtoRemover">Selecione o produto:</label>
                <select name="id_produto" id="produtoRemover" required>
                    <option value="">Selecione...</option>
                    <?php
                    // Aqui você consulta o banco e lista os produtos
                    include_once "../Server/Conexao.php";
                    $conn = Conexao();
                    $sql = "SELECT id, nome FROM estoque";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                    }
                    $conn->close();
                    ?>
                </select>
                <div id="produtoRemoverDados" style="display:none;">
                    <input type="hidden" name="acao" value="remover">
                    <button type="submit" name="remover_estoque">Remover</button>
                </div>
                <script>
                    document.getElementById("produtoRemover").addEventListener("change", function() {
                        var produtoId = this.value;
                        if (produtoId) {
                            document.getElementById("produtoRemoverDados").style.display = "block";
                        } else {
                            document.getElementById("produtoRemoverDados").style.display = "none";
                        }
                    });
                </script>
            </form>
        </div>
        <div class="Estoque_geral">
            <h2>Estoque geral</h2>
            <?php

            if($estoque->num_rows > 0){
                while($row = $estoque->fetch_assoc()){
                    echo "<div class='Estoque_item'>";
                    echo "<p>Nome: " . $row['nome'] . "</p>";
                    echo "<p>Quantidade: " . $row['quantidade'] . "</p>";
                    echo "<p>Entrega: " . $row['entrega'] . "</p>";
                    echo "<p>Preço: " . $row['preco'] . "</p>";
                    echo "</div>";
                }
            }else{
                echo "<p>Nenhum produto no estoque</p>";
            }
            
            ?>
        </div>
    </div>
    
</body>
</html>