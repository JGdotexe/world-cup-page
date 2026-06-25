<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se a pessoa está logada
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Você precisa estar logado para palpitar."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    
    // O Front-end agora vai mandar um Array JSON com todos os palpites da tela de uma vez só.
    // Exemplo do que vai chegar: [{"jogo_id": "760462", "resultado_escolhido": "CASA"}, {"jogo_id": "760463", "resultado_escolhido": "EMPATE"}]
    $json = file_get_contents('php://input');
    $palpites = json_decode($json, true);

    if (empty($palpites) || !is_array($palpites)) {
        echo json_encode(["sucesso" => false, "mensagem" => "Nenhum palpite recebido ou formato inválido."]);
        exit;
    }

    try {
        // Transação: garante que ou salva todos de uma vez, ou desfaz tudo se der erro na metade
        $pdo->beginTransaction(); 
        
        $sql = "INSERT INTO palpites_jogos (usuario_id, jogo_id, resultado_escolhido, fase) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE resultado_escolhido = VALUES(resultado_escolhido), fase = VALUES(fase)";
        $stmt = $pdo->prepare($sql);

        // Faz um loop no array que o front mandou e salva no banco um por um rapidinho
        foreach ($palpites as $p) {
            $jogo_id = $p['jogo_id'] ?? '';
            $resultado = $p['resultado_escolhido'] ?? ''; // 'CASA', 'VISITANTE', 'EMPATE'
            $fase = $p['fase'] ?? 'Grupos';
            
            if (!empty($jogo_id) && !empty($resultado)) {
                $stmt->execute([$usuario_id, $jogo_id, $resultado, $fase]);
            }
        }
        
        $pdo->commit(); // Finaliza a gravação no banco
        
        echo json_encode(["sucesso" => true, "mensagem" => "Todos os palpites foram salvos com sucesso!"]);
    } catch (\PDOException $e) {
        $pdo->rollBack(); // Cancela se deu ruim
        echo json_encode(["sucesso" => false, "mensagem" => "Erro no servidor: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Método inválido."]);
}
?>
