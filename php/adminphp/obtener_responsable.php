<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit;
}
$id = $_GET['id'];

try {
    $query = "SELECT r.*, u.correo FROM responsables r 
              JOIN usuarios u ON r.usuario_id = u.usuario_id WHERE r.responsable_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($responsable) {
        echo json_encode(['success' => true, 'data' => $responsable]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Responsable no encontrado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>