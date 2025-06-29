<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=nuevoweb', 'root', '');
    echo "Conexión exitosa";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
