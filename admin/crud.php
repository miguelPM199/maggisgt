<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluimos la conexión a la base de datos
require_once __DIR__ . "/../config/db.php";

// Obtenemos todos los productos
$stmt = $conn->query("SELECT * FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Productos</h1>
    <a href="form.php" class="btn btn-primary">Agregar Producto</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td>Q<?= number_format($p['precio'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
                <a href="form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que quieres eliminar este producto?');">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>