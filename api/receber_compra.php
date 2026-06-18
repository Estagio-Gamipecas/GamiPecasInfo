<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'includes/database.php';

$response = ['success' => false, 'message' => ''];

// Receber dados da requisição
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    $response['message'] = 'Dados inválidos';
    echo json_encode($response);
    exit();
}

$compra_id = $data['compra_id'] ?? 0;
$cliente_nome = $data['cliente_nome'] ?? '';
$cliente_email = $data['cliente_email'] ?? '';
$total = $data['total'] ?? 0;
$produtos = json_encode($data['produtos'] ?? []);

if (empty($cliente_nome)) {
    $response['message'] = 'Nome do cliente é obrigatório';
    echo json_encode($response);
    exit();
}

// Verificar se já foi processada
$check = $conn->query("SELECT id FROM compras_site WHERE compra_id = $compra_id");
if ($check && $check->num_rows > 0) {
    $response['message'] = 'Compra já foi processada';
    echo json_encode($response);
    exit();
}

// Guardar compra
$stmt = $conn->prepare("
    INSERT INTO compras_site (compra_id, cliente_nome, cliente_email, total, produtos, status_picking) 
    VALUES (?, ?, ?, ?, ?, 'pendente')
");
$stmt->bind_param("issds", $compra_id, $cliente_nome, $cliente_email, $total, $produtos);
$stmt->execute();
$stmt->close();

$response['success'] = true;
$response['message'] = 'Compra recebida com sucesso!';
echo json_encode($response);
?>