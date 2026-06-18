<?php
// cron/sincronizar_site.php
require_once '../includes/database.php';

$ultimo_id = 0;

// Buscar último ID sincronizado (guardar num ficheiro ou tabela)
if (file_exists('ultimo_sync.txt')) {
    $ultimo_id = (int)file_get_contents('ultimo_sync.txt');
}

// Chamar API do site
$url = "http://localhost/GamiPecas/api/encomendas.php?nova=1&ultimo_id=$ultimo_id";
$response = file_get_contents($url);
$encomendas = json_decode($response, true);

foreach ($encomendas as $encomenda) {
    // Verificar se já existe no peaking
    $check = $conn_peaking->query("SELECT id FROM encomendas WHERE numero_encomenda = 'SITE-{$encomenda['id']}'");
    
    if ($check->num_rows == 0) {
        // Inserir no peaking
        $numero_peaking = 'SITE-' . $encomenda['id'];
        $conn_peaking->query("
            INSERT INTO encomendas (numero_encomenda, cliente_nome, cliente_email, data_encomenda, status, prioridade)
            VALUES ('$numero_peaking', '{$encomenda['cliente_nome']}', '{$encomenda['cliente_email']}', '{$encomenda['data_encomenda']}', 'pendente', 'media')
        ");
        
        $ultimo_id = max($ultimo_id, $encomenda['id']);
    }
}

// Guardar último ID
file_put_contents('ultimo_sync.txt', $ultimo_id);
echo "Sincronização concluída! Último ID: $ultimo_id\n";
?>