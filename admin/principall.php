<?php
session_start();

// Forzar que la sesión esté validada: si no, ir al login
if (empty($_SESSION['user_id']) || empty($_SESSION['usuario']) || $_SESSION['usuario'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Conexión a la base de datos MySQL en el puerto 3307
$mysqli = new mysqli("localhost", "root", "", "maggisgt", 3307);
if ($mysqli->connect_errno) {
    die("Error de conexión a MySQL: " . $mysqli->connect_error);
}

// --- UTIL: asegurar columna descripcion en tablas ---
function ensure_description_column($mysqli, $table) {
    $db = $mysqli->real_escape_string($mysqli->query("SELECT DATABASE()")->fetch_row()[0]);
    $tableEsc = $mysqli->real_escape_string($table);
    $q = "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='{$db}' AND TABLE_NAME='{$tableEsc}' AND COLUMN_NAME='descripcion'";
    $res = $mysqli->query($q);
    $row = $res ? $res->fetch_assoc() : null;
    if (!$row || intval($row['c']) === 0) {
        $mysqli->query("ALTER TABLE `{$tableEsc}` ADD `descripcion` TEXT NULL");
    }
}
ensure_description_column($mysqli, 'productos_gt');
ensure_description_column($mysqli, 'productos_mx');
ensure_description_column($mysqli, 'promociones');

// --- IA: generar descripción (usa OpenAI si OPENAI_API_KEY está en entorno, si no usa fallback) ---
function generate_description_api($name, $tipo) {
    $key = getenv('OPENAI_API_KEY');
    if (!$key) return false;
    $prompt = "Escribe en español una breve descripción comercial (1-2 frases) para un producto llamado \"{$name}\". Tipo: {$tipo}. Mantén el texto claro, atractivo y conciso.";
    $payload = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role"=>"system","content"=>"Eres un redactor creativo que genera descripciones cortas para productos en español."],
            ["role"=>"user","content"=>$prompt]
        ],
        "max_tokens" => 100,
        "temperature" => 0.7
    ];
    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$key}"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err || !$resp) return false;
    $json = json_decode($resp, true);
    if (isset($json['choices'][0]['message']['content'])) {
        return trim($json['choices'][0]['message']['content']);
    }
    return false;
}
function generate_description_fallback($name, $tipo) {
    $tipo_text = ($tipo === 'gt') ? "guatemalteco" : (($tipo === 'mx') ? "mexicano" : $tipo);
    return "{$name} es un producto {$tipo_text} de alta calidad, ideal para quienes buscan buen sabor y excelente relación precio-calidad.";
}
function generate_description($name, $tipo) {
    // intenta API
    $desc = generate_description_api($name, $tipo);
    if ($desc && strlen($desc) > 10) return $desc;
    return generate_description_fallback($name, $tipo);
}

// --- Manejo de imagen drag & drop y guardado en /uploads ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_imagen = uniqid('img_') . '.' . $ext;
        $uploads_dir = realpath(__DIR__ . '/../uploads');
        if ($uploads_dir === false) {
            $uploads_dir = __DIR__ . '/../uploads';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }
        }
        $ruta_destino = $uploads_dir . DIRECTORY_SEPARATOR . $nombre_imagen;
        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            die('Error al guardar la imagen. Verifica permisos y ruta de la carpeta uploads.');
        }
        $_POST['imagen'] = 'uploads/' . $nombre_imagen;
    }
}

