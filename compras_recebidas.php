<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Buscar compras pendentes
$sql = "SELECT * FROM compras_site WHERE status_picking = 'pendente' ORDER BY data_compra DESC";
$compras = $conn->query($sql);

// Processar criação de picking a partir da compra
if (isset($_GET['criar_picking'])) {
    $compra_id = intval($_GET['criar_picking']);
    
    $compra = $conn->query("SELECT * FROM compras_site WHERE id = $compra_id")->fetch_assoc();
    
    if ($compra) {
        $produtos = json_decode($compra['produtos'], true);
        
        // Criar encomenda
        $stmt = $conn->prepare("INSERT INTO encomendas (cliente_nome, prioridade, status) VALUES (?, 'media', 'pendente')");
        $stmt->bind_param("s", $compra['cliente_nome']);
        $stmt->execute();
        $encomenda_id = $conn->insert_id;
        
        // Adicionar itens (simplificado - precisa mapear produtos reais)
        foreach ($produtos as $produto) {
            $stmt_item = $conn->prepare("INSERT INTO encomenda_itens (encomenda_id, produto_id, quantidade, status_picking) VALUES (?, ?, ?, 'pendente')");
            $produto_id = $produto['id'] ?? 1; // fallback
            $quantidade = $produto['quantidade'] ?? 1;
            $stmt_item->bind_param("iii", $encomenda_id, $produto_id, $quantidade);
            $stmt_item->execute();
        }
        
        // Marcar como processado
        $conn->query("UPDATE compras_site SET status_picking = 'processado' WHERE id = $compra_id");
        
        header('Location: compras_recebidas.php?msg=sucesso');
        exit();
    }
}

$mensagem = '';
if (isset($_GET['msg']) && $_GET['msg'] == 'sucesso') {
    $mensagem = '<div class="alert-success"><i class="fas fa-check"></i> Picking criado com sucesso!</div>';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Compras Recebidas</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            min-height: 100vh;
            color: #fff;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo h1 {
            font-family: 'Orbitron', monospace;
            font-size: 24px;
            background: linear-gradient(135deg, #00ffff, #ff00ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .btn-voltar {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
        }
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(0, 255, 255, 0.2);
        }
        .card-title {
            font-size: 20px;
            margin-bottom: 20px;
            font-family: 'Orbitron', monospace;
            color: #00ffff;
            border-bottom: 1px solid rgba(0, 255, 255, 0.2);
            padding-bottom: 10px;
        }
        .compra-item {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .compra-info h3 { font-size: 16px; margin-bottom: 5px; }
        .compra-info p { font-size: 12px; color: rgba(255,255,255,0.6); }
        .btn-criar {
            background: linear-gradient(135deg, #00ffff, #ff00ff);
            color: #000;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .empty-state { text-align: center; padding: 50px; color: rgba(255,255,255,0.4); }
        .alert-success {
            background: rgba(34,197,94,0.2);
            border: 1px solid #22c55e;
            color: #22c55e;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <h1><i class="fas fa-shopping-cart"></i> Compras do Site</h1>
            </div>
            <a href="dashboard.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <?php echo $mensagem; ?>

        <div class="card">
            <div class="card-title">
                <i class="fas fa-clock"></i> Compras Aguardando Picking
                <span class="count"><?php echo $compras->num_rows ?? 0; ?></span>
            </div>

            <?php if($compras && $compras->num_rows > 0): ?>
                <?php while($row = $compras->fetch_assoc()): ?>
                    <div class="compra-item">
                        <div class="compra-info">
                            <h3><i class="fas fa-user"></i> <?php echo htmlspecialchars($row['cliente_nome']); ?></h3>
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['cliente_email']); ?></p>
                            <p><i class="fas fa-calendar"></i> Data: <?php echo date('d/m/Y H:i', strtotime($row['data_compra'])); ?></p>
                            <p><i class="fas fa-euro-sign"></i> Total: € <?php echo number_format($row['total'], 2); ?></p>
                        </div>
                        <a href="?criar_picking=<?php echo $row['id']; ?>" class="btn-criar" onclick="return confirm('Criar picking para <?php echo addslashes($row['cliente_nome']); ?>?')">
                            <i class="fas fa-boxes"></i> Criar Picking
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Nenhuma compra pendente</p>
                    <small>Quando clientes fizerem compras, aparecerão aqui</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>