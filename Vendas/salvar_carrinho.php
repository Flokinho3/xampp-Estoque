<?php
// salvar_carrinho.php

// Adicione isso no início para permitir CORS (se necessário)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Verifica se o diretório existe
if (!is_dir('Comandas')) {
    if (!mkdir('Comandas', 0777, true)) { // Cria recursivamente
        echo json_encode(['success' => false, 'error' => 'Falha ao criar diretório']);
        exit;
    }
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validação mais robusta
if (empty($data['numero_comanda']) || !is_array($data['produtos'] ?? null)) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

// Sanitização do número da comanda
$numero_comanda = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['numero_comanda']);
$FILE_CARRINHO = "Comandas/" . $numero_comanda . ".json";

try {
    // Mantém outros dados da comanda se existirem
    $comanda = file_exists($FILE_CARRINHO) 
        ? json_decode(file_get_contents($FILE_CARRINHO), true) 
        : [];
    
    // Atualiza apenas os produtos mantendo outros dados
    $comanda['produtos'] = $data['produtos'];
    
    // Salvamento com verificação
    if (file_put_contents($FILE_CARRINHO, json_encode($comanda, JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Falha na escrita do arquivo');
    }
    
} catch (Exception $e) {
    error_log('Erro ao salvar carrinho: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno']);
}
?>