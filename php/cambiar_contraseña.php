<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "servicio_social";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$etapa = "validarCorreo"; 
$mensaje = ""; 
$correoValidado = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["etapa"]) && $_POST["etapa"] === "validarCorreo") {
    $correo = $conn->real_escape_string($_POST["correo"]);
    $sql = "SELECT correo FROM usuarios WHERE correo = '$correo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $etapa = "validarContraseñaActual";
        $correoValidado = $correo;
    } else {
        $mensaje = "<p class='text-danger'>El correo electrónico no está registrado.</p>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["etapa"]) && $_POST["etapa"] === "validarContraseñaActual") {
    $correoValidado = $conn->real_escape_string($_POST["correo_validado"]);
    $contraseñaActual = $conn->real_escape_string($_POST["contraseña_actual"]);
    $sql = "SELECT contrasena FROM usuarios WHERE correo = '$correoValidado'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $fila = $result->fetch_assoc();
        $contrasenaGuardada = $fila["contrasena"];

        if (password_verify($contraseñaActual, $contrasenaGuardada)) {
            $etapa = "cambiarContraseña";
        } else {
            $mensaje = "<p class='text-danger'>La contraseña actual es incorrecta.</p>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["etapa"]) && $_POST["etapa"] === "cambiarContraseña") {
    $correoValidado = $conn->real_escape_string($_POST["correo_validado"]);
    $nuevaContrasena = $conn->real_escape_string($_POST["nueva_contrasena"]);
    $confirmarContrasena = $conn->real_escape_string($_POST["confirmar_contrasena"]);

    if ($nuevaContrasena === $confirmarContrasena) {
        $hashContrasena = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $sqlUpdate = "UPDATE usuarios SET contrasena = '$hashContrasena' WHERE correo = '$correoValidado'";
        
        if ($conn->query($sqlUpdate) === TRUE) {
            $etapa = "exito";
            $mensaje = "<h3 class='text-success'></h3>";
        } else {
            $mensaje = "<p class='text-danger'>Error al actualizar la contraseña.</p>";
        }
    } else {
        $mensaje = "<p class='text-danger'>Las contraseñas no coinciden.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h3>Cambio de contraseña</h3>
                        <?= $mensaje ?>

                        <?php if ($etapa === "validarCorreo") : ?>
                            <form action="cambiar_contraseña.php" method="POST">
                                <input type="hidden" name="etapa" value="validarCorreo">
                                <div class="mb-3">
                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="correo" name="correo" required>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Validar correo</button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if ($etapa === "validarContraseñaActual") : ?>
                            <form action="cambiar_contraseña.php" method="POST">
                                <input type="hidden" name="etapa" value="validarContraseñaActual">
                                <input type="hidden" name="correo_validado" value="<?= htmlspecialchars($correoValidado) ?>">
                                <div class="mb-3">
                                    <label for="contraseña_actual" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="contraseña_actual" name="contraseña_actual" required>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Validar Contraseña</button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if ($etapa === "cambiarContraseña") : ?>
                            <form action="cambiar_contraseña.php" method="POST">
                                <input type="hidden" name="etapa" value="cambiarContraseña">
                                <input type="hidden" name="correo_validado" value="<?= htmlspecialchars($correoValidado) ?>">
                                <div class="mb-3">
                                    <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if ($etapa === "exito") : ?>
                            <div id="mensajeExito">
                                <h3 class="text-black">Contraseña actualizada correctamente</h3>
                                <div class="mt-3">
                                    <a href="../index.html" class="btn btn-primary">Volver al inicio</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>