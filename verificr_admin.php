<?php
// verificar_admin.php
if (!isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] != 'admin') {
    header('Location: login.php');
    exit();
}
?>