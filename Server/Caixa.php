<?php
include_once "Server.php";

echo "Chegou aqui!"; // Verifique se o script está sendo executado até aqui


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['acao']) && $_POST['acao'] == 'abrir') {
        // Lógica de abrir o caixa
        if (is_numeric($_POST['valor']) && $_POST['valor'] > 0) {
            $res = Caixa_abrir($_POST['valor']);
            
            if ($res) {
                echo "<script>alert('Caixa aberto com sucesso!'); window.location.href='../Caixa/caixa.php';</script>";
            } else {
                echo "<script>alert('Erro ao abrir caixa!\\nVerifique se o caixa já está aberto!'); window.location.href='../Caixa/caixa.php';</script>";
            }
        } else {
            echo "<script>alert('Por favor, insira um valor válido para abrir o caixa.'); window.location.href='../Caixa/caixa.php';</script>";
        }
    }

    if (isset($_POST['acao']) && $_POST['acao'] == 'fechar') {
        $res = Caixa_fechar();
        
        if ($res) {
            echo "<script>alert('Caixa fechado com sucesso!'); window.location.href='../Caixa/caixa.php';</script>";
        } else {
            echo "<script>alert('Erro ao fechar caixa!\\nVerifique se o caixa já está fechado ou se há transações pendentes.'); window.location.href='../Caixa/caixa.php';</script>";
        }
    }
}


?>