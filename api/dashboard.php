<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method == 'GET') {
    $data = [];
    
    // Total de produtos
    $result = $conn->query("SELECT COUNT(*) as total FROM produtos");
    $data['total_produtos'] = $result->fetch_assoc()['total'];
    
    // Produtos com stock baixo
    $result = $conn->query("SELECT COUNT(*) as total FROM produtos WHERE quantidade <= quantidade_minima");
    $data['stock_baixo'] = $result->fetch_assoc()['total'];
    
    // Total de regras ativas
    $result = $conn->query("SELECT COUNT(*) as total FROM regras_alertas WHERE ativo = 1");
    $data['regras_ativas'] = $result->fetch_assoc()['total'];
    
    // Notificações enviadas hoje
    $result = $conn->query("SELECT COUNT(*) as total FROM logs_notificacoes WHERE DATE(data_envio) = CURDATE() AND status = 'enviado'");
    $data['notificacoes_hoje'] = $result->fetch_assoc()['total'];
    
    // Picking ativos
    $result = $conn->query("SELECT COUNT(*) as total FROM encomendas WHERE status IN ('picking', 'preparacao')");
    $data['picking_ativos'] = $result->fetch_assoc()['total'];
    
    // Encomendas urgentes
    $result = $conn->query("SELECT COUNT(*) as total FROM encomendas WHERE prioridade = 'urgente' AND status != 'entregue'");
    $data['encomendas_urgentes'] = $result->fetch_assoc()['total'];
    
    // Produtos críticos
    $result = $conn->query("
        SELECT nome, quantidade, quantidade_minima, 
        (quantidade_minima - quantidade) as falta 
        FROM produtos 
        WHERE quantidade <= quantidade_minima 
        ORDER BY quantidade ASC 
        LIMIT 5
    ");
    $data['produtos_criticos'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Últimas notificações
    $result = $conn->query("
        SELECT l.*, p.nome as produto_nome, r.nome as regra_nome 
        FROM logs_notificacoes l
        LEFT JOIN produtos p ON l.produto_id = p.id
        LEFT JOIN regras_alertas r ON l.regra_id = r.id
        ORDER BY l.data_envio DESC 
        LIMIT 10
    ");
    $data['ultimas_notificacoes'] = $result->fetch_all(MYSQLI_ASSOC);
    
    // Últimas atividades
    $result = $conn->query("
        SELECT * FROM logs_atividades 
        ORDER BY data_hora DESC 
        LIMIT 10
    ");
    $data['ultimas_atividades'] = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($data);
    exit();
}

echo json_encode(['error' => 'Método não permitido']);
?>