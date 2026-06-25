<?php
require_once 'conexao.php';

header('Content-Type: application/json');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(["sucesso" => false, "mensagem" => "Token não fornecido."]);
    exit;
}

try {
    // 1. Descobre quem é o dono do token
    $stmtUser = $pdo->prepare("SELECT id, nome FROM usuarios WHERE token_compartilhamento = ?");
    $stmtUser->execute([$token]);
    $usuario = $stmtUser->fetch();

    if (!$usuario) {
        echo json_encode(["sucesso" => false, "mensagem" => "Bolão não encontrado ou link inválido."]);
        exit;
    }

    // 2. Busca os palpites dessa pessoa
    $stmt = $pdo->prepare("SELECT jogo_id, resultado_escolhido, fase FROM palpites_jogos WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $palpites = $stmt->fetchAll();

    echo json_encode([
        "sucesso" => true,
        "nome_dono" => $usuario['nome'],
        "palpites" => $palpites
    ]);
} catch (\PDOException $e) {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro: " . $e->getMessage()]);
}
?>
