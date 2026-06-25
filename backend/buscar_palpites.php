<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Não autenticado."]);
    exit;
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Busca todos os palpites desse usuário, junto com o token de compartilhamento para ele usar
    $stmtUser = $pdo->prepare("SELECT token_compartilhamento FROM usuarios WHERE id = ?");
    $stmtUser->execute([$usuario_id]);
    $token = $stmtUser->fetchColumn();

    $stmt = $pdo->prepare("SELECT jogo_id, resultado_escolhido, fase FROM palpites_jogos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $palpites = $stmt->fetchAll();

    $stmtCampeao = $pdo->prepare("SELECT time_campeao_id, time_vice_id FROM palpites_campeao WHERE usuario_id = ?");
    $stmtCampeao->execute([$usuario_id]);
    $campeao = $stmtCampeao->fetch() ?: null;

    echo json_encode([
        "sucesso" => true,
        "nome" => $_SESSION['usuario_nome'] ?? '',
        "token_compartilhamento" => $token,
        "palpites" => $palpites,
        "palpite_campeao" => $campeao,
    ]);
} catch (\PDOException $e) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro: " . $e->getMessage()]);
}
?>
