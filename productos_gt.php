<?php

session_start();



// Simulación de productos (20 espacios, 5 con precios y 7 con imágenes)
$precios_demo = [99.99, 130.00, 150.00, 160.00, 85.00, 200.00];
$imagenes_demo = [
    "assets/img/img1.jpeg",
    "assets/img/img2.jpeg",
    "assets/img/img3.jpeg",
    "assets/img/img4.jpeg",
    "assets/img/img5.jpeg",
    "assets/img/img6.jpeg",
    "assets/img/img7.jpeg"
];
$productos = [];
for ($i = 1; $i <= 20; $i++) {
    $productos[] = [
        "id" => $i,
        "nombre" => "Producto Guatemalteco $i",
        "precio" =>  ($i <= count($precios_demo)) ? $precios_demo[$i - 1] : "",
        "imagen" => ($i <= 7) ? $imagenes_demo[$i - 1] : ""
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
    // Mostrar notificación de producto agregado
    $showNotification = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Guatemaltecos - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primary: #4c6ca4ff;
            --color-primary-dark: #bfa14a;
            --color-dark: #232526;
            --color-light: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
            color: var(--color-light);
            padding-top: 80px;
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(90deg, var(--color-dark) 0%, #333 100%) !important;
            border-bottom: 2px solid var(--color-primary);
        }
        
        .navbar-brand {
            font-weight: 800;
            color: var(--color-primary) !important;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .page-title {
            color: var(--color-primary);
            font-weight: 800;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            padding-bottom: 10px;
            border-bottom: 2px solid var(--color-primary);
            margin-bottom: 30px;
        }
        
        .product-card {
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(231, 200, 115, 0.18);
            background: linear-gradient(135deg, #346762ff 60%, #73d8e7ff 100%);
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
            background: linear-gradient(135deg, #83a1e1ff 0%, #1280b7ff 100%);
            color: #232526;
        }
        
        .product-img {
            background: rgba(231, 200, 115, 0.08);
            border-radius: 16px 16px 0 0;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #e7c873;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .product-card:hover .product-img {
            color: #232526;
            background: rgba(35, 37, 38, 0.15);
        }
        
        .product-card h5 {
            font-weight: 700;
            color: inherit;
        }
        
        .product-card .text-muted {
            color: #e7c873cc !important;
        }
        
        .product-card:hover .text-muted {
            color: #232526cc !important;
        }
        
        .add-cart-btn {
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(90deg, #8edbb2ff 0%, #f8e19dff 100%);
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
            background: linear-gradient(90deg, #a69667ff 0%, #0dcef0ff 100%);
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
            padding: 12px 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .go-cart-btn:hover {
            background: linear-gradient(90deg, #bfa14a 0%, #e7c873 100%);
            color: #232526;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        
        .volver-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, #689fbbff 0%, #7373e7ff 100%);
            color: #e7c873;
            border: none;
            padding: 12px 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .volver-btn:hover {
            background: linear-gradient(90deg, #ef5d52ff 0%, #91c5dfff 100%);
            color: #232526;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        
        .cart-badge {
            font-size: 0.7rem;
        }
        
        @media (max-width: 991px) {
            .go-cart-btn, .volver-btn {
                position: static;
                margin: 1rem 0 0 0;
                width: 100%;
                display: block;
            }
            
            .fixed-btn-container {
                display: flex;
                justify-content: space-between;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: var(--color-dark);
                padding: 15px;
                z-index: 1000;
                box-shadow: 0 -4px 10px rgba(107, 214, 148, 0.3);
            }
            
            .go-cart-btn, .volver-btn {
                position: static;
                margin: 0;
                width: 48%;
                display: inline-block;
            }
        }
        
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: var(--color-primary);
            color: var(--color-dark);
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1050;
            font-weight: 600;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .notification.show {
            transform: translateX(0);
        }
    </style>
</head>
<body>
    <?php if (isset($showNotification) && $showNotification): ?>
        <div class="notification" id="addedNotification"><i class="bi bi-cart-check"></i> Producto agregado al carrito</div>
    <?php endif; ?>
    
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-star-fill"></i> MaggiSGT
            </a>
            <a href="carrito.php" class="btn btn-outline-warning position-relative">
                <i class="bi bi-cart"></i> Carrito
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </span>
            </a>
        </div>
    </nav>
    
    <div class="container mt-5">
        <h1 class="text-center page-title">
            <i class="bi bi-basket2"></i> Productos Guatemaltecos
        </h1>
        
        <div class="row g-4">
            <?php foreach ($productos as $producto): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card p-3">
                        <div class="product-img mb-3">
                            <?php if ($producto['imagen']): ?>
                                <img src="<?php echo $producto['imagen']; ?>" alt="Producto" />
                            <?php else: ?>
                                <i class="bi bi-image"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="text-center mb-2"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="text-center text-muted mb-3 fs-5">
                            <?php echo $producto['precio'] !== "" ? "Q " . number_format($producto['precio'],2) : "<span class='text-secondary'>Próximamente</span>"; ?>
                        </p>
                        <form method="post" action="productos_gt.php" class="d-grid">
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
    
    <div class="fixed-btn-container d-lg-none">
        <a href="index.php" class="btn volver-btn">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="carrito.php" class="btn go-cart-btn">
            <i class="bi bi-cart-check"></i> Carrito
        </a>
    </div>
    
    <div class="d-none d-lg-block">
        <a href="carrito.php" class="btn go-cart-btn shadow">
            <i class="bi bi-cart-check"></i> Ir al carrito
        </a>
        <a href="index.php" class="btn volver-btn shadow">
            <i class="bi bi-arrow-left"></i> Volver al inicio
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar notificación de producto agregado
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('addedNotification');
            if (notification) {
                notification.classList.add('show');
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
                <div class="modal fade" id="imagenModal" tabindex="-1" aria-labelledby="imagenModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark">
      <div class="modal-body text-center">
        <img id="imagenModalSrc" src="" alt="Producto grande" style="max-width:100%;max-height:70vh;border-radius:18px;">
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">
          <i class="bi bi-x-lg"></i> Cerrar
        </button>
      </div>
    </div>
  </div>
</div>
<script>
function mostrarImagenModal(src) {
    document.getElementById('imagenModalSrc').src = src;
    var modal = new bootstrap.Modal(document.getElementById('imagenModal'));
    modal.show();
}
</script>