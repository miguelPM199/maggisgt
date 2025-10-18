<?php


session_start();

// Simulación de productos en el carrito (puedes reemplazar esto con tu lógica real)
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [
        [
            "id" => 1,
            "nombre" => "Producto 1",
            "precio" => 25.00,
            "cantidad" => 2
        ],
        [
            "id" => 2,
            "nombre" => "Producto 2",
            "precio" => 40.00,
            "cantidad" => 1
        ]
    ];
}

// Actualizar cantidades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['cantidades'] as $index => $cantidad) {
        $_SESSION['carrito'][$index]['cantidad'] = max(1, intval($cantidad));
    }
    header("Location: carrito.php");
    exit;
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $index = intval($_GET['eliminar']);
    if (isset($_SESSION['carrito'][$index])) {
        array_splice($_SESSION['carrito'], $index, 1);
    }
    header("Location: carrito.php");
    exit;
}

// Prueba de conexión cURL para depuración
if (isset($_GET['test_curl'])) {
    $ch = curl_init("https://www.google.com/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (!$response) {
        echo 'Error de conexión cURL: ' . curl_error($ch);
    } else {
        echo 'Conexión cURL exitosa';
    }
    curl_close($ch);
    exit;
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Procesar pago personalizado
$mensaje_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar_personalizado'])) {
    $nombre = trim($_POST['nombre'] ?? "");
    $telefono = trim($_POST['telefono'] ?? "");
    $departamento = trim($_POST['departamento'] ?? "");
    $correo = trim($_POST['correo'] ?? "");
    $direccion = trim($_POST['direccion'] ?? "");
    $notas = trim($_POST['notas'] ?? "");
    $metodo_pago = $_POST['metodo_pago'] ?? "";

    if ($nombre && $telefono && $departamento && $correo && $direccion && $metodo_pago) {
        if ($metodo_pago === "Tarjeta") {
            // INTEGRACIÓN DINÁMICA CON RECURRENTE + DEPURACIÓN DE ERRORES
            $data = [
                "amount" => $total,
                "currency" => "GTQ",
                "customer" => [
                    "name" => $nombre,
                    "email" => $correo,
                    "phone" => $telefono
                ],
                "description" => "Compra en MaggiSGT",
                "metadata" => [
                    "departamento" => $departamento,
                    "direccion" => $direccion,
                    "notas" => $notas
                ]
            ];

            $ch = curl_init("https://api.recurrente.com/v1/checkout/sessions");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer sk_live_xfQ2XYsAzExwZE63GozS8UJdAvcVSnJNPigO0NbDIwDYgSDV2SymDxbsA"
            ]);
            $response = curl_exec($ch);
            // DEPURACIÓN: mostrar error de conexión si ocurre
            if (!$response) {
                $mensaje_error = 'Error de conexión: ' . curl_error($ch);
            }
            $result = json_decode($response, true);
            // DEPURACIÓN: mostrar respuesta de la API si no hay URL
            if (!isset($result['url'])) {
                $mensaje_error .= "<br>Respuesta de la API: <pre>" . htmlspecialchars($response) . "</pre>";
            }
            curl_close($ch);

            if (isset($result['url'])) {
                header("Location: " . $result['url']);
                exit;
            } else {
                if (!$mensaje_error) {
                    $mensaje_error = "No se pudo iniciar el pago con tarjeta.";
                }
            }
        } else {
            // Enviar correo al usuario
            $asunto = "Tu pedido en MaggiSGT ha sido realizado";
            $mensaje_correo = "¡Gracias por tu compra en MaggiSGT!\n\n";
            $mensaje_correo .= "Resumen de tu pedido:\n";
            foreach ($_SESSION['carrito'] as $item) {
                $mensaje_correo .= "- {$item['nombre']} x{$item['cantidad']} (Q " . number_format($item['precio'], 2) . ")\n";
            }
            $mensaje_correo .= "\nTotal: Q " . number_format($total, 2) . "\n";
            $mensaje_correo .= "Nombre: $nombre\n";
            $mensaje_correo .= "Teléfono: $telefono\n";
            $mensaje_correo .= "Departamento/Ciudad: $departamento\n";
            $mensaje_correo .= "Dirección de entrega: $direccion\n";
            $mensaje_correo .= "Notas: $notas\n";
            $mensaje_correo .= "Método de pago: $metodo_pago\n\n";
            $mensaje_correo .= "Nos pondremos en contacto contigo para el seguimiento de tu pedido.\n";
            $headers = "From: no-reply@maggisgt.com\r\n";
            @mail($correo, $asunto, $mensaje_correo, $headers);

            // Enviar WhatsApp a MaggiSGT
            $mensaje = "Nueva compra en MaggiSGT:%0A";
            $mensaje .= "Nombre: $nombre%0A";
            $mensaje .= "Teléfono: $telefono%0A";
            $mensaje .= "Correo: $correo%0A";
            $mensaje .= "Departamento/Ciudad: $departamento%0A";
            $mensaje .= "Dirección: $direccion%0A";
            $mensaje .= "Notas: $notas%0A";
            $mensaje .= "Método de pago: $metodo_pago%0A";
            $mensaje .= "Total: Q " . number_format($total, 2) . "%0A";
            $mensaje .= "Productos:%0A";
            foreach ($_SESSION['carrito'] as $item) {
                $mensaje .= "- {$item['nombre']} x{$item['cantidad']} (Q " . number_format($item['precio'], 2) . ")%0A";
            }
            $whatsapp = "https://wa.me/50259252725?text=" . $mensaje;
            header("Location: $whatsapp");
            exit;
        }
    } else {
        $mensaje_error = "Por favor, completa todos los campos obligatorios.";
    }
}

