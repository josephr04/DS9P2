<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'p2gestiongeneral';

try {
    // Crear conexión PDO
    $conexion = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
    ]));
}

?>
