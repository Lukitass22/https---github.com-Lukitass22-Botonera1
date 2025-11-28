<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

require "../Controlador/conexion.php";

$idUsuario = $_SESSION['id'];
$usuarioActual = $_SESSION['usuario'];

// ============================
// HANDLER: Actualizar perfil
// ============================

if (isset($_POST['actualizar_perfil'])) {
    $nuevoUsuario = trim($_POST['nuevo_usuario']);
    $nuevaPassword = trim($_POST['nueva_password']);

    if ($nuevoUsuario !== "") {

        // Si escribió una nueva contraseña
        if ($nuevaPassword !== "") {
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

            $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, contraseña=? WHERE id=?");
            $stmt->bind_param("ssi", $nuevoUsuario, $hash, $idUsuario);

        } else {
            // Cambia solo usuario
            $stmt = $conexion->prepare("UPDATE usuarios SET usuario=? WHERE id=?");
            $stmt->bind_param("si", $nuevoUsuario, $idUsuario);
        }

        $mensaje = $stmt->execute()
            ? "Perfil actualizado correctamente."
            : "Error al actualizar el perfil.";

        // Actualiza datos en sesión
        $_SESSION['usuario'] = $nuevoUsuario;
    }
}

// Obtener info actualizada
$consulta = $conexion->prepare("SELECT usuario FROM usuarios WHERE id=? LIMIT 1");
$consulta->bind_param("i", $idUsuario);
$consulta->execute();
$datos = $consulta->get_result()->fetch_assoc();
$usuarioActual = $datos['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../Estilos/style_inicio.css"> 
</head>
<body>

<div class="header">
    <h1>Botonera</h1>

    <div class="user-info">
        <p><?php echo htmlspecialchars($usuarioActual); ?></p>

        <a href="inicio.php" class="logout-btn" style="margin-right: 10px;">
            Volver
        </a>

        <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
    </div>
</div>

<div class="content">

    <h2>Editar Perfil</h2>

    <?php if (isset($mensaje)): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <form method="POST" class="form-programa">

        <label>Nombre de usuario</label>
        <input type="text" name="nuevo_usuario" class="input-form"
               value="<?php echo htmlspecialchars($usuarioActual); ?>" required>

        <label>Nueva contraseña (opcional)</label>
        <input type="password" name="nueva_password" class="input-form"
               placeholder="Dejar vacío para mantener la actual">

        <button type="submit" name="actualizar_perfil" class="btn-form">
            Guardar cambios
        </button>

    </form>

</div>

</body>
</html>
