<?php
include_once "../Server/Server.php";  // Inclua o arquivo que contém as funções de abrir/fechar caixa

function listar_comandas(){
    $comandas = glob("../Vendas/Comandas/*.json");
    $comandas_abertas = [];
    foreach ($comandas as $comandaFile) {
        $comandaData = json_decode(file_get_contents($comandaFile), true);
        if ($comandaData === null) {
            continue; // Skip if the file content is not valid JSON
        }
        if ($comandaData['status'] == "aberta") {
            $comandas_abertas[] = $comandaData;
        }
    }
    return $comandas_abertas;
}

// Lógica para listar o histórico (Exemplo)
function listarHistorico() {
    $conn = Conexao(); // Conectar ao banco de dados
    
    /**
     * se o caixa estiver aberto, exibir o valor inicial do caixa
     * se o caixa estiver fechado, exibir o valor inicial do caixa e o valor final do caixa
     */
    $sql = "SELECT * FROM caixa";
    $result = $conn->query($sql);
    $historico = [];
    while ($row = $result->fetch_assoc()) {
        $historico[] = $row;
    }

    $conn->close(); // Fecha a conexão
    return $historico;
    
}

// Lógica para verificar o estado do caixa (aberto ou fechado)
function verificarCaixa() {
    $conn = Conexao(); // Conectar ao banco de dados
    $sql = "SELECT * FROM caixa WHERE aberto = 1 LIMIT 1";
    $result = $conn->query($sql);

    $caixaAberto = $result->num_rows > 0; // Retorna true se o caixa estiver aberto

    $conn->close(); // Fecha a conexão
    return $caixaAberto;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/caixa.css">
    <title>Caixa</title>
</head>
<body>
    <div class = "Top_bar">
        <a href="../index.html">Home</a>
        <a href="../Estoque/estoque.php">Estoque</a>
        <a href="../Vendas/vendas.php">Vendas</a>
        <a href="caixa.php">Caixa</a>
    </div>
    <div class="container">
        <h1>Caixa</h1>
        <!-- Seção para abrir caixa -->
        <?php if (!verificarCaixa()): ?>  <!-- Verifica se o caixa não está aberto -->
        <div class="AbriCaixa">
            <h2>Abrir caixa</h2>
            <form action="../Server/Caixa.php" method="POST">
                <input type="number" name="valor" placeholder="Digite o valor inicial" required>
                <input type="hidden" name="acao" value="abrir">
                <button type="submit" name="abrir">Abrir caixa</button>
            </form>

        </div>
        <?php else: ?>
        <!-- Caixa já aberto, não permite abrir novamente -->
        <div class="AbriCaixa">
            <h2>Caixa já aberto</h2>
        </div>
        <?php endif; ?>

        <!-- Seção para fechar caixa -->
        <?php if (verificarCaixa()): ?> <!-- Verifica se o caixa está aberto -->
        <div class="FecharCaixa">
            <h2>Fechar caixa</h2>
            <form action="../Server/Caixa.php" method="POST">
                <input type="hidden" name="acao" value="fechar">
                <button type="submit" name="fechar" onclick="return confirm('Você tem certeza que deseja fechar o caixa?')">Fechar caixa</button>
            </form>

        </div>
        <?php else: ?>
        <!-- Caixa fechado, não permite fechar -->
        <div class="FecharCaixa">
            <h2>Caixa já fechado</h2>
        </div>
        <?php endif; ?>

        <!-- Seção para exibir histórico -->
        <div class="Historico">
            <h2>Histórico</h2>
            <table>
                <tr>
                    <th>Data</th>
                    <th>Valor Inicial</th>
                </tr>
                <?php foreach (listarHistorico() as $historico): ?>
                <tr>
                    <td><?php echo $historico['data_inicial']; ?></td>
                    <td><?php echo $historico['valor_inicial_estoque']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            
        </div>
    </div>
</body>
</html>
