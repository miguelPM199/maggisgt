<?php


session_start();

// Simulación de productos en promoción (20 espacios, 5 con precios y texto "2x1")
$precios_demo = [19.99, 32.50, 27.00, 44.99, 15.75];
$productos = [];
for ($i = 1; $i <= 20; $i++) {
    $productos[] = [
        "id" => $i,
        "nombre" => "Promo $i",
        "precio" => ($i <= 5) ? $precios_demo[$i - 1] : "",
        "promo" => "2x1", // Siempre muestra 2x1 en todas las tarjetas
        "imagen" => ""
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
    header("Location: promociones.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Promociones 2x1 - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .promo-badge {
            display: block;
            margin: 0 auto 0.5rem auto;
            background: linear-gradient(90deg, #ff5858 0%, #f09819 100%);
            color: #fff;
            font-weight: 700;
            padding: 0.3em 1.2em;
            border-radius: 20px;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(255, 88, 88, 0.15);
            width: fit-content;
        }
        .product-card {
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(231, 200, 115, 0.18);
            background: linear-gradient(135deg, #232526 60%, #e7c873 100%);
            color: #e7c873;
            min-height: 370px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid #e7c87333;
            position: relative;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 12px 36px rgba(231, 200, 115, 0.25);
            border-color: #e7c873;
            background: linear-gradient(135deg, #e7c873 0%, #232526 100%);
            color: #232526;
        }
        .product-img {
            background: rgba(231, 200, 115, 0.08);
            border-radius: 16px 16px 0 0;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #ff5858;
            margin-bottom: 1rem;
        }
        .product-card h5 {
            font-weight: 700;
            color: inherit;
        }
        .product-card .text-muted {
            color: #e7c873cc !important;
        }
        .add-cart-btn {
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(90deg, #e7c873 0%, #bfa14a 100%);
            color: #232526;
            border: none;
            transition: background 0.2s, color 0.2s;
        }
        .add-cart-btn:disabled {
            background: #bfa14a55;
            color: #23252699;
            border: none;
        }
        .add-cart-btn:hover:not(:disabled) {
            background: linear-gradient(90deg, #bfa14a 0%, #e7c873 100%);
            color: #232526;
        }
        .go-cart-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, #e7c873 0%, #bfa14a 100%);
            color: #232526;
            border: none;
        }
        .go-cart-btn:hover {
            background: linear-gradient(90deg, #bfa14a 0%, #e7c873 100%);
            color: #232526;
        }
        .volver-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, #232526 0%, #e7c873 100%);
            color: #e7c873;
            border: none;
        }
        .volver-btn:hover {
            background: linear-gradient(90deg, #e7c873 0%, #232526 100%);
            color: #232526;
        }
        @media (max-width: 991px) {
            .go-cart-btn, .volver-btn {
                position: static;
                margin: 1rem 0 0 0;
                width: 100%;
                display: block;
            }
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
        <h1 class="mb-4 text-center" style="color:#e7c873;font-weight:800;letter-spacing:1px;">
            Promociones <span style="color:#ff5858;">2x1</span>
        </h1>
        <div class="row g-4">
            <?php foreach ($productos as $producto): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card p-3">
                        <span class="promo-badge"><?php echo $producto['promo']; ?></span>
                        <div class="product-img mb-3">
                            <i class="bi bi-gift"></i>
                        </div>
                        <h5 class="text-center mb-2"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="text-center text-muted mb-3 fs-5">
                            <?php echo $producto['precio'] !== "" ? "Q " . number_format($producto['precio'],2) : "<span class='text-secondary'>Precio</span>"; ?>
                        </p>
                        <form method="post" action="promociones.php" class="d-grid">
                            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                            <button type="submit" class="btn add-cart-btn" <?php echo $producto['precio'] === "" ? "disabled" : ""; ?>>
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <a href="carrito.php" class="btn go-cart-btn shadow">
        <i class="bi bi-cart-check"></i> Ir al carrito
    </a>
    <a href="index.php" class="btn volver-btn shadow">
        <i class="bi bi-arrow-left"></i> Volver al inicio
    </a>
    <script src=