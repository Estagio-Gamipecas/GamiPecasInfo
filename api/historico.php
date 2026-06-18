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
    if (isset($_GET['id'])) {
        // Detalhes de uma encomenda específica
        $id = (int)$_GET['id'];
        
        // Buscar encomenda
        $result = $conn->query("SELECT * FROM encomendas WHERE id = $id");
        $encomenda = $result->fetch_assoc();
        
        if (!$encomenda) {
            echo json_encode(['error' => 'Encomenda não encontrada']);
            exit();
        }
        
        // Buscar itens
        $itens = $conn->query("
            SELECT ei.*, p.nome as produto_nome 
            FROM encomenda_itens ei 
            LEFT JOIN produtos p ON ei.produto_id = p.id 
            WHERE ei.encomenda_id = $id
        ");
        $encomenda['itens'] = $itens->fetch_all(MYSQLI_ASSOC);
        
        // Buscar preparação
        $prep = $conn->query("
            SELECT pp.*, u.nome as operador_nome 
            FROM preparacoes_picking pp 
            LEFT JOIN utilizadores u ON pp.operador_id = u.id 
            WHERE pp.encomenda_id = $id
        ");
        $encomenda['preparacao'] = $prep->fetch_assoc();
        
        echo json_encode($encomenda);
    } else {
        // Listar todas as encomendas
        $filtro = isset($_GET['filtro']) ? $conn->real_escape_string($_GET['filtro']) : 'todos';
        
        $sql = "SELECT e.*, 
                COUNT(ei.id) as total_itens,
                SUM(CASE WHEN ei.status_picking = 'coletado' THEN 1 ELSE 0 END) as itens_coletados,
                pp.data_inicio as prep_inicio,
                pp.data_fim as prep_fim,
                pp.status as prep_status,
                u.nome as operador_nome
                FROM encomendas e
                LEFT JOIN encomenda_itens ei ON e.id = ei.encomenda_id
                LEFT JOIN preparacoes_picking pp ON e.id = pp.encomenda_id
                LEFT JOIN utilizadores u ON pp.operador_id = u.id";
        
        if ($filtro != 'todos') {
            $sql .= " WHERE e.status = '$filtro'";
        }
        
        $sql .= " GROUP BY e.id ORDER BY e.data_encomenda DESC";
        
        $result = $conn->query($sql);
        $encomendas = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($encomendas);
    }
    exit();
}

echo json_encode(['error' => 'Método não permitido']);
?>