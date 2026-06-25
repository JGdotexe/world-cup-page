<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se a pessoa está logada
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Você precisa estar logado para realizar esta ação."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];

    try {
        // Graças ao ON DELETE CASCADE no banco, apagar o usuário apaga todos os palpites dele também!
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);

        // Mata a sessão (faz o logoff na hora)
        session_unset();
        session_destroy();
        
        echo json_encode(["sucesso" => true, "mensagem" => "Conta e palpites excluídos com sucesso."]);
    } catch (\PDOException $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro no servidor: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Método inválido."]);
}
?>
