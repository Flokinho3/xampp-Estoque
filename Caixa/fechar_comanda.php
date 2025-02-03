<?php
// Caminho da pasta onde as comandas estão armazenadas
$comandasDir = "../Vendas/Comandas/";

// Verifica se a pasta existe
if (!is_dir($comandasDir)) {
    echo "Pasta de comandas não encontrada.";
    exit;
}

// Obtém a lista de arquivos JSON na pasta
$comandas = glob($comandasDir . "*.json");

// Verifica se há comandas
if (empty($comandas)) {
    echo "Não há comandas disponíveis.";
    exit;
}

// Se o número da comanda foi especificado via GET
if (isset($_GET['numero'])) {
    $comandaNumero = $_GET['numero'];
    $comandaFile = $comandasDir . "{$comandaNumero}.json";

    // Verifica se o arquivo existe
    if (!file_exists($comandaFile)) {
        echo "Comanda não encontrada.";
        exit;
    }

    // Carrega os dados da comanda
    $jsonContent = file_get_contents($comandaFile);
    $data = json_decode($jsonContent, true);

    // Processa ações via POST (remover item ou vender/fechar comanda)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action'])) {
            // Remoção de item
            if ($_POST['action'] == 'remover' && $data['status'] == 'aberta') {
                $index = isset($_POST['index']) ? intval($_POST['index']) : -1;
                if ($index >= 0 && $index < count($data['produtos'])) {
                    // Remove o item pelo índice
                    array_splice($data['produtos'], $index, 1);
                    // Salva a comanda atualizada
                    file_put_contents($comandaFile, json_encode($data, JSON_PRETTY_PRINT));
                }
                // Redireciona para evitar reenvio do formulário
                header("Location: fechar_comanda.php?numero={$comandaNumero}");
                exit;
            }
            // Fechamento da comanda
            if ($_POST['action'] == 'vender' && $data['status'] == 'aberta') {
                // Atualiza status e horário de fechamento
                $data['status'] = 'fechada';
                $data['horario_fechado'] = date("Y-m-d H:i:s");
                file_put_contents($comandaFile, json_encode($data, JSON_PRETTY_PRINT));
                header("Location: fechar_comanda.php?numero={$comandaNumero}");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fechar Comanda</title>
    <link rel="stylesheet" href="../CSS/fecharcomanda.css">
</head>
<body>
    <div class = "Top_bar">
        <a href="../index.html">Home</a>
        <a href="../Estoque/estoque.php">Estoque</a>
        <a href="../Vendas/vendas.php">Vendas</a>
    </div>
    <?php if (!isset($_GET['numero'])): ?>
        <h1>Escolha uma Comanda</h1>
        <ul>
            <?php foreach ($comandas as $comandaPath): ?>
                <?php
                    // Extrai o número da comanda a partir do nome do arquivo
                    $comandaNome = basename($comandaPath, ".json");
                ?>
                <li>
                    <a href="fechar_comanda.php?numero=<?php echo urlencode($comandaNome); ?>">
                        Comanda <?php echo htmlspecialchars($comandaNome); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <h1>Comanda <?php echo htmlspecialchars($comandaNumero); ?></h1>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($data['status']); ?></p>
        <p><strong>Horário de Abertura:</strong> <?php echo htmlspecialchars($data['horario_aberto']); ?></p>
        <p><strong>Horário de Fechamento:</strong> <?php echo ($data['horario_fechado'] ? htmlspecialchars($data['horario_fechado']) : 'Ainda não fechado'); ?></p>

        <h2>Produtos:</h2>
            <?php if (empty($data['produtos'])): ?>
                <p>Nenhum produto na comanda.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($data['produtos'] as $index => $produto): ?>
                        <li>
                            <?php
                                // Exibe os detalhes do produto formatados
                                if (is_array($produto)) {
                                    echo htmlspecialchars($produto['nome']) . 
                                        ' (Quantidade: ' . htmlspecialchars($produto['quantidade']) . 
                                        ', ID: ' . htmlspecialchars($produto['id'] ?? 'N/A') . ')';
                                } else {
                                    // Caso o produto seja uma string (formato antigo)
                                    echo htmlspecialchars($produto);
                                }
                            ?>
                            <!-- Botão para remover (só aparece se a comanda estiver aberta) -->
                            <?php if ($data['status'] == 'aberta'): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="remover">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button type="submit">Remover</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        <?php if ($data['status'] == 'aberta'): ?>
            <!-- Botão para vender (fechar comanda) -->
            <form method="post">
                <input type="hidden" name="action" value="vender">
                <button type="submit">Vender</button>
            </form>
        <?php else: ?>
            <p>A comanda já foi fechada.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>