// URL de PayPal (reemplaza con tu enlace real de PayPal)
$paypal_url = "https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=tu-correo@paypal.com&currency_code=GTQ&amount=" . number_format($total, 2, '.', '') . "&item_name=Compra+MaggiSGT";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de compras - MaggiSGT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { 
            padding-top: 60px; 
            background: linear-gradient(135deg, #f9d423 0%, #ff4e50 100%);
            min-height: 100vh;
        }
        .cart-card {
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.09);
            background: #fff;
            padding: 2rem 1.5rem;
            margin-bottom: 2rem;
        }
        .cart-title {
            font-weight: 700;
            letter-spacing: 1px;
        }
        .cart-table th, .cart-table td {
            vertical-align: middle;
        }
        .cart-table input[type="number"] {
            width: 70px;
        }
        .cart-summary {
            border-radius: 16px;
            background: linear-gradient(90deg, #ffe259 0%, #ffa751 100%);
            color: #222;
            padding: 2rem 1.5rem;
            box-shadow: 0 4px 24px rgba(255, 174, 0, 0.09);
        }
        .btn-checkout {
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.75rem 2rem;
        }
        .best-worlds {
            font-size: 1.3rem;
            font-weight: 900;
            color: #111;
            text-align: center;
            letter-spacing: 1px;
            text-shadow:
                2px 2px 0 #bbb,
                4px 4px 0 #ccc,
                6px 6px 2px #888,
                0 2px 12px #0002;
            background: linear-gradient(90deg, #fff 60%, #eee 100%);
            border-radius: 16px;
            box-shadow: 0 6px 24px #0002, 0 2px 0 #fff inset;
            padding: 1.1rem 2rem;
            display: inline-block;
            margin: 0 auto 1.5rem auto;
        }
        .empty-cart {
            text-align: center;
            color: #bbb;
            font-size: 1.3rem;
            margin-top: 3rem;
        }
        .paypal-btn {
            background: #ffc439;
            color: #222;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            transition: background 0.2s;
        }
        .paypal-btn:hover {
            background: #ffb347;
            color: #111;
        }
    </style>
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
    function onSignIn(response) {
        // Decodifica el JWT para obtener el correo
        const data = JSON.parse(atob(response.credential.split('.')[1]));
        document.getElementById('correo').value = data.email;
    }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MaggiSGT</a>
            <!-- Mini menú desplegable -->
            <div class="dropdown ms-auto">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="seguirComprandoMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-arrow-left"></i> Seguir comprando
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="seguirComprandoMenu">
                    <li><a class="dropdown-item" href="index.php"><i class="bi bi-house"></i> Página principal</a></li>
                    <li><a class="dropdown-item" href="productos_mx.php"><i class="bi bi-basket2"></i> Productos Mexicanos</a></li>
                    <li><a class="dropdown-item" href="productos_gt.php"><i class="bi bi-basket2-fill"></i> Productos Guatemaltecos</a></li>
                    <li><a class="dropdown-item" href="promociones.php"><i class="bi bi-gift"></i> Promociones</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="d-flex justify-content-center mb-4">
            <div class="best-worlds">
                <i class="bi bi-stars"></i> ¡Comprarás lo mejor de ambos mundos!
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <div class="cart-card">
                    <h2 class="cart-title mb-4"><i class="bi bi-cart4"></i> Tu carrito</h2>
                    <?php if (empty($_SESSION['carrito'])): ?>
                        <div class="empty-cart">
                            <i class="bi bi-bag-x"></i> Tu carrito está vacío.
                        </div>
                    <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="update_cart" value="1">
                        <div class="table-responsive">
                            <table class="table cart-table align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($_SESSION['carrito'] as $index => $item): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                        </td>
                                        <td>Q <?php echo number_format($item['precio'], 2); ?></td>
                                        <td>
                                            <input type="number" name="cantidades[<?php echo $index; ?>]" min="1" max="99" value="<?php echo $item['cantidad']; ?>" class="form-control form-control-sm text-center">
                                        </td>
                                        <td>Q <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                                        <td>
                                            <a href="?eliminar=<?php echo $index; ?>" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="bi bi-arrow-repeat"></i> Actualizar cantidades
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="cart-summary shadow">
                    <h4 class="mb-3">Resumen</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total productos:</span>
                        <span><?php echo count($_SESSION['carrito']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total a pagar:</span>
                        <span class="fw-bold fs-5">Q <?php echo number_format($total, 2); ?></span>
                    </div>
                    <?php if (!empty($_SESSION['carrito'])): ?>
                        <a href="<?php echo $paypal_url; ?>" target="_blank" class="paypal-btn w-100 mb-2">
                            <i class="bi bi-paypal"></i> Finalizar compra con PayPal
                        </a>
                        <form method="post" class="mt-3">
                            <h5 class="mb-2"><i class="bi bi-person-circle"></i> Tus datos para la entrega</h5>
                            <!-- Google Sign-In -->
                            <div id="g_id_onload"
                                 data-client_id="538522532355-i69tu0tjt15914o54eabvoj8ttp5k8sk.apps.googleusercontent.com"
                                 data-context="signin"
                                 data-ux_mode="popup"
                                 data-callback="onSignIn"
                                 data-auto_prompt="false">
                            </div>
                            <div class="g_id_signin mb-2" data-type="standard"></div>
                            <?php if ($mensaje_error): ?>
                                <div class="alert alert-danger py-1"><?php echo $mensaje_error; ?></div>
                            <?php endif; ?>
                            <div class="mb-2">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-2">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required pattern="[0-9]{8,15}">
                            </div>
                            <div class="mb-2">
                                <label for="departamento" class="form-label">Departamento/Ciudad</label>
                                <input type="text" class="form-control" id="departamento" name="departamento" required>
                            </div>
                            <div class="mb-2">
                                <label for="correo" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" required>
                            </div>
                            <div class="mb-2">
                                <label for="direccion" class="form-label">Dirección de entrega</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required>
                            </div>
                            <div class="mb-2">
                                <label for="notas" class="form-label">Notas para el repartidor (opcional)</label>
                                <textarea class="form-control" id="notas" name="notas"></textarea>
                            </div>
                            <div class="mb-2">
                                <label for="metodo_pago" class="form-label">Método de pago</label>
                                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                    <option value="">Selecciona un método</option>
                                    <option value="PayPal">PayPal</option>
                                    <option value="Contra entrega">Pago contra entrega</option>
                                    <option value="Tarjeta">Tarjeta de débito/crédito</option>
                                </select>
                            </div>
                            <button type="submit" name="pagar_personalizado" class="btn btn-success w-100">
                                <i class="bi bi-cash-coin"></i> Pagar ahora
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-warning btn-checkout w-100" disabled>
                            <i class="bi bi-cash-coin"></i> Finalizar compra
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>