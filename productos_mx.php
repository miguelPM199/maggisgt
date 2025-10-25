<?php

session_start();

// Conexión a la base de datos (ajusta el puerto si es necesario)
$mysqli = new mysqli("178.128.67.133", "usrmaggisgt", "mipass", "maggisgt", 3306);
if ($mysqli->connect_errno) {
    die("Error de conexión a MySQL: " . $mysqli->connect_error);
}

// Agregar al carrito (PRG)
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Productos Mexicanos - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root{
            --bg-1: #eb72d9ff;
            --bg-2: #659ed6ff;
            --bg-3: #232526;
            --text: #e8eef1;
            --card-glass: rgba(255,255,255,0.06);
            --accent: #e7c873;
            --muted: rgba(232,238,241,0.6);
            --success: #3bb54a;
        }

        html,body{height:100%}
        body {
            margin:0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 50%, var(--bg-3) 100%);
            color: var(--text);
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
            padding-top: 86px;
        }

        .main-wrap {
            max-width:1200px;
            margin:0 auto 80px;
            padding:28px;
            backdrop-filter: blur(6px) saturate(1.05);
        }

        .navbar {
            height:72px;
            background: linear-gradient(90deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
            border-bottom:1px solid rgba(255,255,255,0.04);
            backdrop-filter: blur(6px);
            z-index:1050;
        }
        .navbar-brand {
            font-weight:800;
            color:var(--text) !important;
            letter-spacing:1.2px;
            display:flex;
            align-items:center;
            gap:.6rem;
            font-size:1.25rem;
        }
        .logo-pill {
            display:inline-flex;
            align-items:center;
            gap:.3rem;
            padding:.25rem .6rem;
            border-radius:8px;
            background: linear-gradient(90deg, rgba(0,0,0,0.15), rgba(255,255,255,0.03));
            font-weight:900;
            color:var(--accent);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            transform: translateY(1cm); /* baja el logo 1 cm */
        }

        /* baja el botón de carrito 1 cm */
        .navbar .ms-auto .btn {
            transform: translateY(1cm);
            will-change: transform;
        }

        /* opcional: revertir en pantallas muy pequeñas si causa overflow */
        @media (max-width: 350px) {
            .logo-pill,
            .navbar .ms-auto .btn {
                transform: translateY(0.6cm);
            }
        }

        .hero-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
            border-radius:14px;
            padding:18px 20px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.35);
            border:1px solid rgba(255,255,255,0.04);
            margin-bottom:22px;
        }
        .hero-title { margin:0; font-weight:800; font-size:1.35rem; letter-spacing:.6px; }
        .hero-sub { margin:6px 0 0; color:var(--muted); font-size:.95rem; }

        .row-products { gap:1.4rem; }

        .product-card {
            border-radius:16px;
            overflow:hidden;
            padding:18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border:1px solid rgba(255,255,255,0.04);
            box-shadow: 0 10px 30px rgba(0,0,0,0.45);
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            min-height:360px;
            transition: transform .22s ease, box-shadow .22s ease;
        }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 22px 60px rgba(0,0,0,0.55); }

        .product-img {
            height:170px;
            border-radius:12px;
            overflow:hidden;
            display:flex;
            align-items:center;
            justify-content:center;
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border:1px solid rgba(255,255,255,0.03);
            margin-bottom:14px;
        }
        .product-img img { width:100%; height:100%; object-fit:cover; display:block; }

        .product-name { font-weight:800; font-size:1.05rem; margin:0 0 6px; color:var(--text); }
        .product-price { font-weight:700; color:var(--accent); font-size:1.05rem; white-space:nowrap; }
        .product-desc { color:var(--muted); font-size:.92rem; margin-top:8px; min-height:42px; }

        .card-footer { margin-top:12px; display:flex; gap:8px; align-items:center; justify-content:space-between; }

        .add-cart-btn {
            background: linear-gradient(90deg,var(--accent), #f09819);
            color:#111;
            font-weight:800;
            border-radius:10px;
            padding:.5rem .8rem;
            border:none;
            box-shadow: 0 6px 18px rgba(231,200,115,0.12);
        }
        .add-cart-btn:disabled { opacity:.55; filter:grayscale(.3); }
        .view-btn {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.06);
            color:var(--text);
            padding:.38rem .6rem;
            border-radius:8px;
        }

        .float-actions { position: fixed; right:24px; bottom:24px; display:flex; flex-direction:column; gap:10px; z-index:1200; }
        .notification {
            position: fixed;
            top:96px;
            right:20px;
            background: rgba(0,0,0,0.6);
            color: var(--text);
            padding:12px 18px;
            border-radius:10px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
            z-index:1300;
            transform: translateX(120%);
            transition: transform .32s ease;
            border:1px solid rgba(255,255,255,0.04);
        }
        .notification.show { transform: translateX(0); }

        .modal-content { background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); color:var(--text); border-radius:14px; border:1px solid rgba(255,255,255,0.04); }
        .modal-body img { border-radius:12px; max-width:100%; height:auto; display:block; margin:0 auto; }

        @media (max-width:991px){
            .product-img { height:140px; }
            body { padding-top:96px; }
            .main-wrap { padding:16px; }
            .float-actions { display:none; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container main-wrap">
            <a class="navbar-brand" href="index.php">
                <span class="logo-pill"><i class="bi bi-basket2"></i>&nbsp;MaggiSGT</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="carrito.php" class="btn btn-sm view-btn">
                    <i class="bi bi-cart"></i>
                    <span class="badge bg-danger ms-2"><?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?></span>
                </a>
            </div>
        </div>
    </nav>

    <main class="main-wrap">
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="hero-title">Productos Mexicanos</h2>
                    <p class="hero-sub">Sabores auténticos de México, seleccionados y listos para tu mesa.</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Total en carrito</small>
                    <div style="font-weight:800;font-size:1.05rem;"><?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?> ítems</div>
                </div>
            </div>
        </div>

        <?php if (isset($showNotification) && $showNotification): ?>
            <div class="notification" id="addedNotification"><i class="bi bi-check2-circle"></i> &nbsp; Producto agregado al carrito</div>
        <?php endif; ?>

        <div class="row row-products row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 mt-3">
            <?php foreach ($productos as $producto): ?>
                <div class="col">
                    <div class="product-card">
                        <div class="product-img" onclick="mostrarImagenModal('<?php echo htmlspecialchars($producto['imagen']); ?>')" style="cursor:pointer;">
                            <?php if ($producto['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Producto" />
                            <?php else: ?>
                                <div style="color:var(--muted); font-size:48px;"><i class="bi bi-image"></i></div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h5 class="product-name"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="product-price">
                                    <?php echo $producto['precio'] !== "" ? "Q&nbsp;" . number_format($producto['precio'],2) : "<span class='text-muted'>Próximamente</span>"; ?>
                                </div>
                                <div class="text-end" style="max-width:60%;">
                                    <small class="product-desc"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></small>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <form method="post" action="productos_mx.php" class="d-grid" style="flex:1">
                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" class="add-cart-btn w-100" <?php echo $producto['precio'] === "" ? "disabled" : ""; ?>>
                                    <i class="bi bi-cart-plus"></i> Agregar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <div class="float-actions d-none d-lg-flex">
        <a href="carrito.php" class="btn add-cart-btn"><i class="bi bi-cart-check"></i>&nbsp; Ver carrito</a>
        <a href="index.php" class="btn view-btn"><i class="bi bi-arrow-left"></i>&nbsp; Inicio</a>
    </div>

    <!-- Modal para imagen grande -->
    <div class="modal fade" id="imagenModal" tabindex="-1" aria-labelledby="imagenModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-body text-center">
            <img id="imagenModalSrc" src="" alt="Producto grande" style="max-width:100%;max-height:70vh;">
          </div>
          <div class="modal-footer border-0 justify-content-center">
            <button type="button" class="btn view-btn" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

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

        function mostrarImagenModal(src) {
            if (!src) return;
            document.getElementById('imagenModalSrc').src = src;
            var modal = new bootstrap.Modal(document.getElementById('imagenModal'));
            modal.show();
        }
    </script>
</body>
</html>
