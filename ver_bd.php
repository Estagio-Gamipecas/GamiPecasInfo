<?php
require_once 'includes/database.php';

echo "<h1>🔍 Diagnóstico da Base de Dados: estagio</h1>";

// 1. Verificar conexão
if ($conn->connect_error) {
    echo "<p style='color:red'>❌ Erro na conexão: " . $conn->connect_error . "</p>";
    exit();
}
echo "<p style='color:green'>✅ Conexão com a base de dados 'estagio' bem sucedida!</p>";

// 2. Listar todas as tabelas
$tabelas = $conn->query("SHOW TABLES");
echo "<h2>📋 Tabelas encontradas na base 'estagio':</h2>";

if ($tabelas->num_rows == 0) {
    echo "<p style='color:red'>⚠️ Nenhuma tabela encontrada! A base de dados 'estagio' está vazia.</p>";
} else {
    echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background:#333; color:white;'><th>Tabela</th><th>Colunas</th><th>Registos</th></tr>";
    
    while($row = $tabelas->fetch_array()) {
        $tabela_nome = $row[0];
        
        // Contar registos
        $count = $conn->query("SELECT COUNT(*) as total FROM $tabela_nome");
        $total_registos = $count->fetch_assoc()['total'];
        
        // Buscar colunas
        $colunas = $conn->query("SHOW COLUMNS FROM $tabela_nome");
        $colunas_lista = [];
        while($col = $colunas->fetch_assoc()) {
            $colunas_lista[] = $col['Field'] . " (" . $col['Type'] . ")";
        }
        
        echo "<tr>";
        echo "<td style='background:#f0f0f0; font-weight:bold;'>$tabela_nome</td>";
        echo "<td>" . implode("<br>", $colunas_lista) . "</td>";
        echo "<td style='text-align:center'>$total_registos</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Sugestões
echo "<h2>💡 Sugestões:</h2>";
echo "<ul>";
echo "<li>Se não tens tabelas, precisas <strong>criar as tabelas necessárias</strong> para o sistema de peaking</li>";
echo "<li>Se tens tabelas com nomes diferentes, <strong>adapta os nomes nos códigos</strong></li>";
echo "<li>Se quiseres usar a base de dados 'peaking_system' em vez de 'estagio', muda no database.php</li>";
echo "</ul>";
?>