<?php

// lista da bd disponiveis
// estoque
/*
    1	ID Primária    int(11)            Não    Nenhum            AUTO_INCREMENT        
    2	ID_USER        int(11)            Sim    NULL                         
    3	nome           text        utf8mb4_general_ci                         
    4	quantidade     int(11)            Não    Nenhum                         
    5	entrega        date            Não    Nenhum                
    6	preco          float           Não    Nenhum                         
*/

//caixa
/*
	1	ID Primária	int(11)			Não	Nenhum		AUTO_INCREMENT		
	2	data_inicial	            date			Não	Nenhum				
	3	data_final	                date			Não	Nenhum				
	4	valor_inicial_estoque	    float			Não	Nenhum				
	5	valor_final_estoque	        float			Não	Nenhum			
    6	Aberto	                    tinyint(1)		Não	Nenhum			
*/

//conexao com o bd localhost
function Conexao(){
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "gerente";

    $conn = new mysqli($host, $user, $pass, $db);

    if($conn->connect_error){
        die("Conexão falhou: " . $conn->connect_error);
    }

    return $conn; // Adicionando o retorno da conexão
}

//Estoque
//retorna uma lista com todos os produtos do estoque
function Estoque_lista(){
    $conn = Conexao(); // Agora a variável $conn recebe a conexão corretamente
    $sql = "SELECT * FROM estoque";
    $result = $conn->query($sql);
    $conn->close(); // Fecha a conexão após a consulta
    return $result;  
}

//Estoque
// Função para adicionar produto ao estoque
function Estoque_adicionar($nome, $quantidade, $entrega, $preco) {
    $conn = Conexao(); // Conectar ao banco de dados

    // Verifica se o item já existe
    $sql = "SELECT * FROM estoque WHERE nome = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        return false; // Retorna falso se a preparação falhar
    }

    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return false; // Retorna falso se o item já existir
    }
    $stmt->close(); // Fecha o statement antes de continuar

    // Inserir o novo produto
    $sql = "INSERT INTO estoque (nome, quantidade, entrega, preco) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        return false; // Retorna falso se a preparação falhar
    }

    $stmt->bind_param("sisd", $nome, $quantidade, $entrega, $preco);
    $stmt->execute();
    
    $resultado = $stmt->affected_rows > 0; // Verifica se a inserção foi bem-sucedida

    $stmt->close();
    $conn->close(); // Fecha a conexão

    return $resultado;
}

//Estoque
// Função para editar produto no estoque
function Estoque_editar($id, $nome, $quantidade, $entrega, $preco) {
    $conn = Conexao(); // Conectar ao banco de dados

    // Verificar se os dados são realmente diferentes antes de atualizar
    $sql = "SELECT nome, quantidade, entrega, preco FROM estoque WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($existing_nome, $existing_quantidade, $existing_entrega, $existing_preco);
    $stmt->fetch();
    $stmt->close();

    // Atualizar apenas os campos que foram alterados
    $nome = !empty($nome) ? $nome : $existing_nome;
    $quantidade = !empty($quantidade) ? $quantidade : $existing_quantidade;
    $entrega = !empty($entrega) ? $entrega : $existing_entrega;
    $preco = !empty($preco) ? $preco : $existing_preco;

    // Verificar se os dados realmente mudaram
    if ($nome == $existing_nome && $quantidade == $existing_quantidade && $entrega == $existing_entrega && $preco == $existing_preco) {
        return "Nenhuma alteração realizada"; // Se os dados não mudaram, não faz nada
    }

    // Atualizar o produto
    $sql = "UPDATE estoque SET nome = ?, quantidade = ?, entrega = ?, preco = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        return "Erro ao preparar a consulta (atualização do produto)";
    }

    $stmt->bind_param("sisdi", $nome, $quantidade, $entrega, $preco, $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $resultado = "Sucesso";
    } else {
        $resultado = "Nenhuma alteração realizada";
    }

    $stmt->close();
    $conn->close();

    return $resultado;
}

//Estoque
// Função para remover produto do estoque
function Estoque_remover($id) {
    $conn = Conexao(); // Conectar ao banco de dados

    // Verificar se o produto existe
    $sql = "SELECT * FROM estoque WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $stmt->close();
        $conn->close();
        return "Produto não encontrado";
    }
    $stmt->close();

    // Remover o produto
    $sql = "DELETE FROM estoque WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $conn->close();
        return "Erro ao preparar a consulta (remoção do produto)";
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $resultado = "Sucesso";
    } else {
        $resultado = "Erro ao remover produto";
    }

    $stmt->close();
    $conn->close();

    return $resultado;
}

