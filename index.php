<?php

session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MaggiSGT — Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root{
            --accent-1: #e7c873;
            --accent-2: #f09819;
            --bg-dark-1: #0f4eecff;
            --bg-dark-2: #0f4eecff;
            --panel: rgba(19, 232, 97, 0.04);
            --glass: rgba(197, 42, 42, 0.06);
        }
        html,body{height:100%}
        body {
            margin:0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: linear-gradient(135deg, #eb72d9ff 0%, #659ed6ff 50%, #232526 100%);
            color: #e8eef1;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }
        .navbar {
            background: linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border-bottom: 1px solid rgba(255,255,255,0.03);
            backdrop-filter: blur(6px);
        }
        .navbar-brand {
            font-weight: 900;
            color: var(--accent-1) !important;
            letter-spacing: 1.4px;
            font-size: 1.5rem;
        }
        .btn-outline-* { border-width:2px; }
        .btn-outline-primary, .btn-outline-success {
            color: var(--accent-1);
            border-color: rgba(48, 235, 10, 0.18);
            background: transparent;
        }
        .hero {
            padding: 80px 0 36px;
            background: linear-gradient(180deg, rgba(231,200,115,0.02), transparent 40%);
        }
        .hero-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(2,6,23,0.6);
            border: 1px solid rgba(231,200,115,0.06);
        }
        .hero-title {
            color: #fff;
            font-weight:800;
            letter-spacing:1px;
        }
        .carousel-inner img {
            border-radius: 12px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.6);
            width:100%;
            height: 18cm;
            object-fit: cover;
        }
        @media (max-width: 575.98px) {
            .carousel-inner img { height: 8cm; }
            .hero { padding-top: 60px; }
        }
        .feature-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border-radius: 12px;
            padding:16px;
            height:100%;
            display:flex;
            flex-direction:column;
            gap:8px;
            border:1px solid rgba(214, 36, 36, 0.02);
        }
        .feature-card .icon { font-size:26px; }
        .cta {
            background: linear-gradient(90deg,#fff8ed 0%, #fff3e8 100%);
            color:#2b2b2b;
            border-radius:12px;
            padding:18px;
            box-shadow: 0 10px 36px rgba(240,150,40,0.06);
        }
        footer {
            background: linear-gradient(90deg,#0b0c0d 0%, #141516 100%);
            color: rgba(255,255,255,0.65);
            text-align:center;
            padding:18px 0;
            margin-top:40px;
            font-weight:600;
        }
        .badge-cart {
            background: linear-gradient(90deg,#ff5f5f,#ffb86b);
            color:#111;
            font-weight:700;
            border-radius:999px;
            padding:4px 8px;
            font-size:0.85rem;
        }
        .showcase-grid { display:grid; grid-template-columns: repeat(3,1fr); gap:20px; }
        @media (max-width:991px) { .showcase-grid{grid-template-columns:1fr} }
        .glass {
            background: rgba(255,255,255,0.03);
            border-radius:12px;
            padding:10px;
            border: 1px solid rgba(255,255,255,0.02);
        }
        .dropdown-menu.login-dropdown {
            min-width: 320px;
            border-radius:12px;
            background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
            border:1px solid rgba(218, 20, 20, 0.02);
            box-shadow: 0 10px 30px rgba(2,6,23,0.5);
        }
        .form-text.text-warning { color: #f2b64a !important; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">MaggiSGT</a>
            <div class="d-flex ms-auto align-items-center gap-3">
                <a href="carrito.php" class="btn btn-sm btn-outline-success position-relative d-flex align-items-center">
                    <i class="bi bi-cart me-2"></i>
                    <span class="me-1">Carrito</span>
                    <span class="badge-cart ms-2"><?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?></span>
                </a>

                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" id="loginDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> Admin
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-4 login-dropdown" aria-labelledby="loginDropdown">
                        <?php
                            $usuario_temp = $_SESSION["usuario_temp"] ?? "";
                            if (isset($_SESSION["login_error"])): ?>
                            <div class="alert alert-danger py-1 mb-2">
                                <?php
                                    echo htmlspecialchars($_SESSION["login_error"]);
                                    unset($_SESSION["login_error"]);
                                ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="login.php" novalidate>
                            <div class="mb-2">
                                <label for="usuario" class="form-label small mb-1">Usuario</label>
                                <input type="text" class="form-control form-control-sm" id="usuario" name="usuario" required value="<?php echo htmlspecialchars($usuario_temp); ?>">
                            </div>
                            <div class="mb-2">
                                <label for="contrasena" class="form-label small mb-1">Contraseña</label>
                                <input type="password" class="form-control form-control-sm" id="contrasena" name="contrasena" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                            <div class="form-text mt-2 text-warning text-center">
                                Solo para administrador
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <header class="hero">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="hero-card">
                        <h1 class="hero-title display-6">MaggiSGT — Productos auténticos de Guatemala y México</h1>
                        <p class="lead text-white mb-3">Encuentra sabores seleccionados, promociones y envíos confiables. Navega por nuestras categorías o pide tu link de pago seguro.</p>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="productos_gt.php" class="btn btn-lg" style="background:linear-gradient(90deg,var(--accent-1),var(--accent-2));color:#111;font-weight:800;border-radius:10px;">Productos GT</a>
                            <a href="productos_mx.php" class="btn btn-outline-success btn-lg">Productos MX</a>
                            <a href="promociones.php" class="btn btn-outline-primary btn-lg">Promociones 2x1</a>
                        </div>

                        <div class="mt-4 glass">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <strong class="text-white">Envío rápido</strong>
                                    <div class="text-white small">Logística optimizada a tu ciudad.</div>
                                </div>
                                <div class="col-md-6">
                                    <strong class="text-white">Pago seguro</strong>
                                    <div class="text-white small">Link de pago y opción</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="hero-card">
                        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators mb-3">
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            </div>
                            <div class="carousel-inner rounded">
                                <?php
                                $imgs = ['1ra.jpeg', 'maggisgt.jpeg', '2dos.jpeg'];
                                $first = true;
                                foreach ($imgs as $file) {
                                    $diskPath = __DIR__ . '/assets/img/' . $file;
                                    $webPath  = 'assets/img/' . $file;
                                    if (!file_exists($diskPath)) {
                                        $webPath = 'https://via.placeholder.com/1200x500?text=Imagen+no+disponible';
                                    }
                                    echo '<div class="carousel-item' . ($first ? ' active' : '') . '">';
                                    echo '<img src="' . htmlspecialchars($webPath) . '" class="d-block w-100" alt="' . htmlspecialchars($file) . '">';
                                    echo '</div>';
                                    $first = false;
                                }
                                ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Showcase -->
    <main class="container mt-5">
        <section class="showcase-grid mb-4">
            <div class="feature-card">
                <div class="icon">🍃</div>
                <h5 class="card-title">Calidad Garantizada</h5>
                <p class="card-text">Productos seleccionados por su sabor y autenticidad.</p>
                <a href="productos_gt.php" class="link-more mt-auto" style="color:var(--accent-1);font-weight:700;text-decoration:none;">Ver productos →</a>
            </div>
            <div class="feature-card">
                <div class="icon">⚡</div>
                <h5 class="card-title">Entregas Rápidas</h5>
                <p class="card-text">Seguimiento y tiempos optimizados para tu comodidad.</p>
                <a href="contacto.php" class="link-more mt-auto" style="color:var(--accent-1);font-weight:700;text-decoration:none;">Contactar soporte →</a>
            </div>
            <div class="feature-card">
                <div class="icon">🔒</div>
                <h5 class="card-title">Pago Seguro</h5>
                <p class="card-text">Link de pago y alternativas seguras para tus compras.</p>
                <a href="promociones.php" class="link-more mt-auto" style="color:var(--accent-1);font-weight:700;text-decoration:none;">Promociones →</a>
            </div>
        </section>

        <div class="row align-items-start gap-4">
            <div class="col-lg-8">
                <div class="glass p-3">
                    <h4 class="mb-3">Testimonios</h4>
                    <blockquote class="blockquote">
                        <p class="mb-1">"Excelente atención y productos de primera. Llegó rápido y todo muy bien empacado."</p>
                        <footer class="blockquote-footer">Cliente feliz</footer>
                    </blockquote>
                    <hr>
                    <blockquote class="blockquote">
                        <p class="mb-1">"Los productos son de buena calidad, todo muy bien excelente."</p>
                        <footer class="blockquote-footer">Stefany</footer>
                    </blockquote>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cta">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="small text-muted">Oferta</div>
                            <h5 class="mb-0">2x1 en Productos Seleccionados</h5>
                        </div>
                        <div class="badge bg-warning text-dark">Mes</div>
                    </div>
                    <p class="small mb-3">Aprovecha la promoción del mes y solicita tu link de pago seguro ahora.</p>
                    <a href="promociones.php" class="btn" style="background:linear-gradient(90deg,var(--accent-1),var(--accent-2));color:#111;font-weight:800;border-radius:8px;">Ver ofertas</a>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="productos_gt.php" class="btn btn-outline-success me-2">Ver GT</a>
            <a href="productos_mx.php" class="btn btn-outline-primary">Ver MX</a>
        </div>
    </main>

    <footer class="mt-5">
        &copy; <?php echo date('Y'); ?> MaggiSGT — Todos los derechos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
