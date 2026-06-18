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

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// GET - Listar regras
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $sql = "SELECT r.*, p.nome as produto_nome 
                FROM regras_alertas r 
                LEFT JOIN produtos p ON r.produto_id = p.id 
                WHERE r.id = $id";
        $result = $conn->query($sql);
        
        if ($result->num_rows == 1) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['success' => false, 'error' => 'Regra não encontrada']);
        }
    } else {
        $sql = "SELECT r.*, p.nome as produto_nome 
                FROM regras_alertas r 
                LEFT JOIN produtos p ON r.produto_id = p.id 
                ORDER BY r.created_at DESC";
        $result = $conn->query($sql);
        $regras = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($regras);
    }
    exit();
}

// POST - Criar regra
if ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $nome = $conn->real_escape_string($data['nome']);
    $tipo = $conn->real_escape_string($data['tipo']);
    $produto_id = isset($data['produto_id']) && $data['produto_id'] != '' ? (int)$data['produto_id'] : 'NULL';
    $valor_limite = (float)$data['valor_limite'];
    $mensagem = $conn->real_escape_string($data['mensagem']);
    $ativo = isset($data['ativo']) ? (int)$data['ativo'] : 1;
    
    $sql = "INSERT INTO regras_alertas (nome, tipo, produto_id, valor_limite, mensagem, ativo) 
            VALUES ('$nome', '$tipo', $produto_id, $valor_limite, '$mensagem', $ativo)";
    
    if ($conn->query($sql)) {
        $id = $conn->insert_id;
        
        // Registrar log
        $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) 
                VALUES ({$_SESSION['user_id']}, '{$_SESSION['user_nome']}', 'Criou regra de alerta', 'Regra: $nome', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log);
        
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// PUT - Atualizar regra
if ($method == 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
        exit();
    }
    
    $id = (int)$data['id'];
    $nome = $conn->real_escape_string($data['nome']);
    $tipo = $conn->real_escape_string($data['tipo']);
    $produto_id = isset($data['produto_id']) && $data['produto_id'] != '' ? (int)$data['produto_id'] : 'NULL';
    $valor_limite = (float)$data['valor_limite'];
    $mensagem = $conn->real_escape_string($data['mensagem']);
    $ativo = isset($data['ativo']) ? (int)$data['ativo'] : 1;
    
    $sql = "UPDATE regras_alertas 
            SET nome = '$nome', 
                tipo = '$tipo', 
                produto_id = $produto_id, 
                valor_limite = $valor_limite, 
                mensagem = '$mensagem', 
                ativo = $ativo 
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) 
                VALUES ({$_SESSION['user_id']}, '{$_SESSION['user_nome']}', 'Atualizou regra de alerta', 'ID: $id', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

// DELETE - Eliminar regra
if ($method == 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($data['id']) ? (int)$data['id'] : 0);
    
    if ($id == 0) {
        echo json_encode(['success' => false, 'error' => 'ID não fornecido']);
        exit();
    }
    
    $sql = "DELETE FROM regras_alertas WHERE id = $id";
    
    if ($conn->query($sql)) {
        $log = "INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) 
                VALUES ({$_SESSION['user_id']}, '{$_SESSION['user_nome']}', 'Eliminou regra de alerta', 'ID: $id', '{$_SERVER['REMOTE_ADDR']}')";
        $conn->query($log);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Método não permitido']);
?>