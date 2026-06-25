<?php
session_start(); // Inicia o gerenciador de sessão (para depois logar)
require_once 'conexao.php';

// Avisa ao front-end que a resposta será em formato JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados que vieram do formulário HTML
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Validação básica
    if (empty($nome) || empty($email) || empty($senha)) {
        echo json_encode(["sucesso" => false, "mensagem" => "Todos os campos são obrigatórios."]);
        exit;
    }

    // Criptografa a senha antes de salvar (Padrão ouro de segurança em PHP)
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Gera um token único de compartilhamento para o usuário
    $token = bin2hex(random_bytes(16));

    try {
        // Prepara o SQL para evitar SQL Injection
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, token_compartilhamento) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $senha_hash, $token]);
        
        echo json_encode(["sucesso" => true, "mensagem" => "Cadastro realizado com sucesso! Vá para o Login."]);
    } catch (\PDOException $e) {
        // O código 23000 do MySQL significa que feriu a regra "UNIQUE" (e-mail já existe)
        if ($e->getCode() == 23000) {
            echo json_encode(["sucesso" => false, "mensagem" => "Este e-mail já está cadastrado."]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro no servidor: " . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Método inválido. Use POST."]);
}
?>
