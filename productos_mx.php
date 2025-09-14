<?php


session_start();

// Simulación de productos (20 espacios, 4 con precios y ejemplo de imagen)
$precios_demo = [55.00, 39.99, 72.50, 28.75];
$imagenes_demo = [
    "https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=400&q=80",
    "https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=400&q=80",
    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80",
    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80"
];
$productos = [];
for ($i = 1; $i <= 20; $i++) {
    $productos[] = [
        "id" => $i,
        "nombre" => "Producto MX $i",
        "precio" => ($i <= 4) ? $precios_demo[$i - 1] : "",
        "imagen" => ($i <= 4) ? $imagenes_demo[$i - 1] : ""
    ];
}

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = intval($_POST['producto_id']);
    foreach ($productos as $prod) {
        if ($prod['id'] === $producto_id && $prod['precio'] !== "") {
            // Buscar si ya está en el carrito
            $encontrado = false;
            if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['id'] === $producto_id) {
                    $item['cantidad'] += 1;
                    $encontrado = true;
                    break;
                }
            }
            unset($item);
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    "id" => $prod['id'],
                    "nombre" => $prod['nombre'],
                    "precio" => $prod['precio'],
                    "cantidad" => 1
                ];
            }
            break;
        }
    }
    header("Location: productos_mx.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos Mexicanos - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .custom-carousel {
            position: relative;
            margin-bottom: 3rem;
        }
        .carousel-main-img {
            width: 420px;
            height: 260px;
            object-fit: cover;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(224, 204, 148, 0.15);
            z-index: 2;
            position: relative;
        }
        .carousel-side-img {
            width: 140px;
            height: 90px;
            object-fit: cover;
            border-radius: 12px;
            opacity: 0.5;
            filter: blur(1.5px) grayscale(0.3);
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
            transition: opacity 0.3s, filter 0.3s;
        }
        .carousel-side-img.left {
            left: 0;
        }
        .carousel-side-img.right {
            right: 0;
        }
        .carousel-controls-custom {
            position: absolute;
            top: 50%;
            width: 100%;
            z-index: 3;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }
        .carousel-controls-custom button {
            background: rgba(96, 182, 248, 0.7);
            border: none;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #232526;
            font-size: 1.5rem;
            transition: background 0.2s;
        }
        .carousel-controls-custom button:hover {
            background: #5aecdbff;
        }
        @media (max-width: 600px) {
            .carousel-main-img { width: 98vw; height: 160px; }
            .carousel-side-img { width: 60px; height: 40px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MaggiSGT</a>
            <a href="carrito.php" class="btn btn-outline-success position-relative">
                <i class="bi bi-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </span>
            </a>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Productos Mexicanos</h1>
        <!-- Carrusel personalizado -->
        <div class="d-flex justify-content-center custom-carousel" id="customCarousel">
            <?php
                $mainIndex = isset($_GET['img']) ? intval($_GET['img']) : 0;
                $totalImgs = count($imagenes_demo);
                $prevIndex = ($mainIndex - 1 + $totalImgs) % $totalImgs;
                $nextIndex = ($mainIndex + 1) % $totalImgs;
            ?>
            <img src="<?php echo $imagenes_demo[$prevIndex]; ?>" class="carousel-side-img left" alt="previa">
            <img src="<?php echo $imagenes_demo[$mainIndex]; ?>" class="carousel-main-img" alt="principal">
            <img src="<?php echo $imagenes_demo[$nextIndex]; ?>" class="carousel-side-img right" alt="siguiente">
            <div class="carousel-controls-custom">
                <a href="?img=<?php echo $prevIndex; ?>" class="btn btn-light"><i class="bi bi-chevron-left"></i></a>
                <a href="?img=<?php echo $nextIndex; ?>" class="btn btn-light"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>
        <!-- Tarjetas para los 20 productos -->
        <div class="row g-4">
            <?php foreach ($productos as $producto): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card p-3">
                        <div class="product-img mb-3">
                            <?php if ($producto['imagen']): ?>
                                <img src="<?php echo $producto['imagen']; ?>" alt="Producto" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                            <?php else: ?>
                                <i class="bi bi-image"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="text-center mb-2"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="text-center text-muted mb-3">
                            <?php echo $producto['precio'] !== "" ? "Q " . number_format($producto['precio'],2) : "<span class='text-secondary'>Precio</span>"; ?>
                        </p>
                        <form method="post" action="productos_mx.php" class="d-grid">
                            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                            <button type="submit" class="btn btn-danger add-cart-btn" <?php echo $producto['precio'] === "" ? "disabled" : ""; ?>>
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <a href="carrito.php" class="btn btn-success btn-lg go-cart-btn shadow">
        <i class="bi bi-cart-check"></i> Ir al carrito
    </a>
    <a href="index.php" class="btn btn-outline-primary btn-lg volver-btn shadow">
        <i class="bi bi-arrow-left"></i> Volver al inicio
    </a>
    <script src=