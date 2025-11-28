<?php
require_once "../Controlador/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    // Encriptar contraseña
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (usuario, clave) VALUES ('$usuario', '$clave_hash')";

    if ($conexion->query($sql)) {
        echo "<script>alert('Usuario creado correctamente'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error al crear usuario');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang=\"es\">
<head>
    <meta charset=\"UTF-8\">
    <title>Crear Usuario</title>
</head>
<body>
    <h2>Registrar nuevo usuario</h2>
    <form method=\"POST\">
        <label>Usuario:</label><br>
        <input type=\"text\" name=\"usuario\" required><br><br>

        <label>Contraseña:</label><br>
        <input type=\"password\" name=\"clave\" required><br><br>

        <button type=\"submit\">Crear Usuario</button>
    </form>
</body>
</html>
