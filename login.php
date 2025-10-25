<?php

session_start();

// base URL dinámico (incluye puerto y carpeta actual)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$baseUrl = $scheme . '://' . $host . $baseDir . '/';

// Parámetros de conexión (igual que productos_mx)
$dbHost = "178.128.67.133";
$dbUser = "usrmaggisgt";
$dbPass = "mipass";
$dbName = "maggisgt";
$dbPort = 3306;

// Conectar con mysqli
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($mysqli->connect_errno) {
    $dbError = "Error de conexión a MySQL: " . $mysqli->connect_error;
} else {
    $dbError = null;
}

// Si ya hay sesión, validar que el usuario exista en la BD; si es válido redirigir, si no destruir sesión
if (!empty($_SESSION['usuario']) && !$dbError) {
    $checkUser = $mysqli->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    if ($checkUser) {
        $checkUser->bind_param('s', $_SESSION['usuario']);
        $checkUser->execute();
        $checkUser->store_result();
        if ($checkUser->num_rows === 1) {
            $checkUser->close();
            header('Location: ' . $baseUrl . 'admin/principall.php');
            exit;
        }
        $checkUser->close();
    }
    // si no existe en BD, limpiar sesión para forzar login
    session_unset();
    session_destroy();
    session_start();
}

// Procesar POST (login)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($dbError) {
        // Asegurar que no quede sesión válida
        session_unset();
        session_regenerate_id(true);
        $_SESSION['login_error'] = "No hay conexión a la base de datos. Intenta más tarde.";
        header('Location: ' . $baseUrl . 'login.php');
        exit;
    }

    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $contrasena === '') {
        // limpiar sesión previa y guardar mensaje
        session_unset();
        session_regenerate_id(true);
        $_SESSION['login_error'] = "Por favor completa usuario y contraseña.";
        $_SESSION['usuario_temp'] = $usuario;
        header('Location: ' . $baseUrl . 'login.php');
        exit;
    }

    // Solo permitir el usuario admin (si ese es el requisito)
    $allowed_username = 'admin';

    // Preparar consulta segura y comprobar existencia
    $stmt = $mysqli->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $usuario);
        if (!$stmt->execute()) {
            error_log("MySQL execute error: " . $stmt->error);
            $stmt->close();
            session_unset();
            session_regenerate_id(true);
            $_SESSION['login_error'] = "Error interno. Intenta más tarde.";
            header('Location: ' . $baseUrl . 'login.php');
            exit;
        }

        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $username_db, $password_db);
            $stmt->fetch();

            $ok = false;
            // Verificar contraseña: hashed o texto plano
            if (!empty($password_db) && @password_verify($contrasena, $password_db)) {
                $ok = true;
            } elseif (hash_equals((string)$password_db, (string)$contrasena)) {
                $ok = true;
                // opcional: re-hashear contraseña en BD para mayor seguridad
                $newHash = password_hash($contrasena, PASSWORD_DEFAULT);
                $upd = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($upd) {
                    $upd->bind_param('si', $newHash, $id);
                    $upd->execute();
                    $upd->close();
                }
            }

            // Requerir además que el usuario sea exactamente 'admin'
            if ($ok && $username_db === $allowed_username) {
                // login exitoso: limpiar mensajes anteriores y establecer sesión
                session_unset();
                session_regenerate_id(true);
                $_SESSION['usuario'] = $username_db;
                $_SESSION['user_id'] = $id;
                unset($_SESSION['usuario_temp'], $_SESSION['login_error']);
                $stmt->close();
                header('Location: ' . $baseUrl . 'admin/principall.php');
                exit;
            }
        }
        $stmt->close();
    } else {
        error_log("MySQL prepare error: " . $mysqli->error);
    }

    // Credenciales inválidas o no admin: limpiar sesión y mostrar error
    session_unset();
    session_regenerate_id(true);
    $_SESSION['login_error'] = "Usuario o contraseña incorrectos";
    $_SESSION['usuario_temp'] = $usuario;
    header('Location: ' . $baseUrl . 'login.php');
    exit;
}

// Mostrar formulario (GET)
// Si vino de un "agregar usuario" u otra acción y quieres que los campos no queden "pegados",
// te aseguras de que $_SESSION no conserve valores innecesarios. Ya limpiamos en cada flujo.
// Mostrar y luego limpiar los mensajes temporales.
$err = $_SESSION['login_error'] ?? null;
$usuario_temp = $_SESSION['usuario_temp'] ?? '';
unset($_SESSION['login_error'], $_SESSION['usuario_temp']);

// Cerrar conexión
if ($mysqli) {
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login — MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:#f6f7f9; font-family:Inter,system-ui,Arial,sans-serif; padding-top:60px; }
        .login-wrap{ max-width:420px; margin:40px auto; }
        .card{ border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.08); }
        .brand{ font-weight:800; color:#ff8a3d; letter-spacing:1px; }
        .error{ background:#fff1f0; border-left:4px solid #d9534f; padding:10px 12px; border-radius:6px; color:#612020; margin-bottom:12px; }
        .db-error{ background:#fff3cd; border-left:4px solid #d39e00; padding:10px 12px; border-radius:6px; color:#856404; margin-bottom:12px; }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="text-center mb-3">
            <div class="brand">MaggiSGT</div>
            <small class="text-muted">Acceso administrativo</small>
        </div>

        <?php if (!empty($dbError)): ?>
            <div class="db-error"><strong>Error de conexión a la BD:</strong> <?php echo htmlspecialchars($dbError); ?></div>
        <?php endif; ?>

        <?php if ($err): ?>
            <div class="error"><?php echo htmlspecialchars($err); ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <form method="post" action="<?php echo htmlspecialchars($baseUrl . 'login.php'); ?>" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input name="usuario" class="form-control" value="<?php echo htmlspecialchars($usuario_temp); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input name="contrasena" type="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button class="btn btn-warning" type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>