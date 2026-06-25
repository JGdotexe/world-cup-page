<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        echo json_encode(["sucesso" => false, "mensagem" => "E-mail e senha são obrigatórios."]);
        exit;
    }

    try {
        // Busca o usuário pelo e-mail
        $stmt = $pdo->prepare("SELECT id, nome, senha_hash FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Se o usuário existe e a senha fornecida bate com a criptografada
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Salva na sessão do PHP quem é o cara (para lembrarmos nos outros endpoints)
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            
            echo json_encode(["sucesso" => true, "mensagem" => "Login realizado com sucesso!", "nome" => $usuario['nome']]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "E-mail ou senha incorretos."]);
        }
    } catch (\PDOException $e) {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro no servidor: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Método inválido. Use POST."]);
}
?>
