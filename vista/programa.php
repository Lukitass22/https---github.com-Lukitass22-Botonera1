<?php
session_start();
include_once("../Controlador/conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Obtener datos del usuario
$usuario = $_SESSION['usuario'];
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$datosUsuario = $resultado->fetch_assoc();
$rol = $datosUsuario['rol'];
$idUsuario = $datosUsuario['id'];

// Obtener id_programa desde GET
if (!isset($_GET['id_programa'])) {
    header("Location: inicio.php");
    exit;
}
$id_programa = (int)$_GET['id_programa'];

// Obtener datos del programa
$sqlPrograma = "SELECT * FROM programas WHERE id = ?";
$stmtPrograma = $conexion->prepare($sqlPrograma);
$stmtPrograma->bind_param("i", $id_programa);
$stmtPrograma->execute();
$resultPrograma = $stmtPrograma->get_result();
$programa = $resultPrograma->fetch_assoc();

// Inicializar mensaje
$mensaje = '';

// Agregar FX (solo jefe)
if ($rol === 'jefe' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fx_nombre'])) {
    $fx_nombre = trim($_POST['fx_nombre']);
    $fx_tipo = $_POST['fx_tipo'];

    if (isset($_FILES['fx_file']) && $_FILES['fx_file']['error'] === 0) {
        $archivo = $_FILES['fx_file'];
        $ext = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . "." . $ext;
        $rutaDestino = "../FX/" . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            $sqlInsert = "INSERT INTO fx (nombre, ruta, tipo, id_programa, id_usuario) VALUES (?, ?, ?, ?, ?)";
            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("sssii", $fx_nombre, $nombreArchivo, $fx_tipo, $id_programa, $idUsuario);
            $stmtInsert->execute();
            $mensaje = "FX agregado correctamente.";
        } else {
            $mensaje = "Error al subir el archivo.";
        }
    } else {
        $mensaje = "No se seleccionó ningún archivo.";
    }
}

// Eliminar FX (solo jefe)
if ($rol === 'jefe' && isset($_POST['eliminar_fx'])) {
    $idEliminar = (int)$_POST['eliminar_fx'];
    $sqlDel = "DELETE FROM fx WHERE id = ?";
    $stmtDel = $conexion->prepare($sqlDel);
    $stmtDel->bind_param("i", $idEliminar);
    $stmtDel->execute();
    $mensaje = "FX eliminado correctamente.";
}

// Obtener FX disponibles para este programa
$sqlFX = "SELECT * FROM fx WHERE id_programa = ? OR tipo = 'institucional'";
$stmtFX = $conexion->prepare($sqlFX);
$stmtFX->bind_param("i", $id_programa);
$stmtFX->execute();
$fxs = $stmtFX->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programa: <?php echo htmlspecialchars($programa['nombre']); ?></title>
    <link rel="stylesheet" href="../Estilos/style_programa.css">
</head>
<body>
    <div class="header">
        <?php if ($rol === 'jefe'): ?>
            <h1><a href="editar_programa.php?id_programa=<?php echo $id_programa; ?>" class="program-link"><?php echo htmlspecialchars($programa['nombre']); ?></a></h1>
        <?php else: ?>
            <h1><?php echo htmlspecialchars($programa['nombre']); ?></h1>
        <?php endif; ?>
        <a href="inicio.php" class="back-btn">← Volver</a>
    </div>

    <div class="content">
        <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

        <h2>FX disponibles</h2>
        <?php if ($fxs->num_rows > 0): ?>
            <div class="fx-botonera">
                <?php while ($row = $fxs->fetch_assoc()): ?>
                    <div class="fx-item">
                        <button class="fx-boton" onclick="document.getElementById('audio<?php echo $row['id']; ?>').play()">
                            <?php echo htmlspecialchars($row['nombre']); ?>
                        </button>
                        <audio id="audio<?php echo $row['id']; ?>" src="../FX/<?php echo htmlspecialchars($row['ruta']); ?>"></audio>

                        <?php if ($rol === 'jefe'): ?>
                            <form method="POST" class="form-eliminar" onsubmit="return confirm('¿Seguro que querés eliminar este FX?')">
                                <input type="hidden" name="eliminar_fx" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-eliminar-fx">×</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No hay FX aún.</p>
        <?php endif; ?>

        <?php if ($rol === 'jefe'): ?>
            <h2>Agregar nuevo FX</h2>
            <form method="POST" enctype="multipart/form-data" class="form-fx">
                <input type="text" name="fx_nombre" class="input-form" placeholder="Nombre del FX" required>
                <select name="fx_tipo" class="input-form" required>
                    <option value="personal">Personal</option>
                    <option value="programa">Programa</option>
                    <option value="institucional">Institucional</option>
                </select>
                <div class="file-input-container">
                    <input type="file" name="fx_file" id="fx_file" accept=".mp3,.wav" required>
                    <label for="fx_file" class="file-input-label">Seleccionar archivo</label>
                </div>
                <button type="submit" class="btn-form">Agregar FX</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
