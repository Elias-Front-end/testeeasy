<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: 'app';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$debug = getenv('APP_DEBUG') === 'true';
$dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$attempts = 0;
$maxAttempts = 5;
while (true) {
    try {
        $pdo = new PDO($dsn, $user, $password, $options);
        
        // Tabela users atualizada com todos os campos solicitados
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        break;
    } catch (PDOException $e) {
        $attempts++;
        if ($attempts >= $maxAttempts) {
            http_response_code(500);
            if ($debug) {
                echo 'Erro ao conectar ao banco de dados: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            } else {
                echo 'Erro ao conectar ao banco de dados.';
            }
            exit;
        }
        sleep(2);
    }
}
