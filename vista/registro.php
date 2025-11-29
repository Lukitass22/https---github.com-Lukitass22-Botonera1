<?php
ob_start();  // <-- ESTO SOLUCIONA EL PROBLEMA

session_start();
include_once("../Controlador/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty($_POST["usuario"]) || empty($_POST["contrasenia"]) || empty($_POST["nombre"])) {
        $mensaje = "Completa todos los campos.";
    } else {

        $nombre = $_POST["nombre"];
        $usuario = $_POST["usuario"];
        $contrasenia = password_hash($_POST["contrasenia"], PASSWORD_DEFAULT);

        // Verificar si el usuario ya existe
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $mensaje = "El usuario ya existe.";
        } else {

            // Insertar usuario nuevo
            $sql = "INSERT INTO usuarios (nombre, usuario, contraseña) VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sss", $nombre, $usuario, $contrasenia);

            if ($stmt->execute()) {

                // Iniciar sesión automática
                $_SESSION['usuario'] = $usuario;

                // REDIRECT FUNCIONAL
                header("Location: inicio.php");
                exit;

            } else {
                $mensaje = "Error al registrar usuario.";
            }
        }
    }
}

ob_end_flush(); // <-- IMPORTANTE
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>

<h2>Crear usuario</h2>

<?php if (isset($mensaje)): ?>
    <p><?php echo $mensaje; ?></p>
<?php endif; ?>

<form action="" method="POST">
    <label>Nombre completo</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Usuario</label><br>
    <input type="text" name="usuario" required><br><br>

    <label>Contraseña</label><br>
    <input type="password" name="contrasenia" required><br><br>

    <button type="submit">Registrarse</button>
</form>

<br>
<a href="login.php">Volver al login</a>

</body>
</html>
