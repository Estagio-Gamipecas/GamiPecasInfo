<?php
session_start();
require_once 'includes/database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    
    // Buscar usuário por email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Comparação direta (sem encriptação)
        if ($senha === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_tipo'] = $user['tipo'] ?? 'cliente';
            
            // Verificar se é admin
            if ($_SESSION['user_tipo'] === 'admin') {
                header('Location: dashboard.php');
                exit();
            } else {
                $erro = 'Acesso negado. Apenas administradores podem entrar.';
                session_destroy();
            }
        } else {
            $erro = 'Email ou senha incorretos!';
        }
    } else {
        $erro = 'Email ou senha incorretos!';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - Peaking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(242, 178, 31, 0.2);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Orbitron', monospace;
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .login-sub {
            text-align: center;
            margin-bottom: 20px;
            color: #f2b21f;
            font-size: 12px;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(242, 178, 31, 0.2);
            border-radius: 10px;
            color: white;
            font-size: 14px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f2b21f, #ff6b35);
            border: none;
            border-radius: 10px;
            color: #1a1a2e;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
        }
        .erro {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            color: #ef4444;
            font-size: 13px;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1><i class="fas fa-chart-line"></i> PEAKING SYSTEM</h1>
        <div class="login-sub">Área Restrita - Administradores</div>
        
        <?php if($erro): ?>
            <div class="erro"><i class="fas fa-exclamation-triangle"></i> <?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required autofocus>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Entrar</button>
        </form>
        
        <div class="info">
            <i class="fas fa-shield-alt"></i> Acesso exclusivo para administradores
        </div>
    </div>
</body>
</html>