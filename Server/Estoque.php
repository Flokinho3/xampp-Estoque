<?php
include_once "Server.php";



// Verifica se o método é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['adicionar_estoque'])) {
        $res = Estoque_adicionar($_POST['nome'], $_POST['quantidade'], $_POST['entrega'], $_POST['preco']);
        
        if ($res) {
            echo "<script>alert('Produto adicionado com sucesso!'); window.location.href='../Estoque/estoque.php';</script>";
        } else {
            echo "<script>alert('Erro ao adicionar produto!\\nVerifique se o item já existe!'); window.location.href='../Estoque/estoque.php';</script>";
        }
    }

    // Verifica se a ação é editar
    if (isset($_POST['editar_estoque'])) {
        $id_produto = $_POST['id_produto'];
        $nome = $_POST['nome'];
        $quantidade = $_POST['quantidade'];
        $entrega = $_POST['entrega'];
        $preco = $_POST['preco'];

        // Chama a função para editar o produto
        $res = Estoque_editar($id_produto, $nome, $quantidade, $entrega, $preco);
        if ($res !== "Sucesso") {
            echo "<script>alert('Erro ao editar produto!\\n$res'); window.location.href='../Estoque/estoque.php';</script>";
        } else {
            echo "<script>alert('Produto editado com sucesso!'); window.location.href='../Estoque/estoque.php';</script>";
        }
    }

    // Verifica se a ação é remover
    if (isset($_POST['remover_estoque'])) {
        $id_produto = $_POST['id_produto'];

        // Chama a função para remover o produto
        $res = Estoque_remover($id_produto);
        if ($res !== "Sucesso") {
            echo "<script>alert('Erro ao remover produto!\\n$res'); window.location.href='../Estoque/estoque.php';</script>";
        } else {
            echo "<script>alert('Produto removido com sucesso!'); window.location.href='../Estoque/estoque.php';</script>";
        }
    }
}

?>