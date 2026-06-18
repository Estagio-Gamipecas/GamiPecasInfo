<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

if ($method == 'GET') {
    // Parâmetros de filtro
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $tipo = isset($_GET['tipo']) ? $conn->real_escape_string($_GET['tipo']) : null;
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
    
    $sql = "SELECT l.*, u.nome as utilizador_nome 
            FROM logs_atividades l 
            LEFT JOIN utilizadores u ON l.utilizador_id = u.id 
            WHERE 1=1";
    
    if ($tipo) {
        $sql .= " AND l.acao = '$tipo'";
    }
    
    if ($user_id) {
        $sql .= " AND l.utilizador_id = $user_id";
    }
    
    $sql .= " ORDER BY l.data_hora DESC LIMIT $limit";
    
    $result = $conn->query($sql);
    $logs = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($logs);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Método não permitido']);
?>