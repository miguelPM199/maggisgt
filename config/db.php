
<?php
$host = "127.0.0.1";
$port = 3307; // Cambia a 3306 si MySQL está en el puerto por defecto
$user = "root";
$pass = "";
$dbname = "maggisgt";

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>