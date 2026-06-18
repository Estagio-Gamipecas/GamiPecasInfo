<?php
// includes/database.php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'estagio';

// Conexão com tratamento de erro melhorado
try {
    $conn = new mysqli($host, $user, $password, $database);
    
    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão: " . $conn->connect_error);
    }
    
    // Definir charset para UTF-8
    $conn->set_charset("utf8mb4");
    
    // Configurar para lançar exceções em erros (PHP 8+)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
} catch (Exception $e) {
    die("Erro de banco de dados: " . $e->getMessage());
}

// Para debug (opcional - remove depois)
// echo "✅ Conectado ao MySQL " . $conn->server_version;
?>