<?php

session_start();

$usuario_valido = "admin";
$contrasena_valida = "1234";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"] ?? "";
    $contrasena = $_POST["contrasena"] ?? "";

    if ($usuario === $usuario_valido && $contrasena === $contrasena_valida) {
        $_SESSION["usuario"] = $usuario;
        unset($_SESSION["usuario_temp"]);
        header("Location: admin/index.php");
        exit;
    } else {
        $_SESSION["login_error"] = "Usuario o contraseña incorrectos";
        $_SESSION["usuario_temp"] = $usuario;
        header("Location: index.php");
        exit;
    }
}
?>