<?php
require 'config.php';

$password = $_GET['pass'] ?? '';

// Proteção simples para não rodar acidentalmente em produção aberta
// Use ?pass=reset123 na URL
if ($password !== 'reset123') {
    die('Senha incorreta. Use ?pass=reset123 na URL.');
}

try {
    echo "Iniciando reset do banco...<br>";

    // Apaga a tabela antiga
    $pdo->exec("DROP TABLE IF EXISTS users");
    echo "Tabela 'users' apagada.<br>";

    // Cria a nova tabela com a estrutura completa
    $sql = 'CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        -- Informações Pessoais
        name VARCHAR(255) NOT NULL,
        cpf VARCHAR(14) NOT NULL UNIQUE,
        birth_date DATE NOT NULL,
        gender ENUM("masculino", "feminino", "outro", "prefero_nao_dizer") NOT NULL,
        marital_status VARCHAR(50),
        profession VARCHAR(100),
        
        -- Contato
        email VARCHAR(255) NOT NULL UNIQUE,
        cellphone VARCHAR(20) NOT NULL,
        landline VARCHAR(20),
        cep VARCHAR(10) NOT NULL,
        address VARCHAR(255) NOT NULL,
        address_number VARCHAR(20) NOT NULL,
        address_complement VARCHAR(100),
        neighborhood VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state CHAR(2) NOT NULL,
        
        -- Preferências e Extras
        interests TEXT, -- JSON ou lista separada por vírgula
        how_met VARCHAR(100),
        terms_accepted BOOLEAN NOT NULL DEFAULT 0,
        photo_path VARCHAR(255),
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

    $pdo->exec($sql);
    echo "Nova tabela 'users' criada com sucesso!<br>";
    echo "<a href='index.php'>Voltar para o cadastro</a>";

} catch (PDOException $e) {
    echo "Erro ao resetar banco: " . $e->getMessage();
}
