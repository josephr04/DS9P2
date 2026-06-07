<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php'; 

if (!empty($_GET['codigo_provincia'])) {
    $codigo_provincia = trim($_GET['codigo_provincia']);

    try {
        $stmt = $conexion->prepare("SELECT codigo_distrito, nombre_distrito 
                                    FROM distrito 
                                    WHERE codigo_provincia = :codigo_provincia 
                                    ORDER BY nombre_distrito ASC");
        $stmt->execute([':codigo_provincia' => $codigo_provincia]);
        $distritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($distritos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}