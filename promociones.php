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
        :root {
            --verde-mx: #006341;
            --rojo-mx: #ce1126;
            --blanco: #fff;
            --azul-gt: #0097d7;
            --amarillo-gt: #ffd600;
            --gris: #232526;
        }
        body {
            background: linear-gradient(135deg, var(--verde-mx) 0%, var(--blanco) 40%, var(--azul-gt) 100%);
            color: var(--gris);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--azul-gt) 100%) !important;
            border-bottom: 3px solid var(--amarillo-gt);
        }
        .navbar-brand {
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 2rem;
            text-shadow: 2px 2px 8px #fff, 0 0 2px var(--verde-mx);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-brand .mxgt-logo {
            display: inline-block;
            font-weight: 900;
            font-size: 2rem;
            letter-spacing: 1px;
            padding: 0 8px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #fff8;
        }
        .navbar-brand .mxgt-logo .mx {
            color: var(--rojo-mx);
            background: var(--rojo-mx);
            padding: 2px 8px;
            border-radius: 6px 0 0 6px;
            font-weight: bold;
            text-shadow: 1px 1px 2px #fff8;
        }
        .navbar-brand .mxgt-logo .y {
            color: var(--amarillo-gt);
            background: var(--amarillo-gt);
            padding: 2px 8px;
            font-weight: bold;
            text-shadow: 1px 1px 2px var(--rojo-mx);
        }
        .navbar-brand .mxgt-logo .gt {
            color: var(--azul-gt);
            background: var(--azul-gt);
            padding: 2px 8px;
            border-radius: 0 6px 6px 0;
            font-weight: bold;
            text-shadow: 1px 1px 2px #fff8;
        }
        .promo-badge {
            display: block;
            margin: 0 auto 0.5rem auto;
            background: linear-gradient(90deg, var(--rojo-mx) 0%, var(--amarillo-gt) 100%);
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
            box-shadow: 0 8px 32px rgba(0,151,215,0.13);
            background: linear-gradient(135deg, var(--blanco) 60%, var(--verde-mx) 100%);
            color: var(--azul-gt);
            min-height: 370px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid var(--amarillo-gt);
            position: relative;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 12px 36px rgba(0,151,215,0.22);
            border-color: var(--rojo-mx);
            background: linear-gradient(135deg, var(--azul-gt) 0%, var(--blanco) 100%);
            color: var(--gris);
        }
        .product-img {
            background: rgba(0,151,215,0.08);
            border-radius: 16px 16px 0 0;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: var(--rojo-mx);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--azul-gt);
            background: #fff;
        }
        .product-card h5 {
            font-weight: 700;
            color: inherit;
        }
        .product-card .text-muted {
            color: var(--verde-mx) !important;
        }
        .add-cart-btn {
            border-radius: 10px;
            font-weight: 600;
            background: linear-gradient(90deg, var(--amarillo-gt) 0%, var(--verde-mx) 100%);
            color: var(--gris);
            border: none;
            transition: background 0.2s, color 0.2s;
        }
        .add-cart-btn:disabled {
            background: #bfa14a55;
            color: #23252699;
            border: none;
        }
        .add-cart-btn:hover:not(:disabled) {
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--amarillo-gt) 100%);
            color: var(--rojo-mx);
        }
        .go-cart-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--amarillo-gt) 0%, var(--verde-mx) 100%);
            color: var(--gris);
            border: none;
        }
        .go-cart-btn:hover {
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--amarillo-gt) 100%);
            color: var(--rojo-mx);
        }
        .volver-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--azul-gt) 100%);
            color: var(--amarillo-gt);
            border: none;
        }
        .volver-btn:hover {
            background: linear-gradient(90deg, var(--rojo-mx) 0%, var(--verde-mx) 100%);
            color: var(--blanco);
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
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <span class="mxgt-logo">
                    <span class="mx">MX</span>
                    <span class="y">&amp;</span>
                    <span class="gt">GT</span>
                </span>
            </a>
            <a href="carrito.php" class="btn btn-outline-success position-relative">
                <i class="bi bi-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </span>
            </a>
        </div>
    </nav>
    <div class="container mt-5">
        <h1 class="mb-4 text-center" style="color:var(--verde-mx);font-weight:800;letter-spacing:1px;">
            Promociones <span style="color:var(--azul-gt);">2x1</span>
        </h1>
        <div class="row g-4">
            <?php foreach ($promos as $producto): ?>
                <?php
                    $precio_promocion = (isset($producto['precio']) && $producto['precio'] !== "") ? round($producto['precio'] * 1.65, 2) : "";
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card p-3">
                        <span class="promo-badge">2x1</span>
                        <div class="product-img mb-3">
                            <?php if (!empty($producto['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Promo" />
                            <?php else: ?>
                                <i class="bi bi-gift"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="text-center mb-2"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="text-center text-muted mb-3 fs-5">
                            <?php
                                if ($precio_promocion !== "") {
                                    echo "Q " . number_format($precio_promocion,2) . " <span style='font-size:0.9em;color:#888;text-decoration:line-through;'>Q " . number_format($producto['precio'],2) . "</span>";
                                } else {
                                    echo "<span class='text-secondary'>Precio</span>";
                                }
                            ?>
                        </p>
                        <form method="post" action="promociones.php" class="d-grid">
                            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                            <button type="submit" class="btn add-cart-btn" <?php echo ($precio_promocion === "") ? "disabled" : ""; ?>>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>