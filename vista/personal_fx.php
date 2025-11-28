<?php
session_start();
include_once("../Controlador/conexion.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['usuario'];
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$datosUsuario = $resultado->fetch_assoc();
$idUsuario = $datosUsuario['id'];

// Mensaje
$mensaje = "";

// AGREGAR FX PERSONAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fx_nombre'])) {
    $fx_nombre = trim($_POST['fx_nombre']);
    $fx_descripcion = trim($_POST['fx_descripcion']);

    if (isset($_FILES['fx_file']) && $_FILES['fx_file']['error'] === 0) {
        $archivo = $_FILES['fx_file'];
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . "." . $ext;
        $rutaDestino = "../FX/" . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
$sqlInsert = "INSERT INTO fx_personales (id_usuario, nombre, descripcion, archivo) VALUES (?, ?, ?, ?)";
$stmtInsert = $conexion->prepare($sqlInsert);
            
$stmtInsert->bind_param("isss", $idUsuario, $fx_nombre, $fx_descripcion, $nombreArchivo);
            $stmtInsert->execute();

            $mensaje = "FX agregado correctamente.";
        } else {
            $mensaje = "Error al subir el archivo.";
        }
    } else {
        $mensaje = "No se seleccionó ningún archivo.";
    }
}

// ELIMINAR FX
if (isset($_GET['eliminar'])) {
    $idEliminar = (int)$_GET['eliminar'];
    $sqlDel = "DELETE FROM fx_personales WHERE id = ? AND id_usuario = ?";
    $stmtDel = $conexion->prepare($sqlDel);
    $stmtDel->bind_param("ii", $idEliminar, $idUsuario);
    $stmtDel->execute();
    $mensaje = "FX eliminado correctamente.";
}

// OBTENER FX PERSONALES
$sqlFX = "SELECT * FROM fx_personales WHERE id_usuario = ?";
$stmtFX = $conexion->prepare($sqlFX);
$stmtFX->bind_param("i", $idUsuario);
$stmtFX->execute();
$fxs = $stmtFX->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FX Personales</title>
    <link rel="stylesheet" href="../Estilos/style_personal_fx.css">
</head>
<body>
    <div class="header">
        <h1>FX Personales</h1>
        <a href="inicio.php" class="back-btn">← Volver</a>
    </div>

    <div class="content">
        <?php if ($mensaje): ?>
            <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <!-- FORMULARIO PARA AGREGAR FX -->
        <h2>Agregar nuevo FX</h2>
        <form method="POST" enctype="multipart/form-data" class="form-fx">
            <input type="text" name="fx_nombre" class="input-form" placeholder="Nombre del FX" required>
            <input type="text" name="fx_descripcion" class="input-form" placeholder="Descripción (opcional)">
            <div class="file-input-container">
                <input type="file" name="fx_file" id="fx_file" accept=".mp3,.wav" required>
                <label for="fx_file" class="file-input-label">Seleccionar archivo</label>
            </div>
            <button type="submit" class="btn-form">Agregar FX</button>
        </form>

        <!-- BOTONERA DE FX -->
        <h2>Tus FX</h2>
        <?php if ($fxs->num_rows > 0): ?>
            <div class="fx-botonera">
                <?php while ($row = $fxs->fetch_assoc()): ?>
                    <div class="fx-item">
                        <button class="fx-boton" onclick="document.getElementById('audio<?php echo $row['id']; ?>').play()">
                            <?php echo htmlspecialchars($row['nombre']); ?>
                        </button>
<audio id="audio<?php echo $row['id']; ?>" src="../FX/<?php echo htmlspecialchars($row['archivo']); ?>"></audio>

                        <div class="fx-opciones">
                            <a href="personal_fx.php?eliminar=<?php echo $row['id']; ?>" class="btn-eliminar">Eliminar</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No tenés FX agregados todavía.</p>
        <?php endif; ?>
    </div>
</body>
</html>
