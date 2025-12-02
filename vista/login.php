<?php
session_start();
include_once("../Controlador/conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitización básica
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    // Consulta preparada
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $fila = $resultado->fetch_assoc();

        // Verificar contraseña
        if (password_verify($password, $fila['contraseña'])) {

            

            // Crear sesión
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['rol'] = $fila['rol'];
            $_SESSION['id'] = $fila['id'];

            // Redirigir
            header("Location: inicio.php");
            exit;

        } else {
            $error = "Contraseña incorrecta";
        }

    } else {
        $error = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="../Estilos/style_registro.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión</h2>

        <a class="link" href="registro.php">Crear cuenta nueva</a>


        <?php if (!empty($error)) : ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</body>
</html>
