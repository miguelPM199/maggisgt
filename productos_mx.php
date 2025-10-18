<?php


session_start();

// Conexión a la base de datos (ajusta el puerto si es necesario)
$mysqli = new mysqli("localhost", "root", "", "maggisgt", 3307);
if ($mysqli->connect_errno) {
    die("Error de conexión a MySQL: " . $mysqli->connect_error);
}

// Agregar al carrito (corregido: busca el producto en la BD y usa PRG)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = intval($_POST['producto_id']);
    $stmt = $mysqli->prepare("SELECT * FROM productos_mx WHERE id=?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prod = $result->fetch_assoc();
    $stmt->close();
    if ($prod && $prod['precio'] !== "") {
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
                "precio" => $prod['precio'],
                "cantidad" => 1
            ];
        }
    }
    header("Location: productos_mx.php?added=1");
    exit;
}
if (isset($_GET['added'])) {
    $showNotification = true;
}

// Obtener productos mexicanos desde la base de datos
$productos = $mysqli->query("SELECT * FROM productos_mx")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Mexicanos - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --verde-mx: #006341;
            --blanco-mx: #fff;
            --rojo-mx: #ce1126;
            --dorado-mx: #e7c873;
            --gris-mx: #232526;
            --amarillo-mx: #ffd600;
        }
        body {
            background: linear-gradient(135deg, var(--verde-mx) 0%, var(--blanco-mx) 50%, var(--rojo-mx) 100%);
            color: var(--gris-mx);
            padding-top: 80px;
            min-height: 100vh;
            font-family: 'Segoe UI', 'Arial', sans-serif;
        }
        .navbar {
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--blanco-mx) 50%, var(--rojo-mx) 100%) !important;
            border-bottom: 3px solid var(--dorado-mx);
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
        .navbar-brand .maggi-mx-logo {
            display: inline-block;
            font-weight: 900;
            font-size: 2rem;
            letter-spacing: 1px;
            padding: 0 8px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(8, 27, 243, 0.53);
        }
        .navbar-brand .maggi-mx-logo .maggi {
            color: var(--rojo-mx);
            background: var(--rojo-mx);
            padding: 2px 8px;
            border-radius: 6px 0 0 6px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(21, 5, 248, 0.53);
        }
        .navbar-brand .maggi-mx-logo .s {
            color: #fff;
            background: #fff;
            padding: 2px 8px;
            font-weight: bold;
            text-shadow: 1px 1px 2px var(--rojo-mx);
        }
        .navbar-brand .maggi-mx-logo .mx {
            color: var(--verde-mx);
            background: var(--verde-mx);
            padding: 2px 8px;
            border-radius: 0 6px 6px 0;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(40, 3, 249, 0.53);
        }
        .navbar .btn-outline-warning {
            border-color: var(--amarillo-mx);
            color: var(--amarillo-mx);
        }
        .navbar .btn-outline-warning:hover {
            background: var(--amarillo-mx);
            color: var(--verde-mx);
        }
        .page-title {
            color: var(--rojo-mx);
            font-weight: 900;
            letter-spacing: 2px;
            text-shadow: 2px 2px 8px #fff, 0 0 2px var(--verde-mx);
            padding-bottom: 10px;
            border-bottom: 3px solid var(--dorado-mx);
            margin-bottom: 30px;
            background: linear-gradient(90deg, var(--blanco-mx) 60%, var(--rojo-mx) 100%);
            border-radius: 0 0 30px 30px;
        }
        .product-card {
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(206, 17, 38, 0.18);
            background: linear-gradient(135deg, var(--blanco-mx) 60%, var(--rojo-mx) 100%);
            color: var(--verde-mx);
            min-height: 370px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid var(--dorado-mx);
            position: relative;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 12px 36px rgba(206, 17, 38, 0.25);
            border-color: var(--verde-mx);
            background: linear-gradient(135deg, var(--verde-mx) 0%, var(--blanco-mx) 100%);
            color: var(--gris-mx);
        }
        .product-img {
            background: rgba(206, 17, 38, 0.08);
            border-radius: 16px 16px 0 0;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--rojo-mx);
            margin-bottom: 1rem;
            overflow: hidden;
            border-bottom: 2px solid var(--dorado-mx);
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--rojo-mx);
            background: #fff;
        }
        .product-card:hover .product-img {
            color: var(--verde-mx);
            background: rgba(0, 99, 65, 0.15);
            border-bottom: 2px solid var(--verde-mx);
        }
        .product-card h5 {
            font-weight: 800;
            color: inherit;
            text-shadow: 1px 1px 2px #fff;
        }
        .product-card .text-muted {
            color: var(--rojo-mx) !important;
        }
        .product-card:hover .text-muted {
            color: var(--verde-mx) !important;
        }
        .add-cart-btn {
            border-radius: 10px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--amarillo-mx) 0%, var(--verde-mx) 100%);
            color: var(--gris-mx);
            border: none;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px rgba(206, 17, 38, 0.10);
        }
        .add-cart-btn:disabled {
            background: #bfa14a55;
            color: #23252699;
            border: none;
        }
        .add-cart-btn:hover:not(:disabled) {
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--amarillo-mx) 100%);
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
            background: linear-gradient(90deg, var(--amarillo-mx) 0%, var(--verde-mx) 100%);
            color: var(--gris-mx);
            border: none;
            padding: 12px 25px;
            box-shadow: 0 4px 15px rgba(206, 17, 38, 0.15);
        }
        .go-cart-btn:hover {
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--amarillo-mx) 100%);
            color: var(--rojo-mx);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(206, 17, 38, 0.25);
        }
        .volver-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--verde-mx) 0%, var(--rojo-mx) 100%);
            color: var(--blanco-mx);
            border: none;
            padding: 12px 25px;
            box-shadow: 0 4px 15px rgba(206, 17, 38, 0.15);
        }
        .volver-btn:hover {
            background: linear-gradient(90deg, var(--rojo-mx) 0%, var(--verde-mx) 100%);
            color: var(--amarillo-mx);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(206, 17, 38, 0.25);
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
                background: var(--blanco-mx);
                padding: 15px;
                z-index: 1000;
                box-shadow: 0 -4px 10px rgba(206, 17, 38, 0.15);
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
            background: var(--rojo-mx);
            color: var(--blanco-mx);
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(206, 17, 38, 0.18);
            z-index: 1050;
            font-weight: 700;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            border: 2px solid var(--verde-mx);
        }
        .notification.show {
            transform: translateX(0);
        }
        /* Cinta bandera México */
        .bandera-mx {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 18px;
            z-index: 2000;
            display: flex;
        }
        .bandera-mx .franja {
            flex: 1;
            height: 100%;
        }
        .bandera-mx .verde { background: var(--verde-mx);}
        .bandera-mx .blanco { background: var(--blanco-mx);}
        .bandera-mx .rojo { background: var(--rojo-mx);}
    </style>
