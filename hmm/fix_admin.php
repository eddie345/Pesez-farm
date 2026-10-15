<?php
require_once 'config.php';
$hash = password_hash('admin123', PASSWORD_BCRYPT);
$pdo->exec("UPDATE users SET password_hash = '$hash' WHERE username = 'admin'");
echo $hash;
