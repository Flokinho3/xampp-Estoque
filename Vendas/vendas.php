
<?php
$FILE_COMANDAS = 'Comandas/'; // Define the correct path to the JSON files
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas</title>
    <link rel="stylesheet" href="../CSS/vendas.css">
</head>
<body>
    <div class="Top_bar">
        <a href="../index.html">Home</a>
        <a href="../Estoque/estoque.php">Estoque</a>
        <a href="vendas.php">Vendas</a>
    </div>
    
    <div class="Abrir_comanda">
        <h2>Abrir comanda</h2>
        <form action="vendas_comandas.php" method="POST">
            <input type="number" name="numero" placeholder="Número da comanda" required>
            <input type="hidden" name="acao" value="abrir">
            <button type="submit" name="Abrir_comanda">Abrir</button>
        </form>
    </div>

    <div class="Comandas_abertas">
        <h2>Comandas abertas</h2>
        <div class="comanda-buttons">
            <?php
            // Verifique se o diretório e arquivos JSON existem antes de tentar carregá-los
            $comandas = glob($FILE_COMANDAS . '/*.json');
            
            if ($comandas) {
                foreach ($comandas as $comandaFile) {
                    $comandaData = json_decode(file_get_contents($comandaFile), true);
                    if ($comandaData === null) {
                        continue; // Skip if the file content is not valid JSON
                    }
                    $statusClass = '';
                    if ($comandaData['status'] == "aberta") {
                        $statusClass = 'comanda-aberta';
                    } elseif ($comandaData['status'] == "fechada") {
                        $statusClass = 'comanda-fechada';
                    } elseif ($comandaData['status'] == "emaberto") {
                        $statusClass = 'comanda-emaberto';
                    }

                    // Formulário para redirecionar para 'abrir_comanda.php'
                    echo "<form action='abrir_comanda.php' method='POST'>";
                    echo "<button type='submit' name='numero_comanda' value='" . $comandaData['numero'] . "' class='comanda-button $statusClass'>Comanda " . $comandaData['numero'] . "</button>";
                    echo "</form>";
                }
            } else {
                echo "<p>Nenhuma comanda encontrada.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
