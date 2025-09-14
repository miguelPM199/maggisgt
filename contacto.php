
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { padding-top: 60px; background: #f8f9fa; }
        .contact-card {
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.09);
            background: #fff;
            padding: 2rem 1.5rem;
            margin-top: 2rem;
        }
        .icon-btn {
            font-size: 2.2rem;
            margin: 0 1rem;
            color: #25d366;
            transition: color 0.2s;
        }
        .icon-btn.tiktok {
            color: #000;
        }
        .icon-btn:hover {
            color: #128c7e;
        }
        .icon-btn.tiktok:hover {
            color: #ff0050;
        }
        .llamativo {
            font-size: 1.3rem;
            font-weight: 600;
            color: #ff8800;
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MaggiSGT</a>
        </div>
    </nav>
    <div class="container">
        <div class="contact-card mx-auto" style="max-width: 500px;">
            <div class="llamativo">
                ¡Contáctanos y vive la mejor experiencia!
            </div>
            <div class="d-flex justify-content-center mb-4">
                <a href="https://wa.me/50259252725?text=¡Hola!%20Quiero%20más%20información%20sobre%20MaggiSGT" target="_blank" class="icon-btn" title="WhatsApp">
                    <i class="bi bi-whatsapp"></i> 59252725
                </a>
                <a href="https://www.tiktok.com/@tucuenta" target="_blank" class="icon-btn tiktok" title="TikTok">
                    <i class="bi bi-tiktok"></i>
                </a>
            </div>
            <form>
                <div class="mb-3">
                    <label for="mensaje" class="form-label">Escribe tu mensaje:</label>
                    <textarea class="form-control" id="mensaje" rows="4" placeholder="¡Déjanos tu consulta, sugerencia o saludo!"></textarea>
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold">Enviar mensaje</button>
            </form>
            <a href="index.php" class="btn btn-outline-primary w-100 mt-3">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>