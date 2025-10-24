<?php

session_start();

// número de WhatsApp de la tienda
$wa_number = '50259252725';

// Manejo del formulario: en lugar de enviar email, redirigimos a WhatsApp con el mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // honeypot anti-spam
    if (!empty($_POST['website_hp'] ?? '')) {
        $_SESSION['contact_status'] = ['type' => 'error', 'msg' => 'No se pudo procesar el formulario.'];
        header('Location: contacto.php');
        exit;
    }

    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $mensaje  = trim($_POST['mensaje'] ?? '');

    // validación mínima
    if ($nombre === '' || $mensaje === '') {
        $_SESSION['contact_status'] = ['type' => 'error', 'msg' => 'Por favor completa tu nombre y mensaje.'];
        header('Location: contacto.php');
        exit;
    }

    // Construir texto para WhatsApp (usa saltos de línea reales, luego rawurlencode)
    $wa_text  = "Buena tarde, quiero contactarme desde el formulario de la web.\n\n";
    $wa_text .= "Nombre: " . $nombre . "\n";
    if ($telefono !== '') $wa_text .= "Teléfono: " . $telefono . "\n";
    if ($correo !== '') $wa_text .= "Correo: " . $correo . "\n";
    $wa_text .= "\nMensaje:\n" . $mensaje . "\n";

    // Redirigir al chat de WhatsApp (abre la app o WhatsApp Web)
    $whatsapp_url = "https://wa.me/{$wa_number}?text=" . rawurlencode($wa_text);
    header("Location: " . $whatsapp_url);
    exit;
}

