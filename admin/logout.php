<?php

session_start();

// Eliminar claves de autenticación y todas las variables de sesión
$_SESSION = [];

// Eliminar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"] ?? false, $params["httponly"] ?? false
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: ../index.php');
exit;
?>