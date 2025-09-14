
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Quiénes somos - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            padding-top: 60px;
            background: linear-gradient(135deg, #ffe259 0%, #ffa751 100%);
            min-height: 100vh;
        }
        .about-card {
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(255, 174, 0, 0.18);
            background: #fff;
            padding: 2.5rem 2rem;
            margin-top: 3rem;
            margin-bottom: 3rem;
            max-width: 700px;
        }
        .about-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ff8800;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }
        .about-highlight {
            color: #ff4e50;
            font-weight: 700;
        }
        .about-icon {
            font-size: 3.5rem;
            color: #ff4e50;
            margin-bottom: 1rem;
        }
        .about-btn {
            background: linear-gradient(90deg, #ffb347 0%, #ffcc33 100%);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-size: 1.2rem;
            box-shadow: 0 4px 16px rgba(255, 174, 0, 0.12);
            transition: background 0.2s;
        }
        .about-btn:hover {
            background: linear-gradient(90deg, #ffcc33 0%, #ffb347 100%);
            color: #fff;
        }
        .about-features {
            margin-top: 2rem;
        }
        .about-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.2rem;
            font-size: 1.1rem;
            color: #444;
        }
        .about-feature i {
            font-size: 1.7rem;
            color: #ffa751;
        }
        @media (max-width: 600px) {
            .about-card {
                padding: 1.2rem 0.5rem;
            }
            .about-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php" style="color:#ff8800;">
                <i class="bi bi-shop-window"></i> MaggiSGT
            </a>
        </div>
    </nav>
    <div class="container d-flex justify-content-center align-items-center flex-column">
        <div class="about-card text-center">
            <div class="about-icon">
                <i class="bi bi-bag-heart-fill"></i>
            </div>
            <div class="about-title">¡Bienvenido a <span class="about-highlight">MaggiSGT</span>!</div>
            <p class="fs-5 mb-4">
                Somos la <span class="about-highlight">tienda en línea</span> líder en productos guatemaltecos y mexicanos.<br>
                <span style="color:#ff4e50;font-weight:600;">Compra fácil, rápido y seguro</span> desde la comodidad de tu hogar.<br>
                <span style="color:#ffa751;">¡Descubre sabores, cultura y calidad en cada pedido!</span>
            </p>
            <div class="about-features text-start mx-auto" style="max-width: 500px;">
                <div class="about-feature"><i class="bi bi-truck"></i> Envíos rápidos a todo el país</div>
                <div class="about-feature"><i class="bi bi-cash-coin"></i> Promociones exclusivas y 2x1 cada semana</div>
                <div class="about-feature"><i class="bi bi-star-fill"></i> Productos auténticos y seleccionados</div>
                <div class="about-feature"><i class="bi bi-shield-check"></i> Pago seguro y soporte personalizado</div>
            </div>
            <a href="productos_gt.php" class="about-btn mt-4">
                <i class="bi bi-bag-plus"></i> ¡Explora nuestros productos!
            </a>
        </div>
    </div>
    <script src=