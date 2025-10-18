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
    $stmt = $mysqli->prepare("SELECT * FROM productos_gt WHERE id=?");
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
    header("Location: productos_gt.php?added=1");
    exit;
}
if (isset($_GET['added'])) {
    $showNotification = true;
}

// Obtener productos guatemaltecos desde la base de datos
$productos = $mysqli->query("SELECT * FROM productos_gt")->fetch_all(MYSQLI_ASSOC);
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
            --azul-gt: #0097d7;
            --azul-oscuro-gt: #005fa3;
            --blanco-gt: #fff;
            --amarillo-gt: #ffd600;
            --verde-gt: #3bb54a;
            --gris-gt: #232526;
            --dorado-gt: #e7c873;
            --rojo-maggi: #e30613;
        }

        body {
            background: linear-gradient(135deg, var(--azul-gt) 0%, var(--blanco-gt) 100%);
            color: var(--gris-gt);
            padding-top: 80px;
            min-height: 100vh;
            font-family: 'Segoe UI', 'Arial', sans-serif;
        }

        .navbar {
            background: linear-gradient(90deg, var(--azul-oscuro-gt) 0%, var(--azul-gt) 100%) !important;
            border-bottom: 3px solid var(--amarillo-gt);
        }

        .navbar-brand {
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 2rem;
            text-shadow: 2px 2px 8px #fff, 0 0 2px var(--azul-oscuro-gt);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar-brand .maggi-gt-logo {
            display: inline-block;
            font-weight: 900;
            font-size: 2rem;
            letter-spacing: 1px;
            padding: 0 8px;
            border-radius: 8px;
            box-shadow: 0 2px 8px #fff8;
        }
        .navbar-brand .maggi-gt-logo .maggi {
            color: var(--rojo-maggi);
            background: var(--rojo-maggi);
            padding: 2px 8px;
            border-radius: 6px 0 0 6px;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(5, 0, 0, 0.53);
        }
        .navbar-brand .maggi-gt-logo .s {
            color: #d45555ff;
            background: #fff;
            padding: 2px 8px;
            font-weight: bold;
            text-shadow: 1px 1px 2px #090909ff;
        }
        .navbar-brand .maggi-gt-logo .gt {
            color: var(--verde-gt);
            background: var(--verde-gt);
            padding: 2px 8px;
            border-radius: 0 6px 6px 0;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(5, 4, 4, 0.53);
        }

        .navbar .btn-outline-warning {
            border-color: var(--amarillo-gt);
            color: var(--amarillo-gt);
        }
        .navbar .btn-outline-warning:hover {
            background: var(--amarillo-gt);
            color: var(--azul-oscuro-gt);
        }

        .page-title {
            color: var(--azul-oscuro-gt);
            font-weight: 900;
            letter-spacing: 2px;
            text-shadow: 2px 2px 8px #fff, 0 0 2px var(--azul-gt);
            padding-bottom: 10px;
            border-bottom: 3px solid var(--amarillo-gt);
            margin-bottom: 30px;
            background: linear-gradient(90deg, var(--blanco-gt) 60%, var(--azul-gt) 100%);
            border-radius: 0 0 30px 30px;
        }

        .product-card {
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(0, 151, 215, 0.18);
            background: linear-gradient(135deg, var(--blanco-gt) 60%, var(--azul-gt) 100%);
            color: var(--azul-oscuro-gt);
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
            box-shadow: 0 12px 36px rgba(0, 151, 215, 0.25);
            border-color: var(--verde-gt);
            background: linear-gradient(135deg, var(--azul-gt) 0%, var(--blanco-gt) 100%);
            color: var(--gris-gt);
        }

        .product-img {
            background: rgba(0, 151, 215, 0.08);
            border-radius: 16px 16px 0 0;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--azul-gt);
            margin-bottom: 1rem;
            overflow: hidden;
            border-bottom: 2px solid var(--amarillo-gt);
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--azul-gt);
            background: #fff;
        }

        .product-card:hover .product-img {
            color: var(--verde-gt);
            background: rgba(59, 181, 74, 0.15);
            border-bottom: 2px solid var(--verde-gt);
        }

        .product-card h5 {
            font-weight: 800;
            color: inherit;
            text-shadow: 1px 1px 2px #fff;
        }

        .product-card .text-muted {
            color: var(--azul-oscuro-gt) !important;
        }

        .product-card:hover .text-muted {
            color: var(--verde-gt) !important;
        }

        .add-cart-btn {
            border-radius: 10px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--amarillo-gt) 0%, var(--verde-gt) 100%);
            color: var(--gris-gt);
            border: none;
            transition: background 0.2s, color 0.2s;
            box-shadow: 0 2px 8px rgba(0, 151, 215, 0.10);
        }

        .add-cart-btn:disabled {
            background: #bfa14a55;
            color: #23252699;
            border: none;
        }

        .add-cart-btn:hover:not(:disabled) {
            background: linear-gradient(90deg, var(--verde-gt) 0%, var(--amarillo-gt) 100%);
            color: var(--azul-oscuro-gt);
        }

        .go-cart-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--amarillo-gt) 0%, var(--verde-gt) 100%);
            color: var(--gris-gt);
            border: none;
            padding: 12px 25px;
            box-shadow: 0 4px 15px rgba(0, 151, 215, 0.15);
        }

        .go-cart-btn:hover {
            background: linear-gradient(90deg, var(--verde-gt) 0%, var(--amarillo-gt) 100%);
            color: var(--azul-oscuro-gt);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 151, 215, 0.25);
        }

        .volver-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--azul-gt) 0%, var(--azul-oscuro-gt) 100%);
            color: var(--amarillo-gt);
            border: none;
            padding: 12px 25px;
            box-shadow: 0 4px 15px rgba(0, 151, 215, 0.15);
        }

        .volver-btn:hover {
            background: linear-gradient(90deg, var(--verde-gt) 0%, var(--azul-gt) 100%);
            color: var(--blanco-gt);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 151, 215, 0.25);
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
                background: var(--azul-gt);
                padding: 15px;
                z-index: 1000;
                box-shadow: 0 -4px 10px rgba(0, 151, 215, 0.15);
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
            background: var(--amarillo-gt);
            color: var(--azul-oscuro-gt);
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 151, 215, 0.18);
            z-index: 1050;
            font-weight: 700;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
            border: 2px solid var(--verde-gt);
        }

        .notification.show {
            transform: translateX(0);
        }

        /* Cinta bandera Guatemala */
        .bandera-gt {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 18px;
            z-index: 2000;
            display: flex;
        }
        .bandera-gt .franja {
            flex: 1;
            height: 100%;
        }
        .bandera-gt .azul { background: var(--azul-gt);}
        .bandera-gt .blanco { background: var(--blanco-gt);}
        .bandera-gt .amarillo {
            background: repeating-linear-gradient(
                45deg,
                var(--amarillo-gt),
                var(--amarillo-gt) 6px,
                var(--blanco-gt) 6px,
                var(--blanco-gt) 12px
            );
        }
    </style>
</head>
<body>
    <div class="bandera-gt">
        <div class="franja azul"></div>
        <div class="franja blanco"></div>
        <div class="franja azul"></div>
    </div>
    <?php if (isset($showNotification) && $showNotification): ?>
        <div class="notification" id="addedNotification"><i class="bi bi-cart-check"></i> Producto agregado al carrito</div>
    <?php endif; ?>
    
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <span class="maggi-gt-logo">
                    <span class="maggi">Maggi</span><span class="s">S</span><span class="gt">GT</span>
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
            <i class="bi bi-basket2"></i> Productos Guatemaltecos
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
            }
        });
    </script>
    <!-- Modal para imagen grande -->
    <div class="modal fade" id="imagenModal" tabindex="-1" aria-labelledby="imagenModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-white">
          <div class="modal-body text-center">
            <img id="imagenModalSrc" src="" alt="Producto grande" style="max-width:100%;max-height:70vh;border-radius:18px;box-shadow:0 4px 24px #0097d7;">
          </div>
          <div class="modal-footer border-0 justify-content-center">
            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
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