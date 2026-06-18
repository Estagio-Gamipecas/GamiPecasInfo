<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/database.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        $result = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
        $produtos = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($produtos);
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $nome = $conn->real_escape_string($data['nome']);
        $quantidade = (int)$data['quantidade'];
        $preco = (float)$data['preco'];
        
        $sql = "INSERT INTO produtos (nome, quantidade, preco) VALUES ('$nome', $quantidade, $preco)";
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;
        
    case 'DELETE':
        $id = (int)$_GET['id'];
        $conn->query("DELETE FROM produtos WHERE id = $id");
        echo json_encode(['success' => true]);
        break;
}
?>