<?php


session_start();

// Seguridad: solo admin puede entrar
if (!isset($_SESSION["usuario"]) || $_SESSION["usuario"] !== "admin") {
    header("Location: ../index.php");
    exit;
}


// ...existing code...
// Conexión a la base de datos MySQL en el puerto 3307
$mysqli = new mysqli("localhost", "root", "", "maggisgt", 3307);
if ($mysqli->connect_errno) {
    die("Error de conexión a MySQL: " . $mysqli->connect_error);
}

// CRUD para productos_gt
if (isset($_POST['accion_gt'])) {
    if ($_POST['accion_gt'] === 'agregar') {
        $stmt = $mysqli->prepare("INSERT INTO productos_gt (nombre, precio, imagen) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $_POST['nombre'], $_POST['precio'], $_POST['imagen']);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['accion_gt'] === 'editar') {
        $stmt = $mysqli->prepare("UPDATE productos_gt SET nombre=?, precio=?, imagen=? WHERE id=?");
        $stmt->bind_param("sdsi", $_POST['nombre'], $_POST['precio'], $_POST['imagen'], $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['accion_gt'] === 'eliminar') {
        $stmt = $mysqli->prepare("DELETE FROM productos_gt WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?tab=gt");
    exit;
}

// CRUD para productos_mx
if (isset($_POST['accion_mx'])) {
    if ($_POST['accion_mx'] === 'agregar') {
        $stmt = $mysqli->prepare("INSERT INTO productos_mx (nombre, precio, imagen) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $_POST['nombre'], $_POST['precio'], $_POST['imagen']);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['accion_mx'] === 'editar') {
        $stmt = $mysqli->prepare("UPDATE productos_mx SET nombre=?, precio=?, imagen=? WHERE id=?");
        $stmt->bind_param("sdsi", $_POST['nombre'], $_POST['precio'], $_POST['imagen'], $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['accion_mx'] === 'eliminar') {
        $stmt = $mysqli->prepare("DELETE FROM productos_mx WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?tab=mx");
    exit;
}

// Agregar a promociones
if (isset($_POST['agregar_promo'])) {
    $tipo = $_POST['tipo'];
    $id = intval($_POST['id']);
    if ($tipo === 'gt') {
        $res = $mysqli->query("SELECT * FROM productos_gt WHERE id=$id");
        $producto = $res->fetch_assoc();
    } else {
        $res = $mysqli->query("SELECT * FROM productos_mx WHERE id=$id");
        $producto = $res->fetch_assoc();
    }
    if ($producto) {
        $stmt = $mysqli->prepare("INSERT INTO promociones (tipo, nombre, precio, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $tipo, $producto['nombre'], $producto['precio'], $producto['imagen']);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?tab=promo");
    exit;
}

// Eliminar de promociones
if (isset($_POST['eliminar_promo'])) {
    $stmt = $mysqli->prepare("DELETE FROM promociones WHERE id=?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php?tab=promo");
    exit;
}

// Pestaña activa
$tab = $_GET['tab'] ?? 'gt';

// Obtener datos de la base
$productos_gt = $mysqli->query("SELECT * FROM productos_gt")->fetch_all(MYSQLI_ASSOC);
$productos_mx = $mysqli->query("SELECT * FROM productos_mx")->fetch_all(MYSQLI_ASSOC);
$promociones = $mysqli->query("SELECT * FROM promociones")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link.active {
            background: #e7c873;
            color: #232526;
            font-weight: bold;
        }
        .nav-tabs .nav-link {
            color: #232526;
        }
        .table img {
            max-width: 60px;
            max-height: 60px;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4">Panel de Administración</h2>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?php if($tab==='gt') echo 'active'; ?>" href="?tab=gt">Productos Guatemaltecos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($tab==='mx') echo 'active'; ?>" href="?tab=mx">Productos Mexicanos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php if($tab==='promo') echo 'active'; ?>" href="?tab=promo">Promociones</a>
        </li>
    </ul>

    <!-- Productos Guatemaltecos -->
    <?php if($tab==='gt'): ?>
    <h4>Productos Guatemaltecos</h4>
    <form class="row g-2 mb-3" method="post">
        <input type="hidden" name="accion_gt" value="agregar">
        <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required>
        </div>
        <div class="col-md-4">
            <input type="text" name="imagen" class="form-control" placeholder="Ruta de imagen (opcional)">
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100" type="submit">Agregar</button>
        </div>
    </form>
    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acciones</th>
                <th>Promociones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($productos_gt as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php if($p['imagen']): ?><img src="../<?php echo htmlspecialchars($p['imagen']); ?>"><?php endif; ?></td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="accion_gt" value="editar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($p['precio']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <input type="text" name="imagen" value="<?php echo htmlspecialchars($p['imagen']); ?>" class="form-control form-control-sm" placeholder="Ruta de imagen">
                        <button class="btn btn-primary btn-sm mt-1" type="submit">Guardar</button>
                    </form>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="accion_gt" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button class="btn btn-danger btn-sm mt-1" type="submit" onclick="return confirm('¿Eliminar producto?')">Eliminar</button>
                    </form>
                </td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="agregar_promo" value="1">
                        <input type="hidden" name="tipo" value="gt">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button class="btn btn-warning btn-sm" type="submit">Agregar a promociones</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Productos Mexicanos -->
    <?php if($tab==='mx'): ?>
    <h4>Productos Mexicanos</h4>
    <form class="row g-2 mb-3" method="post">
        <input type="hidden" name="accion_mx" value="agregar">
        <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required>
        </div>
        <div class="col-md-4">
            <input type="text" name="imagen" class="form-control" placeholder="Ruta de imagen (opcional)">
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100" type="submit">Agregar</button>
        </div>
    </form>
    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acciones</th>
                <th>Promociones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($productos_mx as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php if($p['imagen']): ?><img src="../<?php echo htmlspecialchars($p['imagen']); ?>"><?php endif; ?></td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="accion_mx" value="editar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($p['precio']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <input type="text" name="imagen" value="<?php echo htmlspecialchars($p['imagen']); ?>" class="form-control form-control-sm" placeholder="Ruta de imagen">
                        <button class="btn btn-primary btn-sm mt-1" type="submit">Guardar</button>
                    </form>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="accion_mx" value="eliminar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button class="btn btn-danger btn-sm mt-1" type="submit" onclick="return confirm('¿Eliminar producto?')">Eliminar</button>
                    </form>
                </td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="agregar_promo" value="1">
                        <input type="hidden" name="tipo" value="mx">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button class="btn btn-warning btn-sm" type="submit">Agregar a promociones</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Promociones -->
    <?php if($tab==='promo'): ?>
    <h4>Promociones</h4>
    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Tipo</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Quitar</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($promociones as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo ($p['tipo'] === 'gt') ? 'Guatemalteco' : 'Mexicano'; ?></td>
                <td><?php if($p['imagen']): ?><img src="../<?php echo htmlspecialchars($p['imagen']); ?>"><?php endif; ?></td>
                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                <td><?php echo htmlspecialchars($p['precio']); ?></td>
                <td>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="eliminar_promo" value="1">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('¿Quitar de promociones?')">Quitar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-secondary mt-4">Volver al inicio</a>
</div>
</body>
</html>