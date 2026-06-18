<?php
session_start();

function estaLogado() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_nome']);
}

function verificarLogin() {
    if (!estaLogado()) {
        header('Location: login.php');
        exit();
    }
}

// ============================================
// FUNÇÃO registrarAtividade (apenas UMA vez, aqui)
// ============================================
function registrarAtividade($conn, $acao, $detalhes = null) {
    if (estaLogado()) {
        $user_id = $_SESSION['user_id'];
        $user_nome = $_SESSION['user_nome'];
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Garantir que a tabela existe
        $conn->query("CREATE TABLE IF NOT EXISTS logs_atividades (
            id INT PRIMARY KEY AUTO_INCREMENT,
            utilizador_id INT,
            utilizador_nome VARCHAR(100),
            acao VARCHAR(100),
            detalhes TEXT,
            ip_address VARCHAR(45),
            data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $conn->prepare("INSERT INTO logs_atividades (utilizador_id, utilizador_nome, acao, detalhes, ip_address) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issss", $user_id, $user_nome, $acao, $detalhes, $ip);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>