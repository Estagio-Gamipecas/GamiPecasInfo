<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peaking System | Plataforma de Picking Inteligente</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            color: #fff;
            overflow-x: hidden;
        }

        .grid-bg {
            position: fixed;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(242, 178, 31, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(242, 178, 31, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInDown 1s ease;
        }

        .logo {
            font-size: 60px;
            margin-bottom: 20px;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-family: 'Orbitron', monospace;
            font-size: 48px;
            background: linear-gradient(135deg, #fff, #f2b21f, #ff6b35);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .subtitle {
            font-size: 18px;
            color: rgba(255,255,255,0.7);
            letter-spacing: 1px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .stat-card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(242, 178, 31, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
            border-color: #f2b21f;
            box-shadow: 0 0 30px rgba(242, 178, 31, 0.2);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 800;
            font-family: 'Orbitron', monospace;
            color: #f2b21f;
            margin-bottom: 10px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(242, 178, 31, 0.2);
            transition: 0.3s;
            text-align: center;
        }

        .feature-card:hover {
            background: rgba(242, 178, 31, 0.1);
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 50px;
            margin-bottom: 20px;
            color: #f2b21f;
        }

        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #f2b21f;
        }

        .feature-card p {
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
        }

        .live-section {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 60px;
            border: 1px solid rgba(242, 178, 31, 0.2);
        }

        .section-title {
            text-align: center;
            font-size: 32px;
            margin-bottom: 30px;
            font-family: 'Orbitron', monospace;
            color: #f2b21f;
        }

        .activities {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 3px solid #f2b21f;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: rgba(242, 178, 31, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .activity-text {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
        }

        .activity-status {
            padding: 5px 15px;
            border-radius: 20px;
            background: rgba(242, 178, 31, 0.2);
            color: #f2b21f;
            font-size: 12px;
            font-weight: 600;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 60px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            color: #1a1a2e;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(242, 178, 31, 0.5);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #f2b21f;
            color: #f2b21f;
        }

        .btn-outline:hover {
            background: #f2b21f;
            color: #1a1a2e;
        }

        .footer {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid rgba(242, 178, 31, 0.1);
            color: rgba(255,255,255,0.5);
            font-size: 14px;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 32px;
            }
            
            .stat-number {
                font-size: 36px;
            }
            
            .buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>
    
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-robot"></i>
            </div>
            <h1>PEAKING SYSTEM</h1>
            <p class="subtitle">Plataforma Inteligente de Picking e Gestão de Armazém</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">98.5%</div>
                <div class="stat-label">Precisão nas Separações</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">2.4s</div>
                <div class="stat-label">Tempo Médio por Item</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">15K+</div>
                <div class="stat-label">Pedidos Processados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Monitoramento em Tempo Real</div>
            </div>
        </div>

        <div class="features">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Dashboards em Tempo Real</h3>
                <p>Visualize métricas e KPIs do seu armazém em tempo real com gráficos interativos e alertas automáticos.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-qrcode"></i></div>
                <h3>Leitura por Código de Barras</h3>
                <p>Integração com scanners e dispositivos móveis para separação rápida e precisa de produtos.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-bell"></i></div>
                <h3>Alertas Inteligentes</h3>
                <p>Sistema de notificações automáticas para estoque baixo, atrasos e oportunidades de melhoria.</p>
            </div>
        </div>

        <div class="live-section">
            <h2 class="section-title"><i class="fas fa-bolt"></i> SISTEMA EM AÇÃO</h2>
            <div class="activities" id="activities">
                <div class="activity">
                    <div class="activity-icon">📦</div>
                    <div class="activity-text">
                        <div class="activity-title">Pedido #P1024 sendo separado</div>
                        <div class="activity-time">Há 2 minutos • Operador: João Silva</div>
                    </div>
                    <div class="activity-status">Em andamento</div>
                </div>
                <div class="activity">
                    <div class="activity-icon">✓</div>
                    <div class="activity-text">
                        <div class="activity-title">Entrega #P1022 confirmada</div>
                        <div class="activity-time">Há 5 minutos • Cliente: Maria Santos</div>
                    </div>
                    <div class="activity-status">Concluído</div>
                </div>
                <div class="activity">
                    <div class="activity-icon">⚠️</div>
                    <div class="activity-text">
                        <div class="activity-title">Estoque baixo: Refrigerante X</div>
                        <div class="activity-time">Há 10 minutos • Urgência: Alta</div>
                    </div>
                    <div class="activity-status">Alerta</div>
                </div>
            </div>
        </div>

        <div class="buttons">
            <button class="btn btn-primary" onclick="window.location.href='login.php'">
                <i class="fas fa-sign-in-alt"></i> ACESSAR SISTEMA
            </button>
            <button class="btn btn-outline" onclick="alert('Demo disponível em breve!')">
                <i class="fas fa-play"></i> VER DEMO
            </button>
        </div>

        <div class="footer">
            <p>© 2024 Peaking System - Tecnologia em Picking Inteligente</p>
            <p style="margin-top: 10px; font-size: 12px;">
                <i class="fas fa-shield-alt"></i> Sistema seguro e em conformidade com LGPD
            </p>
        </div>
    </div>

    <script>
        const activitiesList = [
            { icon: "📦", title: "Pedido #P1025 sendo separado", time: "Agora mesmo", operator: "Carlos Lima", status: "Em andamento" },
            { icon: "🚚", title: "Caminhão saiu para entrega", time: "Há 1 minuto", operator: "Rota Norte", status: "Em trânsito" },
            { icon: "✅", title: "Pedido #P1023 finalizado", time: "Há 3 minutos", operator: "Ana Paula", status: "Concluído" },
            { icon: "⚠️", title: "Produto com validade próxima", time: "Há 8 minutos", operator: "Estoque", status: "Atenção" }
        ];

        let activityIndex = 0;

        function addNewActivity() {
            const activity = activitiesList[activityIndex % activitiesList.length];
            const activitiesDiv = document.getElementById('activities');
            const newActivity = document.createElement('div');
            newActivity.className = 'activity';
            newActivity.style.animation = 'slideIn 0.5s ease';
            newActivity.innerHTML = `
                <div class="activity-icon">${activity.icon}</div>
                <div class="activity-text">
                    <div class="activity-title">${activity.title}</div>
                    <div class="activity-time">${activity.time} • Operador: ${activity.operator}</div>
                </div>
                <div class="activity-status">${activity.status}</div>
            `;
            
            activitiesDiv.insertBefore(newActivity, activitiesDiv.firstChild);
            
            if (activitiesDiv.children.length > 5) {
                activitiesDiv.removeChild(activitiesDiv.lastChild);
            }
            
            activityIndex++;
        }

        setInterval(addNewActivity, 10000);
    </script>
</body>
</html>