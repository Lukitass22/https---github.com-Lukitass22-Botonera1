<?php
session_start();
include_once("../Controlador/conexion.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit("Acceso denegado. No hay sesión iniciada.");
}



$usuario = $_SESSION['usuario'];

// Obtener datos del usuario logueado
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$datosUsuario = $stmt->get_result()->fetch_assoc();

$rol = $datosUsuario['rol'];
$idUsuario = $datosUsuario['id'];

// Inicializar mensajes
$mensaje = "";

// CRUD de programas (solo jefe)
if ($rol === 'jefe') {
    // Crear programa
    if (isset($_POST['crear_programa'])) {
        $nombre = trim($_POST['programa_nombre']);
        $descripcion = trim($_POST['programa_descripcion']);
        $stmt = $conexion->prepare("INSERT INTO programas (nombre, descripcion) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $descripcion);
        if ($stmt->execute()) {
            $mensaje = "Programa creado correctamente.";
        } else {
            $mensaje = "Error al crear el programa.";
        }
    }

    // Editar programa
    if (isset($_POST['editar_programa'])) {
        $id = $_POST['programa_id'];
        $nombre = trim($_POST['programa_nombre']);
        $descripcion = trim($_POST['programa_descripcion']);
        $stmt = $conexion->prepare("UPDATE programas SET nombre=?, descripcion=? WHERE id=?");
        $stmt->bind_param("ssi", $nombre, $descripcion, $id);
        if ($stmt->execute()) {
            $mensaje = "Programa actualizado correctamente.";
        } else {
            $mensaje = "Error al actualizar el programa.";
        }
    }

    // Eliminar programa
    if (isset($_POST['eliminar_programa'])) {
        $id = $_POST['programa_id'];
        $stmt = $conexion->prepare("DELETE FROM programas WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $mensaje = "Programa eliminado correctamente.";
        } else {
            $mensaje = "Error al eliminar el programa.";
        }
    }

    // Asignar programa a operador
    if (isset($_POST['asignar_programa'])) {
        $id_programa = $_POST['id_programa'];
        $id_operador = $_POST['id_operador'];
        // Verificar si ya está asignado
        $stmt = $conexion->prepare("SELECT * FROM asignaciones WHERE id_usuario=? AND id_programa=?");
        $stmt->bind_param("ii", $id_operador, $id_programa);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt = $conexion->prepare("INSERT INTO asignaciones (id_usuario, id_programa) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_operador, $id_programa);
            if ($stmt->execute()) {
                $mensaje = "Programa asignado correctamente.";
            } else {
                $mensaje = "Error al asignar programa.";
            }
        } else {
            $mensaje = "El programa ya está asignado a este operador.";
        }
    }

    // Eliminar asignación
    if (isset($_POST['eliminar_asignacion'])) {
        $id_asignacion = $_POST['asignacion_id'];
        $stmt = $conexion->prepare("DELETE FROM asignaciones WHERE id=?");
        $stmt->bind_param("i", $id_asignacion);
        if ($stmt->execute()) {
            $mensaje = "Asignación eliminada correctamente.";
        } else {
            $mensaje = "Error al eliminar asignación.";
        }
    }
}

// Obtener programas para mostrar
$sqlProgramas = "SELECT * FROM programas";
$programas = $conexion->query($sqlProgramas);

// Obtener operadores para asignaciones
$operadores = $conexion->query("SELECT * FROM usuarios WHERE rol='operador'");

// Obtener asignaciones actuales
$asignaciones = $conexion->query("
    SELECT a.id as asignacion_id, u.usuario, p.nombre as programa_nombre 
    FROM asignaciones a
    INNER JOIN usuarios u ON a.id_usuario = u.id
    INNER JOIN programas p ON a.id_programa = p.id
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Botonera</title>
    <link rel="stylesheet" href="../Estilos/style_inicio.css">
</head>
<body>
    <div class="header">
        <h1>Panel Principal</h1>
            <p>Bienvenido, <span><?php echo htmlspecialchars($usuario); ?></span></p>
            <p>Rol: <strong><?php echo htmlspecialchars($rol); ?></strong></p>
            <div class="user-info">
            <a href="perfil.php" class="logout-btn" style="margin-right: 10px; background:#e02f2f;">
    Editar Perfil
</a>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </div>
    </div>

    <div class="content">
        <?php if ($mensaje) echo "<p class='mensaje'>$mensaje</p>"; ?>

        <?php if ($rol === 'jefe'): ?>

<?php
    // ============================
    // CRUD HANDLERS (solo jefe)
    // ============================

    // Crear usuario
    if (isset($_POST['crear_usuario'])) {
        $usuarioNuevo = trim($_POST['nuevo_usuario']);
        $passwordPlano = $_POST['nuevo_password'];
        $rolNuevo = $_POST['nuevo_rol'];

        $passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

        // nombre queda vacío (tu sistema no lo usa)
        $stmt = $conexion->prepare("
            INSERT INTO usuarios (nombre, usuario, contraseña, rol)
            VALUES ('', ?, ?, ?)
        ");
        $stmt->bind_param("sss", $usuarioNuevo, $passwordHash, $rolNuevo);

        $mensaje = $stmt->execute()
            ? "Usuario creado correctamente."
            : "Error al crear usuario.";
    }

    // Editar usuario
    if (isset($_POST['editar_usuario'])) {
        $id = $_POST['usuario_id'];
        $usuarioNombre = trim($_POST['usuario_nombre']);
        $usuarioRol = $_POST['usuario_rol'];

        $stmt = $conexion->prepare("UPDATE usuarios SET usuario=?, rol=? WHERE id=?");
        $stmt->bind_param("ssi", $usuarioNombre, $usuarioRol, $id);

        $mensaje = $stmt->execute()
            ? "Usuario actualizado correctamente."
            : "Error al actualizar usuario.";
    }

    // Eliminar usuario
    if (isset($_POST['eliminar_usuario'])) {
        $id = $_POST['usuario_id'];

        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $id);

        $mensaje = $stmt->execute()
            ? "Usuario eliminado correctamente."
            : "Error al eliminar usuario.";
    }

    // ============================
    // Obtener usuarios
    // ============================
    $usuarios = $conexion->query("SELECT * FROM usuarios WHERE id != $idUsuario");
?>

<h2>Gestión de Usuarios</h2>

<?php if(isset($mensaje)) echo "<p style='color:lightgreen;'>$mensaje</p>"; ?>

<table>
    <tr>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Acciones</th>
    </tr>

    <?php while ($u = $usuarios->fetch_assoc()): ?>
        <tr>
            <form method="POST" class="form-programa">

                <td>
                    <input type="text" name="usuario_nombre" class="input-form"
                           value="<?php echo htmlspecialchars($u['usuario']); ?>">
                </td>

                <td>
                    <select name="usuario_rol" class="select-form">
                        <option value="jefe" <?php if($u['rol']=='jefe') echo 'selected'; ?>>jefe</option>
                        <option value="operador" <?php if($u['rol']=='operador') echo 'selected'; ?>>operador</option>
                    </select>
                </td>

                <td>
                    <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">

                    <button type="submit" name="editar_usuario" class="btn-form">Aplicar</button>

                    <button type="submit" name="eliminar_usuario" class="btn-form"
                            onclick="return confirm('¿Eliminar este usuario?')">
                        Eliminar
                    </button>
                </td>
            </form>
        </tr>
    <?php endwhile; ?>
</table>
<h3>Crear nuevo usuario</h3>

<form method="POST" class="form-programa">
    <input type="text" name="nuevo_usuario" placeholder="Nombre de usuario" class="input-form" required>

    <input type="password" name="nuevo_password" placeholder="Contraseña" class="input-form" required>

    <select name="nuevo_rol" class="select-form">
        <option value="operador">operador</option>
        <option value="jefe">jefe</option>
    </select>

    <button type="submit" name="crear_usuario" class="btn-form">Crear usuario</button>
</form>

<?php endif; ?>


        <?php if ($rol === 'jefe'): ?>
            <h2>Gestión de Programas</h2>

            <table>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($row = $programas->fetch_assoc()): ?>
                    <tr>
                        <form method="POST" class="form-programa">
                            <td><input type="text" name="programa_nombre" class="input-form" value="<?php echo htmlspecialchars($row['nombre']); ?>"></td>
                            <td><input type="text" name="programa_descripcion" class="input-form" value="<?php echo htmlspecialchars($row['descripcion']); ?>"></td>
                            <td>
                                <input type="hidden" name="programa_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="editar_programa" class="btn-form">Aplicar</button>
                                <button type="submit" name="eliminar_programa" class="btn-form" onclick="return confirm('¿Eliminar este programa?')">Eliminar</button>
                                <a href="programa.php?id_programa=<?php echo $row['id']; ?>" class="btn-editar-fx">Editar FX</a>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </table>

            <h3>Crear nuevo programa</h3>
            <form method="POST" class="form-programa">
                <input type="text" name="programa_nombre" placeholder="Nombre del programa" class="input-form" required>
                <input type="text" name="programa_descripcion" placeholder="Descripción" class="input-form">
                <button type="submit" name="crear_programa" class="btn-form">Crear</button>
            </form>

            <h3>Asignar programa a operador</h3>
            <form method="POST" class="form-programa">
                <select name="id_programa" class="select-form" required>
                    <option value="">Selecciona programa</option>
                    <?php 
                    $programas2 = $conexion->query($sqlProgramas);
                    while ($p = $programas2->fetch_assoc()): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>

                <select name="id_operador" class="select-form" required>
                    <option value="">Selecciona operador</option>
                    <?php while ($op = $operadores->fetch_assoc()): ?>
                        <option value="<?php echo $op['id']; ?>"><?php echo htmlspecialchars($op['usuario']); ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" name="asignar_programa" class="btn-form">Asignar</button>
            </form>

            <h3>Asignaciones actuales</h3>
            <table>
                <tr>
                    <th>Operador</th>
                    <th>Programa</th>
                    <th>Acción</th>
                </tr>
                <?php while ($a = $asignaciones->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($a['programa_nombre']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="asignacion_id" value="<?php echo $a['asignacion_id']; ?>">
                                <button type="submit" name="eliminar_asignacion" class="btn-form" onclick="return confirm('¿Eliminar asignación?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>

        <?php if ($rol === 'operador'): ?>
            <h2>Mis programas asignados</h2>

            <?php
            $stmt = $conexion->prepare("
                SELECT p.id, p.nombre, p.descripcion
                FROM asignaciones a
                INNER JOIN programas p ON a.id_programa = p.id
                WHERE a.id_usuario = ?
            ");
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $resAsignados = $stmt->get_result();

            if ($resAsignados->num_rows > 0):
            ?>
                <table>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acción</th>
                    </tr>
                    <?php while ($p = $resAsignados->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($p['descripcion']); ?></td>
                            <td><a href="programa.php?id_programa=<?php echo $p['id']; ?>" class="btn-editar-fx">Abrir</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No tenés programas asignados.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php if ($rol === 'operador'): ?>
    <a href="personal_fx.php" class="btn-form" style="margin-bottom: 20px; display: inline-block;">
        Ingresar a FX personales
    </a>
<?php endif; ?>

</body>
</html>
