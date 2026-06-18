<?php
require_once '../includes/database.php';

// Configuração do e-mail
use PHPMailer\PHPMailer\PHPMailer;
require_once '../vendor/autoload.php'; // Baixe PHPMailer via Composer

function enviarEmail($destino, $assunto, $mensagem) {
    $mail = new PHPMailer(true);
    try {
        // Configuração para Gmail (ou outro SMTP)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'seuemail@gmail.com';
        $mail->Password = 'suasenhaapp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('sistema@peaking.com', 'Sistema Peaking');
        $mail->addAddress($destino);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: " . $mail->ErrorInfo);
        return false;
    }
}

// Buscar todas as regras ativas
$sql = "
    SELECT r.*, p.nome as produto_nome, p.quantidade, p.preco, u.email 
    FROM regras_peaking r
    JOIN produtos p ON r.produto_id = p.id
    CROSS JOIN usuarios u
    LEFT JOIN logs_notificacoes l ON l.produto_id = p.id 
        AND l.usuario_email = u.email 
        AND DATE(l.data_envio) = CURDATE()
    WHERE l.id IS NULL  -- Evita enviar o mesmo alerta várias vezes no mesmo dia
";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $condicaoAtendida = false;
    
    switch($row['condicao']) {
        case 'estoque_minimo':
            if ($row['quantidade'] <= $row['valor_limite']) {
                $condicaoAtendida = true;
                $motivo = "Estoque baixo: {$row['quantidade']} unidades (mínimo: {$row['valor_limite']})";
            }
            break;
        case 'preco_maximo':
            if ($row['preco'] >= $row['valor_limite']) {
                $condicaoAtendida = true;
                $motivo = "Preço alto: R$ {$row['preco']} (limite: R$ {$row['valor_limite']})";
            }
            break;
    }
    
    if ($condicaoAtendida) {
        $assunto = "🔔 Alerta Peaking: {$row['produto_nome']}";
        $mensagem = "Olá!\n\nProduto: {$row['produto_nome']}\nMotivo: $motivo\n\n{$row['mensagem_alerta']}";
        
        if (enviarEmail($row['email'], $assunto, $mensagem)) {
            // Registrar log
            $stmt = $conn->prepare("INSERT INTO logs_notificacoes (usuario_email, produto_id, mensagem_enviada) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $row['email'], $row['produto_id'], $row['mensagem_alerta']);
            $stmt->execute();
            
            echo "E-mail enviado para {$row['email']} sobre {$row['produto_nome']}\n";
        }
    }
}

$conn->close();
?>