</head>
<body>
    <div class="bandera-mx">
        <div class="franja verde"></div>
        <div class="franja blanco"></div>
        <div class="franja rojo"></div>
    </div>
    <?php if (isset($showNotification) && $showNotification): ?>
        <div class="notification" id="addedNotification"><i class="bi bi-cart-check"></i> Producto agregado al carrito</div>
    <?php endif; ?>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <span class="maggi-mx-logo">
                    <span class="maggi">Maggi</span><span class="s">S</span><span class="mx">MX</span>
                </span>
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
            <i class="bi bi-basket2"></i> Productos Mexicanos
        </h1>
        <div class="row g-4">
            <?php foreach ($productos as $producto): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card p-3">
                        <div class="product-img mb-3" onclick="mostrarImagenModal('<?php echo htmlspecialchars($producto['imagen']); ?>')" style="cursor:pointer;">
                            <?php if ($producto['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Producto" />
                            <?php else: ?>
                                <i class="bi bi-image"></i>
                            <?php endif; ?>
                        </div>
                        <h5 class="text-center mb-2"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="text-center text-muted mb-3 fs-5">
                            <?php echo $producto['precio'] !== "" ? "Q " . number_format($producto['precio'],2) : "<span class='text-secondary'>Próximamente</span>"; ?>
                        </p>
                        <form method="post" action="productos_mx.php" class="d-grid">
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
            }
        });
    </script>
    <!-- Modal para imagen grande -->
    <div class="modal fade" id="imagenModal" tabindex="-1" aria-labelledby="imagenModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-white">
          <div class="modal-body text-center">
            <img id="imagenModalSrc" src="" alt="Producto grande" style="max-width:100%;max-height:70vh;border-radius:18px;box-shadow:0 4px 24px #ce1126;">
          </div>
          <div class="modal-footer border-0 justify-content-center">
            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
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
</body>
</html>