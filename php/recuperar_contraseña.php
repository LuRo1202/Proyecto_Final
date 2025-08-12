<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Conexión a la Base de Datos ---
$servername = "localhost";
$username = "root";
$password = "";
$database = "servicio_social";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $conn->real_escape_string($_POST['correo']);

    $sql = "SELECT correo FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $sqlInsert = "INSERT INTO recuperacion (correo, token, expira) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("sss", $correo, $token, $expira);

        if ($stmtInsert->execute()) {
            // Mensaje de éxito
            $mensaje = "<div class='alert alert-success-custom text-center'>
                            <h6>Correo enviado con éxito</h6>
                            <p class='mb-0 small'>Hemos enviado un enlace de recuperación a: <strong>$correo</strong></p>
                        </div>";
        } else {
            // Mensaje de error general
            $mensaje = "<div class='alert alert-danger-custom text-center'>Error al generar el enlace de recuperación.</div>";
        }
        $stmtInsert->close();
    } else {
        // Mensaje de correo no encontrado
        $mensaje = "<div class='alert alert-warning-custom text-center'>Este correo no está registrado en nuestra base de datos.</div>";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Recuperación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #8E7CC3;
            --primary-medium: #AEA0D8;
            --pastel-background: #F8F5FB;
            --text-dark: #343a40;
            --text-light: #FFFFFF;
            --border-light: #dee2e6;
            --card-success-bg: #198754;
            --card-danger-bg: #dc3545;
        }

        body {
            background-color: var(--pastel-background);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .card {
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            border-radius: 12px;
            border: none;
        }

        .card-body h3 {
            color: var(--primary-dark);
            font-weight: 700;
        }

        .btn-primary {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            border-radius: 8px;
            font-weight: 600;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-medium);
            border-color: var(--primary-medium);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-primary:focus {
            /* border-color: var(--primary-medium); */
            box-shadow: none; /* Eliminamos la sombra azul */
        }

        .text-decoration-none {
            color: var(--primary-dark);
        }

        /* Estilos para las alertas de mensaje */
        .alert {
            border-radius: 8px;
        }
        .alert-success-custom {
            color: var(--card-success-bg);
            background-color: #d1e7dd;
            border-color: #badbcc;
        }
        .alert-danger-custom {
            color: var(--card-danger-bg);
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }
        .alert-warning-custom {
            color: #664d03;
            background-color: #fff3cd;
            border-color: #ffecb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="../imagenes/data.png" alt="Logo" class="img-fluid" style="max-height: 70px;">
                        </div>

                        <h3 class="text-center mb-4 fs-5">Estado de la Solicitud</h3>

                        <?php if (!empty($mensaje)) echo "<div class='mb-3'>$mensaje</div>"; ?>

                        <div class="d-grid gap-2 mt-4">
                            <a href="../index.html" class="btn btn-primary">Volver al Inicio</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>