<?php
require_once 'includes/database.php';

echo "<h1>Base de Dados: estagio</h1>";
echo "<h2>Tabelas encontradas:</h2>";

$tabelas = $conn->query("SHOW TABLES");
if ($tabelas->num_rows == 0) {
    echo "<p style='color:red'>Nenhuma tabela encontrada na base de dados 'estagio'!</p>";
} else {
    echo "<ul>";
    while($row = $tabelas->fetch_array()) {
        echo "<li><strong>" . $row[0] . "</strong>";
        
        // Mostrar colunas de cada tabela
        $colunas = $conn->query("SHOW COLUMNS FROM " . $row[0]);
        echo "<ul>";
        while($col = $colunas->fetch_assoc()) {
            echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
        }
        echo "</ul>";
        echo "</li>";
    }
    echo "</ul>";
}
?>