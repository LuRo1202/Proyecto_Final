<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

try {
    $query = "SELECT 
                r.responsable_id, 
                r.nombre, 
                r.apellido_paterno, 
                r.apellido_materno, 
                r.cargo, 
                r.departamento, 
                r.telefono, 
                r.activo,
                u.correo
              FROM responsables r
              LEFT JOIN usuarios u ON r.usuario_id = u.usuario_id
              ORDER BY r.apellido_paterno, r.apellido_materno, r.nombre";
    
    $stmt = $conn->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Error: " . $e->getMessage()]);
}
?>