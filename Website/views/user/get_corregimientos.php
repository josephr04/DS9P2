<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../config/conexion.php'; 

if (!empty($_GET['codigo_distrito'])) {
    $codigo_distrito = trim($_GET['codigo_distrito']);

    try {
        $stmt = $conexion->prepare("SELECT codigo_corregimiento, nombre_corregimiento 
                                    FROM corregimiento 
                                    WHERE codigo_distrito = :codigo_distrito 
                                    ORDER BY nombre_corregimiento ASC");
        $stmt->execute([':codigo_distrito' => $codigo_distrito]);
        $corregimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($corregimientos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([]);
}