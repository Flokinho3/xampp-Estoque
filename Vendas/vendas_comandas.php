<?php

$FILE_COMANDAS = "Comandas/"; // Certifique-se de adicionar a barra no final do caminho

// Verifica se o diretório existe e, se não, cria
if (!file_exists($FILE_COMANDAS)) {
    mkdir($FILE_COMANDAS, 0777, true);  // Cria o diretório se não existir
}

function Abrir_comanda($numero) {
    global $FILE_COMANDAS; // Certifique-se de usar o caminho global do diretório
    
    // Verifica se tem alguma comanda aberta com o mesmo número
    $comandas = listarComandas();
    foreach ($comandas as $comandaFile) {
        $comandaData = json_decode(file_get_contents($FILE_COMANDAS . $comandaFile), true);
        if ($comandaData['status'] == "aberta" && $comandaData['numero'] == $numero) {
            return false;
        }
    }

    // Cria um JSON com o número da comanda e o status aberto
    $comanda = [
        "numero" => $numero,
        "status" => "aberta",
        "produtos" => [], // Array vazio para produtos inicialmente
        "horario_aberto" => date("Y-m-d H:i:s"),
        "horario_fechado" => null
    ];

    // Salva o JSON no diretório correto
    $filePath = $FILE_COMANDAS . $numero . ".json"; // Caminho correto para salvar o arquivo
    if (file_put_contents($filePath, json_encode($comanda))) {
        return true;
    } else {
        return false;
    }
}


function listarComandas() {
    global $FILE_COMANDAS;    
    $comandas = scandir($FILE_COMANDAS);
    $comandas = array_diff($comandas, array('..', '.', '.DS_Store')); // Remove '.' e '..'
    return $comandas;
}

if (isset($_POST['numero']) && $_POST['acao'] == "abrir") {
    $numero = $_POST['numero'];
    if (Abrir_comanda($numero)) {
        header("Location: vendas.php");
    } else {
        echo "Erro ao abrir a comanda.";
    }
}


?> 