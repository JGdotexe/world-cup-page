<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Você precisa estar logado para palpitar o campeão."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Pega o corpo da requisição JSON (ex: {"time_campeao_id": "Brasil", "time_vice_id": "Alemanha"})
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $campeao = $data['time_campeao_id'] ?? '';
    $vice = $data['time_vice_id'] ?? '';

    if (empty($campeao)) {
        echo json_encode(["sucesso" => false, "mensagem" => "Escolher o campeão é obrigatório."]);
        exit;
    }

    try {
        $sql = "INSERT INTO palpites_campeao (usuario_id, time_campeao_id, time_vice_id) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE time_campeao_id = VALUES(time_campeao_id), time_vice_id = VALUES(time_vice_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $campeao, $vice]);
        
        echo json_encode(["sucesso" => true, "mensagem" => "Palpite de campeão e vice salvo com sucesso!"]);
    } catch (\PDOException $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro no servidor: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Método inválido."]);
}
?>