// --- CRUD unificado para agregar productos (ahora con descripcion generada) ---
if (isset($_POST['accion_producto']) && $_POST['accion_producto'] === 'agregar' && isset($_POST['tipo_producto'])) {
    $tipo = $_POST['tipo_producto'];
    $nombre = $_POST['nombre'] ?? '';
    $precio = floatval($_POST['precio'] ?? 0);
    $imagen = $_POST['imagen'] ?? '';
    // generar descripcion IA (o fallback)
    $descripcion = generate_description($nombre, $tipo);
    if ($tipo === 'gt') {
        $stmt = $mysqli->prepare("INSERT INTO productos_gt (nombre, precio, imagen, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $nombre, $precio, $imagen, $descripcion);
        $stmt->execute();
        $stmt->close();
        header("Location: principall.php?tab=gt");
        exit;
    }
    if ($tipo === 'mx') {
        $stmt = $mysqli->prepare("INSERT INTO productos_mx (nombre, precio, imagen, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $nombre, $precio, $imagen, $descripcion);
        $stmt->execute();
        $stmt->close();
        header("Location: principall.php?tab=mx");
        exit;
    }
}

// CRUD para productos_gt (editar/eliminar) - ahora incluye descripcion
if (isset($_POST['accion_gt'])) {
    if ($_POST['accion_gt'] === 'editar') {
        $stmt = $mysqli->prepare("UPDATE productos_gt SET nombre=?, precio=?, imagen=?, descripcion=? WHERE id=?");
        $stmt->bind_param("sdssi", $_POST['nombre'], $_POST['precio'], $_POST['imagen'], $_POST['descripcion'], $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['accion_gt'] === 'eliminar') {
        $stmt = $mysqli->prepare("DELETE FROM productos_gt WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: principall.php?tab=gt");
    exit;
}

// CRUD para productos_mx (editar/eliminar) - incluye descripcion
if (isset($_POST['accion_mx'])) {
    if ($_POST['accion_mx'] === 'editar') {
        $stmt = $mysqli->prepare("UPDATE productos_mx SET nombre=?, precio=?, imagen=?, descripcion=? WHERE id=?");
        $stmt->bind_param("sdssi", $_POST['nombre'], $_POST['precio'], $_POST['imagen'], $_POST['descripcion'], $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    if ($_POST['accion_mx'] === 'eliminar') {
        $stmt = $mysqli->prepare("DELETE FROM productos_mx WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: principall.php?tab=mx");
    exit;
}

// Agregar a promociones (ahora guarda descripcion también)
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
        $imagen = $producto['imagen'] ?? '';
        $descripcion = $producto['descripcion'] ?? generate_description($producto['nombre'], $tipo);
        $stmt = $mysqli->prepare("INSERT INTO promociones (tipo, nombre, precio, imagen, descripcion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $tipo, $producto['nombre'], $producto['precio'], $imagen, $descripcion);
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
$tab = $_GET['tab'] ?? 'productos';

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
        #drop-area-producto {
            border:2px dashed #aaa; border-radius:10px; padding:10px; text-align:center; background:#fafafa; cursor:pointer;
        }
        #drop-area-producto.dragover { background: #f0f0f0; }
        #preview-producto { max-width:100px;max-height:60px;display:none;margin-top:5px; }
        .descripcion-input { min-height:54px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Panel de Administración</h2>
        <div>
            <span class="me-2">Conectado como: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
            <form method="post" action="logout.php" style="display:inline;">
                <button class="btn btn-outline-danger btn-sm" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?php if($tab==='productos') echo 'active'; ?>" href="?tab=productos">Productos</a>
        </li>
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

    <!-- NUEVO: Agregar producto a GT o MX -->
    <?php if($tab==='productos'): ?>
    <h4>Agregar Producto</h4>
    <form class="row g-2 mb-3" method="post" enctype="multipart/form-data">
        <input type="hidden" name="accion_producto" value="agregar">
        <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="precio" class="form-control" placeholder="Precio" required>
        </div>
        <div class="col-md-3">
            <select name="tipo_producto" class="form-select" required>
                <option value="">¿A qué productos agregar?</option>
                <option value="gt">Productos Guatemaltecos</option>
                <option value="mx">Productos Mexicanos</option>
            </select>
        </div>
        <div class="col-md-2">
            <div id="drop-area-producto">
                <p style="margin:0;">Arrastra una imagen aquí o haz clic</p>
                <input type="file" name="imagen" id="imagen-producto" accept="image/*" style="display:none;">
                <img id="preview-producto" src="">
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100" type="submit">Agregar</button>
        </div>
    </form>
    <?php endif; ?>

    <!-- Productos Guatemaltecos -->
    <?php if($tab==='gt'): ?>
    <h4>Productos Guatemaltecos</h4>
    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Descripción</th>
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
                    <form method="post" class="d-inline" enctype="multipart/form-data">
                        <input type="hidden" name="accion_gt" value="editar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($p['precio']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <textarea name="descripcion" class="form-control form-control-sm descripcion-input" placeholder="Descripción"><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></textarea>
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
    <table class="table table-bordered table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Descripción</th>
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
                    <form method="post" class="d-inline" enctype="multipart/form-data">
                        <input type="hidden" name="accion_mx" value="editar">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <input type="number" step="0.01" name="precio" value="<?php echo htmlspecialchars($p['precio']); ?>" class="form-control form-control-sm" required>
                </td>
                <td>
                        <textarea name="descripcion" class="form-control form-control-sm descripcion-input" placeholder="Descripción"><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></textarea>
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
                <th>Descripción</th>
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
                <td><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></td>
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
<script>
const dropAreaProducto = document.getElementById('drop-area-producto');
const inputImagenProducto = document.getElementById('imagen-producto');
const previewProducto = document.getElementById('preview-producto');
if(dropAreaProducto && inputImagenProducto && previewProducto) {
    dropAreaProducto.addEventListener('click', function() {
        inputImagenProducto.click();
    });
    inputImagenProducto.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            let reader = new FileReader();
            reader.onload = function(ev) {
                previewProducto.src = ev.target.result;
                previewProducto.style.display = 'block';
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    dropAreaProducto.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropAreaProducto.classList.add('dragover');
    });
    dropAreaProducto.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropAreaProducto.classList.remove('dragover');
    });
    dropAreaProducto.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropAreaProducto.classList.remove('dragover');
        let files = e.dataTransfer.files;
        if (files.length > 0) {
            inputImagenProducto.files = files;
            let reader = new FileReader();
            reader.onload = function(ev) {
                previewProducto.src = ev.target.result;
                previewProducto.style.display = 'block';
            }
            reader.readAsDataURL(files[0]);
        }
    });
}
</script>
</body>
</html>