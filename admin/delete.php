<?php
require_once "../config/db.php";

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: crud.php");
exit;