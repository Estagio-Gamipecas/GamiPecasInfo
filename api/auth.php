<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/database.php';
session_start();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['email']) || !isset($data['senha'])) {
        echo json_encode(['success' => false, 'error' => 'Email e senha são obrigatórios']);
        exit();
    }
    
    $email = $conn->real_escape_string($data['email']);
    $senha = $data['senha'];
    
    // Buscar utilizador
    $sql = "SELECT * FROM utilizadores WHERE email = '$email' AND ativo = 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'];
            $_SESSION['user_numero'] = $user['numero_cliente'];
            
            // Atualizar último acesso
            $conn->query("UPDATE utilizadores SET ultimo_acesso = NOW() WHERE id = {$user['id']}");
            
            // Registrar log
            $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, ip_address) 
                    VALUES ({$user['id']}, '{$user['nome']}', 'login', '{$_SERVER['REMOTE_ADDR']}')";
            $conn->query($log);
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                    'tipo' => $user['tipo']
                ]
            ]);
            exit();
        }
    }
    
    echo json_encode(['success' => false, 'error' => 'Email ou senha incorretos']);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Método não permitido']);
?>