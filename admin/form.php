<?php
require_once "../config/db.php";

$id = $_GET['id'] ?? null;
$producto = [
    "nombre" => "",
    "precio" => "",
    "descripcion" => "",
    "stock" => ""
];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $descripcion = $_POST["descripcion"];
    $stock = $_POST["stock"];

    if ($id) {
        $stmt = $conn->prepare("UPDATE productos SET nombre=?, precio=?, descripcion=?, stock=? WHERE id=?");
        $stmt->execute([$nombre, $precio, $descripcion, $stock, $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO productos (nombre, precio, descripcion, stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $precio, $descripcion, $stock]);
    }
    header("Location: crud.php");
    exit;
}

include "header.php";
?>

<h1 class="h3"><?= $id ? "Editar" : "Agregar" ?> Producto</h1>

<form method="POST">
    <div class="mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Precio</label>
        <input type="number" step="0.01" name="precio" class="form-control" value="<?= $producto['precio'] ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" value="<?= $producto['stock'] ?>">
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
    <a href="crud.php" class="btn btn-secondary">Cancelar</a>
</form>

<?php include "footer.php"; ?>