// Obtener estado si existe (mensajes de validación)
$status = $_SESSION['contact_status'] ?? null;
unset($_SESSION['contact_status']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - MaggiSGT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root{
            --accent1:#ff7a18;
            --accent2:#ffb199;
            --card-bg:#ffffff;
            --muted:#6c757d;
        }
        body{
            background: linear-gradient(135deg, #fff6e6 0%, #fff1e0 100%);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            padding-top:72px;
        }
        .contact-wrap{ max-width:980px; margin:0 auto; padding:28px; }
        .hero{
            border-radius:14px; padding:22px; background:linear-gradient(90deg,var(--accent1),var(--accent2));
            color:#fff; box-shadow:0 10px 30px rgba(255,122,24,0.12);
            display:flex; gap:18px; align-items:center;
        }
        .hero .title{ font-size:1.25rem; font-weight:700; }
        .card-contact{
            margin-top:18px; border-radius:14px; background:var(--card-bg); box-shadow:0 12px 30px rgba(16,24,40,0.06); overflow:hidden;
            display:grid; grid-template-columns:1fr 360px;
        }
        @media(max-width:991px){ .card-contact{ grid-template-columns:1fr; } }
        .left { padding:28px; }
        .right { padding:22px; background:linear-gradient(180deg,#fafafa,#fff); border-left:1px solid rgba(0,0,0,0.04); }
        .field-label{ font-weight:600; font-size:0.9rem; color:#111; margin-bottom:6px; }
        .form-control:focus{ box-shadow:0 0 0 0.15rem rgba(255,122,24,0.12); border-color:var(--accent1); }
        .btn-primary-cta{ background:linear-gradient(90deg,var(--accent1),#ff9a5a); border:none; color:#fff; font-weight:700; padding:12px 16px; border-radius:10px; box-shadow:0 8px 20px rgba(255,122,24,0.12); }
        .whatsapp-cta{ background:#25D366; color:#fff; border-radius:10px; padding:10px 12px; display:inline-flex; gap:10px; align-items:center; text-decoration:none; font-weight:700; }
        .hint{ color:var(--muted); font-size:0.9rem; }
        .socials a{ margin-right:10px; color:#333; font-size:1.25rem; text-decoration:none; }
        .badge-secure { display:inline-block; padding:6px 10px; background:#198754; color:#fff; border-radius:8px; font-weight:700; font-size:0.85rem; margin-bottom:10px; }
        .success { background:#e9f9ee; border-left:4px solid #2fa84f; padding:12px 14px; border-radius:8px; color:#165a2c; margin-bottom:14px; }
        .error { background:#fff1f0; border-left:4px solid #d9534f; padding:12px 14px; border-radius:8px; color:#612020; margin-bottom:14px; }
        footer small { color:var(--muted); }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">MaggiSGT</a>
        </div>
    </nav>

    <main class="contact-wrap">
        <div class="hero">
            <div>
                <div class="title">¿Tienes alguna duda? Estamos aquí para ayudarte.</div>
                <div class="hint">Completa el formulario y abre el chat de WhatsApp con tu información prellenada.</div>
            </div>
            <div class="ms-auto text-end">
                <div class="badge rounded-pill" style="background:rgba(255,255,255,0.12); padding:8px 12px; font-weight:700;">Atención rápida</div>
            </div>
        </div>

        <div class="card-contact">
            <div class="left">
                <?php if ($status): ?>
                    <div class="<?php echo $status['type'] === 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($status['msg']); ?>
                    </div>
                <?php endif; ?>

                <h5 class="mb-3">Escríbenos</h5>
                <form method="post" novalidate>
                    <!-- Honeypot anti-spam (oculto) -->
                    <input type="text" name="website_hp" style="display:none" tabindex="-1" autocomplete="off">

                    <div class="mb-3">
                        <label class="field-label" for="nombre">Nombre completo <span class="text-danger">*</span></label>
                        <input id="nombre" name="nombre" type="text" class="form-control" placeholder="Tu nombre" required>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label class="field-label" for="telefono">Teléfono</label>
                            <input id="telefono" name="telefono" type="tel" class="form-control" placeholder="502 1234 5678">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="field-label" for="correo">Correo (opcional)</label>
                            <input id="correo" name="correo" type="email" class="form-control" placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="field-label" for="mensaje">Mensaje <span class="text-danger">*</span></label>
                        <textarea id="mensaje" name="mensaje" rows="6" class="form-control" placeholder="Cuéntanos ¿en qué podemos ayudarte?" required></textarea>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <!-- El botón ahora abre WhatsApp con los datos del formulario -->
                        <button type="submit" name="contact_submit" class="btn btn-primary-cta">
                            <i class="bi bi-chat-dots me-1"></i> Chatear por WhatsApp
                        </button>

                        <!-- enlace directo (sin form) permanece como alternativa -->
                        <a href="https://wa.me/<?php echo $wa_number; ?>" target="_blank" rel="noopener" class="whatsapp-cta" aria-label="Contactar por WhatsApp">
                            <i class="bi bi-whatsapp" style="font-size:1.2rem"></i> Abrir WhatsApp
                        </a>
                    </div>

                    <div class="mt-3 hint">Al abrir el chat, revisa el mensaje que se abrirá y envíalo. No compartimos tus datos.</div>
                </form>
            </div>

            <aside class="right">
                <div class="p-2 mb-3">
                    <div class="badge-secure">Forma segura de pagar</div>
                    <p class="mt-2 mb-1">Solicita tu link de pago seguro para pagar con tarjeta. Te lo generamos desde nuestra pasarela oficial.</p>
                    <a class="whatsapp-cta d-inline-block" href="<?php
                        $wa_text2 = "Buena tarde, por favor envíenme el link de pago seguro para cancelar con tarjeta.";
                        if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
                            $wa_text2 .= "\n\nResumen de productos:\n";
                            foreach ($_SESSION['carrito'] as $it) {
                                $wa_text2 .= "- " . ($it['nombre'] ?? 'Producto') . " x" . ($it['cantidad'] ?? 1) . " (Q " . number_format($it['precio'] ?? 0, 2) . ")\n";
                            }
                        }
                        echo "https://wa.me/{$wa_number}?text=" . rawurlencode($wa_text2);
                    ?>" target="_blank" rel="noopener">
                        <i class="bi bi-whatsapp"></i> Solicitar link de pago
                    </a>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="mb-2">Otras formas de contacto</h6>
                    <div class="socials mb-2">
                        <a href="https://wa.me/<?php echo $wa_number; ?>?text=¡Hola!%20Quiero%20más%20información%20sobre%20MaggiSGT" target="_blank" rel="noopener" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        <a href="https://www.tiktok.com/@maggisgt" target="_blank" rel="noopener" title="TikTok"><i class="bi bi-tiktok"></i></a>
                        <a href="https://www.facebook.com" target="_blank" rel="noopener" title="Facebook"><i class="bi bi-facebook"></i></a>
                    </div>

                    <p class="hint mb-1">Horario de atención</p>
                    <p class="fw-semibold">Lun - Vie: 9:00 - 18:00</p>
                </div>

                <hr>

                <footer>
                    <small>MaggiSGT • Atención y pagos seguros</small>
                </footer>
            </aside>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
