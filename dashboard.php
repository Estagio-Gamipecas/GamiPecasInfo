<?php
session_start();
require_once 'includes/database.php';

// Verificar se está logado e é admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$sql_picking = "
    SELECT e.*, 
           COUNT(ei.id) as total_itens,
           COALESCE(SUM(CASE WHEN ei.status_picking = 'coletado' THEN 1 ELSE 0 END), 0) as itens_coletados
    FROM encomendas e
    LEFT JOIN encomenda_itens ei ON e.id = ei.encomenda_id
    WHERE e.status IN ('pendente', 'picking')
    GROUP BY e.id
    ORDER BY 
        CASE e.prioridade 
            WHEN 'urgente' THEN 1 
            WHEN 'alta' THEN 2 
            WHEN 'media' THEN 3 
            ELSE 4 
        END ASC
";
$encomendas_picking = $conn->query($sql_picking);

// Preparações prontas - APENAS VISUAL (sem botão)
$sql_preparacoes = "
    SELECT e.id, e.prioridade, e.cliente_nome, pp.data_fim, u.username as operador_nome
    FROM encomendas e
    JOIN preparacoes_picking pp ON e.id = pp.encomenda_id
    LEFT JOIN users u ON pp.operador_id = u.id
    WHERE pp.status = 'concluida' AND e.status != 'expedido'
    ORDER BY pp.data_fim DESC
    LIMIT 10
";
$preparacoes_prontas = $conn->query($sql_preparacoes);

$tem_pickings = ($encomendas_picking && $encomendas_picking->num_rows > 0);
$tem_preparacoes = ($preparacoes_prontas && $preparacoes_prontas->num_rows > 0);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peaking System | Dashboard</title>
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
            max-width: 1400px;
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(0,0,0,0.3);
            padding: 10px 20px;
            border-radius: 50px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn {
            background: rgba(242, 178, 31, 0.15);
            border: 1px solid rgba(242, 178, 31, 0.3);
            color: #f2b21f;
            padding: 8px 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: rgba(242, 178, 31, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(242, 178, 31, 0.2);
            transition: 0.3s;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #f2b21f;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            font-family: 'Orbitron', monospace;
            color: #f2b21f;
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(242, 178, 31, 0.2);
        }

        .card-title {
            font-size: 20px;
            margin-bottom: 20px;
            font-family: 'Orbitron', monospace;
            color: #f2b21f;
            border-bottom: 1px solid rgba(242, 178, 31, 0.2);
            padding-bottom: 10px;
        }

        .picking-list, .preparacao-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .picking-item {
            background: rgba(0,0,0,0.3);
            border-radius: 15px;
            padding: 15px;
            border-left: 4px solid;
            transition: 0.3s;
        }

        .picking-item:hover {
            transform: translateX(5px);
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-urgente { background: rgba(239,68,68,0.2); color: #ef4444; }
        .badge-alta { background: rgba(249,115,22,0.2); color: #f97316; }
        .badge-media { background: rgba(59,130,246,0.2); color: #3b82f6; }
        .badge-baixa { background: rgba(34,197,94,0.2); color: #22c55e; }
        .badge-concluida { background: rgba(34,197,94,0.2); color: #22c55e; }

        .progress-bar-container {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            height: 6px;
            width: 100px;
            overflow: hidden;
        }

        .progress-fill {
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            height: 100%;
        }

        .preparacao-item {
            background: rgba(0,0,0,0.3);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            border-left: 4px solid #22c55e;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: rgba(255,255,255,0.4);
        }

        .footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid rgba(242, 178, 31, 0.1);
            margin-top: 30px;
            color: rgba(255,255,255,0.4);
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .two-columns { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <h1><i class="fas fa-chart-line"></i> PEAKING SYSTEM</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar"><i class="fas fa-user"></i></div>
                <div><strong><?php echo htmlspecialchars($user_data['username'] ?? 'Utilizador'); ?></strong><br><small><?php echo htmlspecialchars($user_data['email'] ?? ''); ?></small></div>
                <button class="logout-btn" onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Sair</button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?php echo $total_users; ?></div><div class="stat-label">Usuários</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $tem_pickings ? $encomendas_picking->num_rows : 0; ?></div><div class="stat-label">Pickings Ativos</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $tem_preparacoes ? $preparacoes_prontas->num_rows : 0; ?></div><div class="stat-label">Prontos para Entregar</div></div>
            <div class="stat-card"><div class="stat-number">0</div><div class="stat-label">Entregues Hoje</div></div>
        </div>

        <div class="two-columns">
            <!-- COLUNA ESQUERDA: ENCOMENDAS EM PICKING (SEM BOTÃO) -->
            <div class="card">
                <div class="card-title"><i class="fas fa-boxes"></i> Encomendas em Picking</div>
                <?php if($tem_pickings): ?>
                    <div class="picking-list">
                        <?php while($row = $encomendas_picking->fetch_assoc()): ?>
                            <?php $progresso = isset($row['total_itens']) && $row['total_itens'] > 0 ? round(($row['itens_coletados'] / $row['total_itens']) * 100) : 0; ?>
                            <div class="picking-item" style="border-left-color: #f2b21f;">
                                <div><i class="fas fa-hashtag"></i> #<?php echo $row['id']; ?> <span class="badge badge-<?php echo $row['prioridade'] ?? 'baixa'; ?>"><?php echo ucfirst($row['prioridade'] ?? 'Normal'); ?></span></div>
                                <div>👤 <?php echo htmlspecialchars($row['cliente_nome'] ?? 'Cliente'); ?></div>
                                <div>📦 <?php echo $row['itens_coletados'] ?? 0; ?>/<?php echo $row['total_itens'] ?? 0; ?> itens</div>
                                <div class="progress-bar-container"><div class="progress-fill" style="width: <?php echo $progresso; ?>%"></div></div>
                                <!-- BOTÃO "REALIZAR PICKING" REMOVIDO - APENAS VISUAL -->
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state"><i class="fas fa-check-circle"></i><p>Nenhuma encomenda em picking</p></div>
                <?php endif; ?>
            </div>

            <!-- COLUNA DIREITA: PREPARAÇÕES PRONTAS (APENAS VISUAL, SEM BOTÃO) -->
            <div class="card">
                <div class="card-title"><i class="fas fa-truck"></i> Prontos para Entrega</div>
                <?php if($tem_preparacoes): ?>
                    <div class="preparacao-list">
                        <?php while($row = $preparacoes_prontas->fetch_assoc()): ?>
                            <div class="preparacao-item">
                                <div><i class="fas fa-hashtag"></i> #<?php echo $row['id']; ?> <span class="badge badge-concluida"><i class="fas fa-check"></i> Pronta</span></div>
                                <div><i class="fas fa-user"></i> <?php echo htmlspecialchars($row['cliente_nome'] ?? 'Cliente'); ?></div>
                                <div><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($row['data_fim'])); ?></div>
                                <div><i class="fas fa-user-check"></i> Operador: <?php echo htmlspecialchars($row['operador_nome'] ?? 'Sistema'); ?></div>
                                <!-- BOTÃO REMOVIDO - APENAS VISUAL -->
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state"><i class="fas fa-inbox"></i><p>Nenhuma preparação pronta</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer"><p><i class="fas fa-sync-alt"></i> Peaking System v2.0 | Atualização a cada 30s</p></div>
    </div>
    <script>setTimeout(function() { location.reload(); }, 30000);</script>
</body>
</html>