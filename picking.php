<?php
session_start();
require_once 'includes/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? 0;

$encomenda = $conn->query("SELECT * FROM encomendas WHERE id = $id")->fetch_assoc();

if (!$encomenda) {
    header('Location: dashboard.php');
    exit();
}

// Atualizar status para 'picking'
if ($encomenda['status'] == 'pendente') {
    $conn->query("UPDATE encomendas SET status = 'picking' WHERE id = $id");
}

$itens = $conn->query("
    SELECT ei.*, p.nome, p.stock
    FROM encomenda_itens ei
    JOIN produtos p ON ei.produto_id = p.id
    WHERE ei.encomenda_id = $id
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $conn->query("UPDATE encomenda_itens SET status_picking = 'coletado' WHERE id = $item_id");
    
    $pendentes = $conn->query("SELECT COUNT(*) as total FROM encomenda_itens WHERE encomenda_id = $id AND status_picking = 'pendente'")->fetch_assoc()['total'];
    
    if ($pendentes == 0) {
        $conn->query("UPDATE encomendas SET status = 'preparacao' WHERE id = $id");
        $conn->query("INSERT INTO preparacoes_picking (encomenda_id, operador_id, status, data_fim) VALUES ($id, {$_SESSION['user_id']}, 'concluida', NOW())");
        header('Location: dashboard.php?msg=completo');
        exit();
    }
    
    header("Location: picking.php?id=$id");
    exit();
}

$total = $conn->query("SELECT COUNT(*) as total FROM encomenda_itens WHERE encomenda_id = $id")->fetch_assoc()['total'];
$coletados = $conn->query("SELECT COUNT(*) as total FROM encomenda_itens WHERE encomenda_id = $id AND status_picking = 'coletado'")->fetch_assoc()['total'];
$progresso = $total > 0 ? round(($coletados / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking #<?php echo $id; ?> | GamiPeças</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            color: #e0e0e0;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            border: 1px solid rgba(242, 178, 31, 0.2);
        }

        .logo h1 {
            font-family: 'Orbitron', monospace;
            font-size: 24px;
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .btn-voltar {
            background: rgba(242, 178, 31, 0.15);
            border: 1px solid rgba(242, 178, 31, 0.3);
            color: #f2b21f;
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-voltar:hover {
            background: rgba(242, 178, 31, 0.3);
            transform: translateY(-2px);
        }

        .progress-card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(242, 178, 31, 0.2);
            text-align: center;
        }

        .progress-number {
            font-size: 48px;
            font-weight: 800;
            font-family: 'Orbitron', monospace;
            color: #f2b21f;
        }

        .progress-bar-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            height: 12px;
            margin: 20px 0;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            height: 100%;
            border-radius: 40px;
            transition: width 0.3s;
        }

        .info-card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(242, 178, 31, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            font-size: 20px;
            color: #f2b21f;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-urgente { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
        .badge-alta { background: rgba(249, 115, 22, 0.2); color: #f97316; }
        .badge-media { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
        .badge-baixa { background: rgba(34, 197, 94, 0.2); color: #22c55e; }

        .items-card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(242, 178, 31, 0.2);
        }

        .items-title {
            font-size: 18px;
            margin-bottom: 20px;
            font-family: 'Orbitron', monospace;
            color: #f2b21f;
            border-bottom: 1px solid rgba(242, 178, 31, 0.2);
            padding-bottom: 10px;
        }

        .item {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-left: 4px solid #f2b21f;
        }

        .item-coletado {
            opacity: 0.6;
            border-left-color: #22c55e;
        }

        .btn-coletar {
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            color: #1a1a2e;
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .badge-coletado {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <h1><i class="fas fa-boxes"></i> GamiPeças</h1>
            </div>
            <a href="dashboard.php" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>

        <div class="progress-card">
            <div class="progress-number"><?php echo $progresso; ?>%</div>
            <div class="progress-bar-container">
                <div class="progress-fill" style="width: <?php echo $progresso; ?>%"></div>
            </div>
            <div style="margin-top: 10px;"><?php echo $coletados; ?> de <?php echo $total; ?> itens coletados</div>
        </div>

        <div class="info-card">
            <div class="info-item"><i class="fas fa-hashtag"></i> <strong>#<?php echo $id; ?></strong></div>
            <div class="info-item"><i class="fas fa-user"></i> <?php echo htmlspecialchars($encomenda['cliente_nome'] ?? 'Cliente'); ?></div>
            <div class="info-item"><i class="fas fa-flag"></i> <span class="badge badge-<?php echo $encomenda['prioridade'] ?? 'baixa'; ?>"><?php echo ucfirst($encomenda['prioridade'] ?? 'Baixa'); ?></span></div>
        </div>

        <div class="items-card">
            <div class="items-title"><i class="fas fa-list"></i> Itens para coletar</div>
            <?php while($item = $itens->fetch_assoc()): ?>
                <div class="item <?php echo $item['status_picking'] == 'coletado' ? 'item-coletado' : ''; ?>">
                    <div>
                        <strong>📦 <?php echo htmlspecialchars($item['nome']); ?></strong><br>
                        <small>Qtd: <?php echo $item['quantidade']; ?> un.</small>
                    </div>
                    <?php if($item['status_picking'] != 'coletado'): ?>
                        <form method="POST">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-coletar"><i class="fas fa-check"></i> Coletar</button>
                        </form>
                    <?php else: ?>
                        <span class="badge-coletado"><i class="fas fa-check"></i> Coletado</span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="footer">
            <p><i class="fas fa-sync-alt"></i> Atualize a página para ver o progresso</p>
        </div>
    </div>
    <script>setTimeout(function() { location.reload(); }, 10000);</script>
</body>
</html>