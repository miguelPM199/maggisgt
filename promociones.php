<?php

session_start();

// Conexión a la base de datos
$mysqli = new mysqli("localhost", "root", "", "maggisgt", 3307);
if ($mysqli->connect_errno) {
    die("Error de conexión a MySQL: " . $mysqli->connect_error);
}

// Obtener productos en promoción desde la tabla real
$promos = $mysqli->query("SELECT * FROM promociones")->fetch_all(MYSQLI_ASSOC);

// Agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = intval($_POST['producto_id']);
    // Buscar el producto en la tabla promociones
    $stmt = $mysqli->prepare("SELECT * FROM promociones WHERE id=?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    // Validación segura para evitar error si $prod es null o no tiene precio
    if (is_array($prod) && isset($prod['precio']) && $prod['precio'] !== "") {
        // Calcular precio promocional (65% más)
        $precio_promocion = round($prod['precio'] * 1.65, 2);
        $encontrado = false;
        if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
        foreach ($_SESSION['carrito'] as &$item) {
            if ($item['id'] == $producto_id) {
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
                "precio" => $precio_promocion,
                "cantidad" => 1
            ];
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
    <style>
        :root{
            --bg-1: #eb72d9ff;
            --bg-2: #659ed6ff;
            --bg-3: #232526;
            --text: #e8eef1;
            --accent: #e7c873;
            --muted: rgba(232,238,241,0.6);
            --success: #3bb54a;
            --danger: #e30613;
        }

        html,body{height:100%}
        body {
            margin:0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 50%, var(--bg-3) 100%);
            color: var(--text);
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
            padding-top: 86px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .navbar {
            height:72px;
            background: linear-gradient(90deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border-bottom: 1px solid rgba(255,255,255,0.04);
            backdrop-filter: blur(6px);
            z-index:1050;
        }
        .navbar-brand {
            font-weight:800;
            color:var(--accent) !important;
            display:flex;
            align-items:center;
            gap:.6rem;
            letter-spacing:1px;
            font-size:1.5rem;
        }
        .mxgt-logo {
            display:inline-flex;
            align-items:center;
            gap:.3rem;
            padding:.2rem .6rem;
            border-radius:8px;
            background: linear-gradient(90deg, rgba(0,0,0,0.12), rgba(255,255,255,0.02));
            color: var(--accent);
            font-weight:900;
            box-shadow: 0 8px 24px rgba(0,0,0,0.28);
        }

        .hero-title {
            margin-top: 18px;
            margin-bottom: 8px;
            font-weight:800;
            font-size:1.6rem;
            letter-spacing:.6px;
            color: var(--text);
            text-shadow: 0 2px 18px rgba(0,0,0,0.35);
        }
        .hero-sub {
            color: var(--muted);
            margin-bottom: 20px;
        }

        /* Cards use same visual language as productos pages */
        .product-card {
            border-radius: 16px;
            padding: 18px;
            background: linear-gradient(135deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            color: var(--bg-3);
            min-height: 360px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            box-shadow: 0 12px 40px rgba(0,0,0,0.36);
            border: 1px solid rgba(255,255,255,0.03);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 26px 70px rgba(0,0,0,0.55);
        }

        .promo-badge {
            display:inline-block;
            background: linear-gradient(90deg, var(--danger), var(--accent));
            color:#fff;
            font-weight:700;
            padding:.25rem .8rem;
            border-radius:20px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.28);
            margin-bottom:10px;
            font-size:1rem;
        }

        .product-img {
            height:150px;
            border-radius:12px;
            overflow:hidden;
            display:flex;
            align-items:center;
            justify-content:center;
            margin-bottom:12px;
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border: 1px solid rgba(255,255,255,0.03);
        }
        .product-img img { width:100%; height:100%; object-fit:cover; display:block; }

        .product-name {
            font-weight:800;
            color:var(--text);
            margin:0 0 6px;
            font-size:1.05rem;
        }
        .product-price {
            font-weight:800;
            color:var(--accent);
            font-size:1.05rem;
            white-space:nowrap;
        }
        .product-desc {
            color:var(--muted);
            font-size:.95rem;
            margin-top:8px;
            min-height:44px;
        }

        .add-cart-btn {
            background: linear-gradient(90deg,var(--accent), #f09819);
            color:#111;
            font-weight:800;
            border-radius:10px;
            padding:.5rem .8rem;
            border:none;
            box-shadow: 0 8px 24px rgba(231,200,115,0.12);
        }
        .add-cart-btn:disabled {
            opacity:.6;
            filter:grayscale(.25);
        }

        .go-cart-btn, .volver-btn {
            border-radius: 50px;
            padding: .6rem 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.28);
        }
        .go-cart-btn {
            position: fixed;
            right: 24px;
            bottom: 24px;
            background: linear-gradient(90deg, var(--accent), #f09819);
            color:#111;
            z-index:1200;
        }
        .volver-btn {
            position: fixed;
            left: 24px;
            bottom: 24px;
            background: rgba(255,255,255,0.06);
            color: var(--text);
            border: 1px solid rgba(255,255,255,0.04);
            z-index:1200;
        }

        .notification {
            position: fixed;
            top: 96px;
            right: 20px;
            background: rgba(0,0,0,0.6);
            color: var(--text);
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            z-index:1300;
            transform: translateX(120%);
            transition: transform .32s ease;
            border:1px solid rgba(255,255,255,0.04);
        }
        .notification.show { transform: translateX(0); }

        @media (max-width: 991px) {
            .product-img { height:140px; }
            body { padding-top:96px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="mxgt-logo"><span class="mx">MX</span>&nbsp;<span class="gt">GT</span></span>
            </a>
            <a href="carrito.php" class="btn btn-sm go-cart-btn position-relative">
                <i class="bi bi-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </span>
            </a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="hero-title">Promociones 2x1</h1>
        <p class="hero-sub">Ofertas especiales seleccionadas para ti — aprovecha antes que se acaben.</p>

        <?php if (isset($showNotification) && $showNotification): ?>
            <div class="notification" id="addedNotification"><i class="bi bi-check2-circle"></i> &nbsp; Producto agregado al carrito</div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($promos as $producto): ?>
                <?php
                    $precio_promocion = (isset($producto['precio']) && $producto['precio'] !== "") ? round($producto['precio'] * 1.65, 2) : "";
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div>
                            <span class="promo-badge">2x1</span>
                            <div class="product-img mb-3">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Promo" />
                                <?php else: ?>
                                    <i class="bi bi-gift" style="font-size:36px;color:var(--muted);"></i>
                                <?php endif; ?>
                            </div>
                            <h5 class="product-name text-center"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                            <p class="text-center mb-2">
                                <?php
                                    if ($precio_promocion !== "") {
                                        echo "<span class='product-price'>Q&nbsp;" . number_format($precio_promocion,2) . "</span> &nbsp; <span style='font-size:0.85rem;color:rgba(232,238,241,0.5);text-decoration:line-through;'>Q " . number_format($producto['precio'],2) . "</span>";
                                    } else {
                                        echo "<span class='text-muted'>Precio</span>";
                                    }
                                ?>
                            </p>
                            <div class="product-desc text-center"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></div>
                        </div>

                        <div class="mt-3">
                            <form method="post" action="promociones.php" class="d-grid">
                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" class="add-cart-btn" <?php echo ($precio_promocion === "") ? "disabled" : ""; ?>>
                                    <i class="bi bi-cart-plus"></i> Agregar al carrito
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <a href="index.php" class="volver-btn"> <i class="bi bi-arrow-left"></i> Volver al inicio</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('addedNotification');
            if (notification) {
                setTimeout(() => notification.classList.add('show'), 80);
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(()=> notification.remove(), 350);
                }, 3000);
            }
        });
    </script>
</body>
</html>
