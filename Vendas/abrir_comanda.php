<?php
include_once "../Server/Server.php"; 

$numero_comanda = isset($_POST['numero_comanda']) ? $_POST['numero_comanda'] : null;
if (!$numero_comanda) {
    die("Número da comanda não informado.");
}

$FILE_CARRINHO = "Comandas/" . $numero_comanda . ".json";

function listarEstoque() {
    $resultado = Estoque_lista(); 
    $produtos = [];
    if ($resultado && $resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $produtos[] = $row;
        }
    }
    return $produtos;
}

function listarCarrinho() {
    global $FILE_CARRINHO;
    if (file_exists($FILE_CARRINHO)) {
        $carrinho = json_decode(file_get_contents($FILE_CARRINHO), true);
        if (!isset($carrinho['produtos'])) {
            $carrinho['produtos'] = [];
        }
    } else {
        $carrinho = ['produtos' => []];
    }
    return $carrinho;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estoque e Carrinho</title>
  <link rel="stylesheet" href="../CSS/carrinho.css">
  <style>
    /* Estilos mantidos como antes */
  </style>
</head>
<body>
  <div class="container">
    <div class="estoque">
      <h2>Estoque Disponível</h2>
      <?php
      $estoque = listarEstoque();
      if (!empty($estoque)) {
          foreach ($estoque as $produto) {
              echo '<div class="item" data-id="' . $produto['ID'] . '">';
              echo '<span>' . $produto['nome'] . ' (Qtd: ' . $produto['quantidade'] . ')</span>';
              // Usar htmlspecialchars para escapar as aspas
              echo '<button onclick="adicionarAoCarrinho('.htmlspecialchars(json_encode($produto), ENT_QUOTES).')">Adicionar</button>';
              echo '</div>';
          }
      } else {
          echo "<p>Nenhum produto disponível.</p>";
      }
      ?>
    </div>

    <div class="carrinho">
      <h2>Conteúdo do Carrinho</h2>
      <div id="carrinhoItems">
        <?php
        $carrinho = listarCarrinho();
        if (!empty($carrinho['produtos'])) {
            foreach ($carrinho['produtos'] as $item) {
                echo '<div class="cart-item" data-id="' . $item['id'] . '">';
                echo '<span>' . $item['nome'] . ' (Qtd: ' . $item['quantidade'] . ')</span>';
                echo '<button onclick="removerDoCarrinho(this)">Remover</button>';
                echo '</div>';
            }
        } else {
            echo "<p>Carrinho vazio.</p>";
        }
        ?>
      </div>
      <button onclick="finalizarCompra()">Finalizar Compra</button>
    </div>
  </div>

  <script>
    function adicionarAoCarrinho(produto) {
      // Verificar se o item já está no carrinho
      const existingItem = document.querySelector(`.cart-item[data-id="${produto.ID}"]`);
      if (existingItem) {
        // Incrementar quantidade
        const span = existingItem.querySelector('span');
        const match = span.textContent.match(/Qtd: (\d+)/);
        let qtd = parseInt(match[1]) + 1;
        span.textContent = `${produto.nome} (Qtd: ${qtd})`;
      } else {
        // Adicionar novo item
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.setAttribute('data-id', produto.ID);
        div.innerHTML = `<span>${produto.nome} (Qtd: 1)</span>
                         <button onclick="removerDoCarrinho(this)">Remover</button>`;
        document.getElementById('carrinhoItems').appendChild(div);
      }
      atualizarCarrinhoNoServidor();
    }

    function removerDoCarrinho(botao) {
      const item = botao.parentElement;
      item.remove();
      atualizarCarrinhoNoServidor();
    }

    function atualizarCarrinhoNoServidor() {
      const carrinhoItems = document.querySelectorAll('.cart-item');
      const produtos = [];
      carrinhoItems.forEach(item => {
        const id = item.getAttribute('data-id');
        const nome = item.querySelector('span').textContent.split(' (Qtd: ')[0];
        const quantidade = parseInt(item.querySelector('span').textContent.match(/Qtd: (\d+)/)[1]);
        produtos.push({ id, nome, quantidade });
      });

      fetch('salvar_carrinho.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          numero_comanda: <?php echo $numero_comanda; ?>,
          produtos: produtos
        })
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          console.error('Erro ao salvar carrinho');
        }
      });
    }

    function finalizarCompra() {
      window.location.href = 'vendas.php';
    }
  </script>
</body>
</html>