//Caixa
// Função para abrir o caixa
function Caixa_abrir($valor) {
    if (!is_numeric($valor)) {
        return false; // Retorna falso se o valor não for numérico
    }

    $conn = Conexao(); // Conectar ao banco de dados

    try {
        // Verifica se o caixa já está aberto
        $sql = "SELECT * FROM caixa WHERE aberto = 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $conn->close();
            return false; // Retorna falso se o caixa já estiver aberto
        }

        // Inserir o novo registro de caixa
        $sql = "INSERT INTO caixa (data_inicial, valor_inicial_estoque, aberto) VALUES (CURDATE(), ?, 1)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $conn->close();
            return false; // Retorna falso se a preparação falhar
        }

        $stmt->bind_param("d", $valor);
        $stmt->execute();
        
        $resultado = $stmt->affected_rows > 0; // Verifica se a inserção foi bem-sucedida

        $stmt->close();
        $conn->close(); // Fecha a conexão

        return $resultado;
    } catch (Exception $e) {
        $conn->close();
        return false; // Retorna falso em caso de exceção
    }
}

//Caixa
// Função para fechar o caixa
function Caixa_fechar() {
    $conn = Conexao();
    
    try {
        // Obtém o estoque antes do fechamento
        $estoque_antes = [];
        $sql = "SELECT id, nome, quantidade, preco FROM estoque";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $estoque_antes[] = $row;
        }
        
        // Obtém os dados das comandas
        $comandas = [];
        $dir = '../Vendas/Comandas/';
        $arquivos = glob($dir . "*.json");
        
        foreach ($arquivos as $arquivo) {
            $json = file_get_contents($arquivo);
            $comandas[] = json_decode($json, true);
        }
        
        // Calcula o fechamento do caixa
        list($valor_inicial, $valor_final, $total_vendas, $lucro) = Caixa_calcula(json_encode($comandas));
        
        // Criar o relatório geral antes do fechamento
        $registro_geral = [
            'estoque_antes' => $estoque_antes,
            'itens_vendidos' => $comandas,
            'valor_inicial_estoque' => $valor_inicial,
            'valor_final_estoque' => $valor_final,
            'total_vendas' => $total_vendas,
            'lucro' => $lucro,
            'data_fechamento' => date('Y-m-d H:i:s')
        ];
        
        file_put_contents('registro_geral.json', json_encode($registro_geral, JSON_PRETTY_PRINT));
        
        // Limpa a pasta de comandas
        foreach ($arquivos as $arquivo) {
            unlink($arquivo);
        }
        
        // Atualiza o status do caixa para fechado
        $sql_update = "UPDATE caixa SET aberto = 0, data_final = CURDATE(), valor_final_estoque = ? WHERE aberto = 1";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("d", $valor_final);
        $stmt->execute();
        $stmt->close();
        
        return $registro_geral;
    } catch (Exception $e) {
        return false;
    } finally {
        $conn->close();
    }
}



//Caixa
// calcula o preço total do estoque/valor inicial do caixa / valor final do caixa / valor final do estoque /lucro ou prejuizo
function Caixa_calcula($json_comandas) {
    $conn = Conexao(); // Conectar ao banco de dados

    try {
        $comandas = json_decode($json_comandas, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false; // Retorna falso se os JSONs forem inválidos
        }

        

        // Obtém o estoque do banco de dados corretamente
        $estoque = [];
        $sql = "SELECT id, nome, quantidade, preco FROM estoque";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $estoque[$row['id']] = $row; // Usa o ID do produto como chave para facilitar acesso
        }

        $total_vendas = 0;

        // Processa cada comanda e ajusta o estoque
        foreach ($comandas as $comanda) {
            foreach ($comanda['produtos'] as $produto) {
                $id_produto = $produto['id'];
                $quantidade_vendida = $produto['quantidade'];

                if (isset($estoque[$id_produto])) {
                    // Subtrai a quantidade vendida do estoque
                    $estoque[$id_produto]['quantidade'] -= $quantidade_vendida;
                    
                    // Se a quantidade for negativa, ajusta para zero
                    if ($estoque[$id_produto]['quantidade'] < 0) {
                        $estoque[$id_produto]['quantidade'] = 0;
                    }

                    // Atualiza o banco de dados com a nova quantidade
                    $sql_update = "UPDATE estoque SET quantidade = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql_update);
                    $stmt->bind_param("ii", $estoque[$id_produto]['quantidade'], $id_produto);
                    $stmt->execute();
                    $stmt->close();

                    // Calcula o total das vendas
                    $total_vendas += $quantidade_vendida * $estoque[$id_produto]['preco'];
                }
            }
        }

        // Calcula o valor final do estoque
        $valor_final_estoque = 0;
        foreach ($estoque as $item) {
            $valor_final_estoque += $item['quantidade'] * $item['preco'];
        }

        // Obtém o valor inicial do estoque
        $sql_valor_inicial = "SELECT valor_inicial_estoque FROM caixa WHERE aberto = 1";
        $result = $conn->query($sql_valor_inicial);
        $row = $result->fetch_assoc();
        $valor_inicial_estoque = $row['valor_inicial_estoque'];

        // Calcula o lucro ou prejuízo
        $lucro = $total_vendas - ($valor_inicial_estoque - $valor_final_estoque);



        // Retorna os valores calculados
        return array($valor_inicial_estoque, $valor_final_estoque, $total_vendas, $lucro);
    } catch (Exception $e) {
        return false; // Retorna falso em caso de erro
    } finally {
        $conn->close();
    }
}

//vendas


