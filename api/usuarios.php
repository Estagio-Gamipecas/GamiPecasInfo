<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/database.php';
session_start();

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
    exit();
}

// Verificar se é admin
if ($_SESSION['user_tipo'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Sem permissão']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// GET - Listar utilizadores
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Buscar um utilizador específico
        $id = (int)$_GET['id'];
        $sql = "SELECT id, nome, email, telefone, numero_cliente, tipo, ativo, ultimo_acesso, created_at 
                FROM utilizadores WHERE id = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['success' => false, 'error' => 'Utilizador não encontrado']);
        }
    } else {
        // Listar todos
        $sql = "SELECT id, nome, email, telefone, numero_cliente, tipo, ativo, ultimo_acesso, created_at 
                FROM utilizadores ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $users = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($users);
    }
    exit();
}

// POST - Criar utilizador
if ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nome = $conn->real_escape_string($data['nome']);
    $email = $conn->real_escape_string($data['email']);
    $senha = password_hash($data['senha'], PASSWORD_DEFAULT);
    $telefone = $conn->real_escape_string($data['telefone'] ?? '');
    $numero_cliente = $conn->real_escape_string($data['numero_cliente']);
    $tipo = $conn->real_escape_string($data['tipo'] ?? 'operador');
    $ativo = isset($data['ativo']) ? (int)$data['ativo'] : 1;
    
    $sql = "INSERT INTO utilizadores (nome, email, senha, telefone, numero_cliente, tipo, ativo) 
            VALUES ('$nome', '$email', '$senha', '$telefone', '$numero_cliente', '$tipo', $ativo)";
    
    if ($conn->query($sql)) {
        $id = $conn->insert_id;
        
        // Registrar log
        $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) 
                VALUES ({$_SESSION['user_id']}, '{$_SESSION['user_nome']}', 'Criou utilizador', 'ID: $id', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log);
        
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// PUT - Atualizar utilizador
if ($method == 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
        exit();
    }
    
    $id = (int)$data['id'];
    $nome = $conn->real_escape_string($data['nome']);
    $email = $conn->real_escape_string($data['email']);
    $telefone = $conn->real_escape_string($data['telefone'] ?? '');
    $numero_cliente = $conn->real_escape_string($data['numero_cliente']);
    $tipo = $conn->real_escape_string($data['tipo']);
    $ativo = isset($data['ativo']) ? (int)$data['ativo'] : 1;
    
    $sql = "UPDATE utilizadores 
            SET nome = '$nome', 
                email = '$email', 
                telefone = '$telefone', 
                numero_cliente = '$numero_cliente', 
                tipo = '$tipo', 
                ativo = $ativo 
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        // Registrar log
        $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) 
                VALUES ({$_SESSION['user_id']}, '{$_SESSION['user_nome']}', 'Atualizou utilizador', 'ID: $id', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// DELETE - Eliminar utilizador
if ($method == 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($data['id']) ? (int)$data['id'] : 0);
    
    if ($id == 0) {
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
        exit();
    }
    
    // Não deixar eliminar o próprio admin
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Não pode eliminar o próprio utilizador']);
        exit();
    }
    
    $sql = "DELETE FROM utilizadores WHERE id = $id";
    
    if ($conn->query($sql)) {
        // Registrar log
        $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) 
                VALUES ({$_SESSION['user_id']}, '{$_SESSION['user_nome']}', 'Eliminou utilizador', 'ID: $id', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Método não permitido']);
?>