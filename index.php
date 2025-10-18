<?php

session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MaggiSGT - Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 60px;
            background: linear-gradient(135deg, #1e2022 0%, #232526 100%);
            min-height: 100vh;
            color: #fff !important;
        }
        .navbar {
            background: linear-gradient(90deg, #232526 0%, #414345 100%) !important;
        }
        .navbar-brand {
            font-weight: 800;
            color: #e7c873 !important;
            letter-spacing: 2px;
            font-size: 2rem;
        }
        .btn-outline-success, .btn-outline-primary, .btn-outline-danger, .btn-outline-warning, .btn-outline-secondary {
            border-width: 2px;
            color: #fff !important;
            border-color: #e7c873;
        }
        .btn-outline-success:hover,
        .btn-outline-primary:hover,
        .btn-outline-danger:hover,
        .btn-outline-warning:hover,
        .btn-outline-secondary:hover {
            background: #e7c873 !important;
            color: #232526 !important;
        }
        .dropdown-menu {
            min-width: 300px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(231, 200, 115, 0.18);
            background: #232526;
            color: #fff;
        }
        .dropdown-menu .form-label, .dropdown-menu input {
            color: #fff;
        }
        .dropdown-menu .btn-primary {
            background: linear-gradient(90deg, #e7c873 0%, #f09819 100%);
            border: none;
            color: #232526;
            font-weight: 700;
        }
        .dropdown-menu .btn-primary:hover {
            background: linear-gradient(90deg, #f09819 0%, #e7c873 100%);
            color: #232526;
        }
        .btn {
            font-weight: 600;
            letter-spacing: 1px;
            color: #fff !important;
        }
        .btn-primary,
        .btn-secondary,
        .btn-success,
        .btn-danger,
        .btn-warning {
            background: linear-gradient(90deg, #e7c873 0%, #f09819 100%) !important;
            border: none;
            color: #232526 !important;
        }
        .btn-primary:hover,
        .btn-secondary:hover,
        .btn-success:hover,
        .btn-danger:hover,
        .btn-warning:hover {
            background: linear-gradient(90deg, #f09819 0%, #e7c873 100%) !important;
            color: #232526 !important;
        }
        .btn-outline-primary {
            color: #fff !important;
            border-color: #e7c873;
        }
        .btn-outline-primary:hover {
            background: #e7c873 !important;
            color: #232526 !important;
        }
        .btn-outline-secondary {
            color: #fff !important;
            border-color: #e7c873;
        }
        .btn-outline-secondary:hover {
            background: #e7c873 !important;
            color: #232526 !important;
        }
        .badge.bg-danger {
            background: linear-gradient(90deg, #ff5858 0%, #f09819 100%);
            color: #fff;
        }
        h1, h4, .form-label, .form-text, label, .navbar, .navbar-brand, .dropdown-menu, .dropdown-menu .form-label, .dropdown-menu input, .dropdown-menu .btn-primary, .btn, .btn-primary, .btn-secondary, .btn-success, .btn-danger, .btn-warning {
            color: #fff !important;
        }
        .btn, .dropdown-menu {
            box-shadow: 0 2px 8px rgba(231, 200, 115, 0.07);
        }
        .carousel-inner img {
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(231, 200, 115, 0.15);
        }
        .btn:focus, .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(231, 200, 115, 0.25);
        }
        .mb-4 {
            margin-bottom: 2rem !important;
        }
        .fs-5 {
            font-size: 1.25rem !important;
        }
        /* Footer profesional */
        footer {
            background: linear-gradient(90deg,#232526 0%,#414345 100%);
            color: #e7c873;
            text-align: center;
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 1.1rem;
            box-shadow: 0 -2px 16px #0004;
        }
        /* Mejoras responsive */
        @media (max-width: 991.98px) {
            .row.g-3.mb-4 > div[class^="col-"] {
                flex: 0 0 50%;
                max-width: 50%;
                margin-bottom: 1rem;
            }
        }
        @media (max-width: 575.98px) {
            .row.g-3.mb-4 > div[class^="col-"] {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 1rem;
            }
            .btn.fs-5, .btn.py-4 {
                font-size: 1.4rem !important;
                padding-top: 1.5rem !important;
                padding-bottom: 1.5rem !important;
            }
            h1 {
                font-size: 2rem !important;
            }
        }
        /* Botones más grandes en móvil */
        .btn.fs-5, .btn.py-4 {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">MaggiSGT</a>
            <div class="d-flex ms-auto align-items-center">
                <!-- Carrito -->
                <a href="carrito.php" class="btn btn-outline-success me-3 position-relative">
                    <i class="bi bi-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                    </span>
                </a>
                <!-- Login -->
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="loginDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-4 shadow" aria-labelledby="loginDropdown">
                        <?php
                            $usuario_temp = $_SESSION["usuario_temp"] ?? "";
                            if (isset($_SESSION["login_error"])): ?>
                            <div class="alert alert-danger py-1">
                                <?php
                                    echo $_SESSION["login_error"];
                                    unset($_SESSION["login_error"]);
                                ?>
                            </div>
                        <?php endif; ?>
                        <form method="post" action="login.php">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required value="<?php echo htmlspecialchars($usuario_temp); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                            <div class="form-text mt-2 text-center text-warning">
                                Solo para administrador
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container mt-5">
        <h1 class="mb-4">Bienvenido a MaggiSGT</h1>
        <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
                <a href="quienes_somos.php" class="btn w-100 h-100 py-4 fs-5" style="background:linear-gradient(90deg,#e7c873 0%, #f09819 100%)!important;">
                    Quiénes somos
                </a>
            </div>
            <div class="col-md-2 col-6">
                <a href="contacto.php" class="btn w-100 h-100 py-4 fs-5" style="background:linear-gradient(90deg,#e7c873 0%, #f09819 100%)!important;">
                    Contacto
                </a>
            </div>
            <div class="col-md-2 col-6">
                <a href="productos_gt.php" class="btn w-100 h-100 py-4 fs-5" style="background:linear-gradient(90deg,#e7c873 0%, #f09819 100%)!important;">
                    Productos Guatemaltecos
                </a>
            </div>
            <div class="col-md-2 col-6">
                <a href="productos_mx.php" class="btn w-100 h-100 py-4 fs-5" style="background:linear-gradient(90deg,#e7c873 0%, #f09819 100%)!important;">
                    Productos Mexicanos
                </a>
            </div>
            <div class="col-md-2 col-6">
                <a href="promociones.php" class="btn w-100 h-100 py-4 fs-5" style="background:linear-gradient(90deg,#b80000 0%,#ff2e2e 100%)!important;color:#fff;font-weight:800;border:none;">
                    2x1
                </a>
            </div>
        </div>
        <!-- Carrusel de imágenes -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-3">Galería</h4>
                <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Producto 1">
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1519864600265-abb23847ef2c?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Producto 2">
                        </div>
                        <div class="carousel-item">
                            <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=800&q=80" class="d-block w-100" alt="Producto 3">
                        </div>
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

    <!-- Footer profesional -->
    <footer class="mt-5 py-4">
        &copy; <?php echo date('Y'); ?> MaggiSGT. Todos los derechos reservados. Desarrollado por MaggiSGT.
    </footer>
    <!-- Bootstrap JS para dropdown y carrusel -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>