<?php
// Configurações do Banco de Dados no Docker
$host = 'db';          // Nome do container do MySQL no docker-compose
$db   = 'bolao';       // Nome do banco que criamos
$user = 'root';        // Usuário
$pass = 'root';        // Senha

// Configura o DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra os erros na tela
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna os dados como array associativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Mais segurança contra SQL Injection
];

try {
    // Tenta conectar
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Descomente a linha abaixo se quiser testar visualmente se a conexão deu certo:
    // echo "Conexão com o banco de dados realizada com sucesso!";
} catch (\PDOException $e) {
    // Se der erro, mostra a mensagem e para a execução
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
