<?php
require_once __DIR__ . '/../conexion.php';

echo "<h1>Recalculando Horas (Modo Depuración)</h1>";
echo "<pre>";
echo "Iniciando proceso a las " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Obtener todos los estudiantes activos
    $stmt_estudiantes = $conn->query("SELECT estudiante_id, nombre, apellido_paterno FROM estudiantes WHERE activo = 1");
    $estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$estudiantes) {
        die("No se encontraron estudiantes activos para procesar.");
    }
    echo "Se encontraron " . count($estudiantes) . " estudiantes.\n\n";

    // 2. Iterar por cada estudiante y actualizarlo individualmente
    foreach ($estudiantes as $estudiante) {
        $id = $estudiante['estudiante_id'];
        $nombre_completo = htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido_paterno']);
        
        echo "Procesando: $nombre_completo (ID: $id)\n";

        // 3. Calcular el total de horas aprobadas para este estudiante
        $stmt_horas = $conn->prepare("
            SELECT COALESCE(SUM(horas_acumuladas), 0) as total 
            FROM registroshoras 
            WHERE estudiante_id = :id AND estado = 'aprobado'
        ");
        $stmt_horas->execute([':id' => $id]);
        $total_horas = $stmt_horas->fetchColumn();

        echo " - Horas calculadas (aprobadas): $total_horas\n";

        // 4. Actualizar la tabla estudiantes con el nuevo total
        $update_stmt = $conn->prepare("
            UPDATE estudiantes SET horas_completadas = :horas WHERE estudiante_id = :id
        ");
        $update_stmt->execute([':horas' => $total_horas, ':id' => $id]);
        
        // 5. Verificar si la actualización afectó a alguna fila
        $filas_afectadas = $update_stmt->rowCount();
        if ($filas_afectadas > 0) {
            echo " - ÉXITO: Se actualizaron las horas en la tabla 'estudiantes'.\n\n";
        } else {
            echo " - ADVERTENCIA: La consulta de actualización no afectó ninguna fila (quizás el valor ya era el mismo).\n\n";
        }
    }

    echo "--------------------------------------------------\n";
    echo "PROCESO DE RECALCULO COMPLETADO.\n";
    echo "Verifica el panel de Admin para ver los resultados.\n";

} catch (Exception $e) {
    echo "--------------------------------------------------\n";
    echo "ERROR CRÍTICO DURANTE LA EJECUCIÓN: " . $e->getMessage() . "\n";
    echo "En el archivo: " . $e->getFile() . " en la línea " . $e->getLine() . "\n";
}

echo "</pre>";