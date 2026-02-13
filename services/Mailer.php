<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dompdf\Dompdf;

require_once __DIR__ . "/../vendor/autoload.php";

class Mailer {
    private $cfg;

    public function __construct() {
        $path = __DIR__ . '/../config/mail.php';
        $this->cfg = file_exists($path) ? require $path : [];

        $this->cfg = array_merge([
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'smtp_secure' => 'ssl',
            'username' => 'looktu541@gmail.com',
            'password' => 'odrfsblkyzqfbfyt',
            'from_email' => 'looktu541@gmail.com',
            'from_name' => 'TuLook'
        ], $this->cfg);
    }

    // ============================================
    //  GENERAR PDF MEJORADO
    // ============================================
    public function generarPdfFactura(array $data) {
        $factura = $data['factura'];
        $items = $data['items'];
        $descuentos_ganados = $data['descuentos_ganados'] ?? [];
        
        // Asegurarse de que tenemos todos los datos necesarios
        if (!isset($factura['Nombre']) && isset($data['cliente']['nombre'])) {
            $nombres = explode(' ', $data['cliente']['nombre'], 2);
            $factura['Nombre'] = $nombres[0] ?? '';
            $factura['Apellido'] = $nombres[1] ?? '';
        }
        
        if (!isset($factura['Correo']) && isset($data['cliente']['email'])) {
            $factura['Correo'] = $data['cliente']['email'];
        }

        ob_start();
        // Pasar explícitamente los datos a la vista
        include __DIR__ . '/../views/pedidos/factura_pdf.php';
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        
        // Configurar opciones
        $options = $dompdf->getOptions();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf->setOptions($options);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Crear directorio temporal si no existe
        $tempDir = sys_get_temp_dir() . '/tulook_facturas';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $output = $dompdf->output();
        $tmp = $tempDir . "/factura_" . ($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) . "_" . time() . ".pdf";
        file_put_contents($tmp, $output);

        return $tmp;
    }

    // ============================================
    //  ENVIAR CORREO DE CONFIRMACIÓN NORMAL
    // ============================================
    public function enviarConfirmacionCompra($cliente, $factura, $items, $descuentos_ganados = []) {
        $mail = new PHPMailer(true);

        try {
            // CONFIGURACIÓN SMTP
            $this->configurarSMTP($mail);

            // REMITENTE Y DESTINATARIO
            $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
            $mail->addAddress($cliente['email'], $cliente['nombre']);
            
            // COPIA OCULTA PARA LA TIENDA
            $mail->addBCC($this->cfg['from_email'], 'TuLook - Registro de venta');

            // ASUNTO
            $mail->Subject = "✅ Confirmación de compra #{$factura['Codigo_Acceso']} - TuLook";

            // GENERAR Y ADJUNTAR PDF - PASANDO TODOS LOS DATOS NECESARIOS
            $datosPdf = [
                'factura' => $factura,
                'items' => $items,
                'descuentos_ganados' => $descuentos_ganados,
                'cliente' => $cliente // ¡IMPORTANTE! Pasar el cliente al PDF
            ];
            $pdfPath = $this->generarPdfFactura($datosPdf);
            
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, "Factura_" . ($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) . "_TuLook.pdf");
            }

            // DETERMINAR TIPO DE CORREO
            $tieneDescuentosGanados = !empty($descuentos_ganados);
            
            // CUERPO DEL CORREO
            $mail->isHTML(true);
            $mail->Body = $this->generarCuerpoCorreoConfirmacion($cliente, $factura, $items, $tieneDescuentosGanados, $descuentos_ganados);
            
            // VERSIÓN TEXTO PLANO
            $mail->AltBody = $this->generarCuerpoTextoPlanoConfirmacion($cliente, $factura, $tieneDescuentosGanados, $descuentos_ganados);

            // ENVIAR
            $enviado = $mail->send();

            // LIMPIAR ARCHIVO TEMPORAL
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            if ($enviado) {
                return [
                    'success' => true, 
                    'message' => 'Correo enviado correctamente',
                    'tipo' => $tieneDescuentosGanados ? 'con_descuentos' : 'normal'
                ];
            } else {
                throw new Exception("No se pudo enviar el correo");
            }

        } catch (Exception $e) {
            $this->registrarError($mail->ErrorInfo);
            return [
                'success' => false, 
                'message' => 'Error al enviar correo: ' . $mail->ErrorInfo
            ];
        }
    }

    // ============================================
    //  CONFIGURAR SMTP
    // ============================================
    private function configurarSMTP(PHPMailer $mail) {
        $mail->isSMTP();
        $mail->Host = $this->cfg['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->cfg['username'];
        $mail->Password = $this->cfg['password'];
        $mail->SMTPSecure = $this->cfg['smtp_secure'];
        $mail->Port = $this->cfg['port'];
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;
        
        // Para desarrollo local (XAMPP)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }

    // ============================================
    //  GENERAR CUERPO HTML DEL CORREO DE CONFIRMACIÓN
    // ============================================
    private function generarCuerpoCorreoConfirmacion($cliente, $factura, $items, $tieneDescuentosGanados, $descuentos_ganados) {
        $fechaFormateada = date('d/m/Y H:i', strtotime($factura['Fecha_Factura'] ?? date('Y-m-d H:i:s')));
        $totalFormateado = number_format($factura['Monto_Total'] ?? 0, 0, ',', '.');
        
        // Calcular cantidad total de productos
        $cantidadTotal = 0;
        foreach ($items as $item) {
            $cantidadTotal += $item['Cantidad'] ?? 1;
        }
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmación de compra - TuLook</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #f5f5f5;
                }
                .container { 
                    background: white; 
                    border-radius: 12px; 
                    overflow: hidden; 
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
                    margin: 20px auto;
                }
                .header { 
                    background: #4A5B7D; 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header-title {
                    font-size: 32px;
                    font-weight: bold;
                    margin: 0;
                    color: white;
                    letter-spacing: 1px;
                }
                .content { 
                    padding: 30px; 
                    background: #f8f9fa;
                }
                .order-info {
                    background: white;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    border: 1px solid #dee2e6;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                .info-row { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 10px; 
                    padding: 8px 0;
                    border-bottom: 1px solid #f0f0f0;
                }
                .info-label { 
                    color: #1B202D; 
                    font-weight: 600;
                    width: 45%;
                }
                .info-value { 
                    font-weight: 600; 
                    color: #2A3448;
                    width: 50%;
                    text-align: right;
                }
                .total-row { 
                    border-top: 2px solid #dee2e6; 
                    padding-top: 15px; 
                    margin-top: 15px; 
                    font-size: 18px; 
                    color: #1e2944; 
                    font-weight: bold;
                }
                .discount-section {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .discount-title { 
                    color: #856404; 
                    font-weight: 700; 
                    font-size: 16px; 
                    margin-bottom: 10px; 
                }
                .discount-code { 
                    background: #1e2944; 
                    color: white; 
                    padding: 10px 20px; 
                    border-radius: 20px; 
                    font-weight: bold; 
                    display: inline-block; 
                    margin: 5px;
                    font-size: 14px;
                }
                .btn-primary { 
                    background: #2A3448; 
                    color: white; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 6px; 
                    display: inline-block; 
                    margin-top: 20px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    border: none;
                    cursor: pointer;
                }
                .btn-primary:hover {
                    background: #1B202D;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                .signature { 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 1px solid #dee2e6; 
                    color: #1B202D;
                }
                .footer { 
                    padding: 20px; 
                    background: #1B202D; 
                    color: white; 
                    text-align: center; 
                }
                .product-summary {
                    background: white;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 15px 0;
                    border: 1px solid #dee2e6;
                }
                .product-item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 8px;
                    padding: 10px;
                    background: #f8f9fa;
                    border-radius: 6px;
                }
                .product-name { flex: 2; font-weight: 600; color: #2A3448; }
                .product-qty { flex: 1; text-align: center; }
                .product-price { flex: 1; text-align: right; font-weight: 600; color: #1e2944; }
                .download-section {
                    background: #e9ecef;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                .download-icon {
                    font-size: 36px;
                    color: #2A3448;
                    margin-bottom: 10px;
                }
                .text-center { text-align: center; }
                .text-muted { color: #6c757d; }
                .mt-3 { margin-top: 15px; }
                .mb-3 { margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 class="header-title">TuLook</h1>
                    <div style="font-size: 18px; margin-top: 15px;">
                        ¡Gracias por tu compra, <?= htmlspecialchars($cliente['nombre']) ?>!
                    </div>
                </div>
                
                <div class="content">
                    <p style="font-size: 16px;">Hemos recibido tu pedido exitosamente. Aquí están los detalles:</p>
                    
                    <div class="order-info">
                        <h3 style="color: #1B202D; margin-bottom: 20px; text-align: center;">
                            <i class="fas fa-info-circle"></i> Detalles del pedido
                        </h3>
                        
                        <div class="info-row">
                            <span class="info-label">Código del pedido:</span>
                            <span class="info-value"><?= htmlspecialchars($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fecha de compra:</span>
                            <span class="info-value"><?= $fechaFormateada ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Método de pago:</span>
                            <span class="info-value"><?= htmlspecialchars($factura['Metodo_Pago'] ?? 'No especificado') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dirección de envío:</span>
                            <span class="info-value"><?= htmlspecialchars($factura['Direccion_Completa'] ?? 'No especificada') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Cantidad de productos:</span>
                            <span class="info-value"><?= $cantidadTotal ?> items</span>
                        </div>
                        
                        <div class="info-row total-row">
                            <span class="info-label">Total pagado:</span>
                            <span class="info-value">$<?= $totalFormateado ?></span>
                        </div>
                    </div>
                    
                    <!-- Resumen de productos -->
                    <div class="product-summary">
                        <h4 style="color: #1B202D; margin-bottom: 15px;">
                            <i class="fas fa-boxes"></i> Resumen de productos
                        </h4>
                        <?php foreach ($items as $item): ?>
                        <div class="product-item">
                            <div class="product-name"><?= htmlspecialchars($item['Nombre_Producto'] ?? $item['Producto'] ?? 'Producto') ?></div>
                            <div class="product-qty">x<?= $item['Cantidad'] ?? 1 ?></div>
                            <div class="product-price">$<?= number_format(($item['Precio_Unitario'] ?? 0) * ($item['Cantidad'] ?? 1), 0, ',', '.') ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Sección de descarga de PDF -->
                    <div class="download-section">
                        <div class="download-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h4 style="color: #2A3448; margin-bottom: 10px;">Factura disponible para descarga</h4>
                        <p style="color: #6c757d; margin-bottom: 15px;">
                            Tu factura oficial está adjunta a este correo en formato PDF. 
                            Conserva este documento para cualquier consulta o garantía.
                        </p>
                        <p style="font-size: 14px; color: #1e2944;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Archivo adjunto:</strong> Factura_<?= ($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) ?>_TuLook.pdf
                        </p>
                    </div>
                    
                    <?php if ($tieneDescuentosGanados): ?>
                    <div class="discount-section">
                        <div class="discount-title">🎁 ¡Felicidades! Has ganado descuentos especiales</div>
                        <p>Por tu compra has obtenido los siguientes códigos de descuento para tu próxima compra en TuLook:</p>
                        
                        <?php foreach ($descuentos_ganados as $descuento): ?>
                            <div class="discount-code">
                                <?= htmlspecialchars($descuento['Codigo'] ?? '') ?> 
                                -<?= htmlspecialchars($descuento['Valor'] ?? '') ?><?= $descuento['Tipo'] == 'Porcentaje' ? '%' : '' ?>
                            </div>
                            <p style="margin: 5px 0 15px 0;"><small>Válido hasta: <?= date('d/m/Y', strtotime($descuento['Valido_Hasta'] ?? '+30 days')) ?></small></p>
                        <?php endforeach; ?>
                        
                        <p><em>Guarda estos códigos y utilízalos en tu próxima compra en nuestro sitio web.</em></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-3" style="background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #2A3448;">
                        <h5 style="color: #1B202D; margin-bottom: 10px;">
                            <i class="fas fa-sync-alt me-2"></i>Estado actual de tu pedido
                        </h5>
                        <p style="color: #2A3448; margin: 0;">
                            <strong>Confirmado:</strong> Tu pedido ha sido validado y confirmado exitosamente. 
                            Pronto comenzaremos con la preparación y empaquetado de tus productos.
                        </p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="<?= $this->getBaseUrl() ?>?c=Checkout&a=detalleCompra&id=<?= $factura['ID_Factura'] ?>" class="btn-primary">
                            <i class="fas fa-eye me-1"></i> Ver detalle de mi pedido
                        </a>
                    </div>
                    
                    <div class="signature">
                        <p>Saludos,<br>
                        <strong>El equipo de TuLook</strong></p>
                        <p class="text-muted" style="margin-top: 10px;">
                            📧 contacto@tulook.com | 📞 +57 (1) 234 5678 | 🌐 www.tulook.com
                        </p>
                    </div>
                </div>
                
                <div class="footer">
                    <p style="margin: 0; font-size: 14px;">© <?= date('Y') ?> TuLook. Todos los derechos reservados.</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">
                        Este es un correo automático, por favor no responder a este mensaje.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    // ============================================
    //  GENERAR CUERPO TEXTO PLANO DE CONFIRMACIÓN
    // ============================================
    private function generarCuerpoTextoPlanoConfirmacion($cliente, $factura, $tieneDescuentosGanados, $descuentos_ganados) {
        $texto = "CONFIRMACIÓN DE COMPRA - TuLook\n";
        $texto .= "================================\n\n";
        $texto .= "¡Gracias por tu compra, {$cliente['nombre']}!\n\n";
        $texto .= "Detalles de tu pedido:\n";
        $texto .= "Código del pedido: " . ($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) . "\n";
        $texto .= "Fecha: " . date('d/m/Y H:i', strtotime($factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'))) . "\n";
        $texto .= "Método de pago: {$factura['Metodo_Pago']}\n";
        $texto .= "Total: $" . number_format($factura['Monto_Total'] ?? 0, 0, ',', '.') . "\n\n";
        
        if ($tieneDescuentosGanados) {
            $texto .= "¡FELICIDADES! HAS GANADO DESCUENTOS:\n";
            foreach ($descuentos_ganados as $descuento) {
                $texto .= "- Código: {$descuento['Codigo']} ";
                $texto .= "-{$descuento['Valor']}" . ($descuento['Tipo'] == 'Porcentaje' ? '%' : '') . "\n";
            }
            $texto .= "\n";
        }
        
        $texto .= "Tu factura está adjunta en formato PDF con el nombre: Factura_" . ($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) . "_TuLook.pdf\n\n";
        $texto .= "ESTADO DEL PEDIDO: Confirmado. Tu pedido ha sido validado y pronto comenzaremos con la preparación y empaquetado.\n\n";
        $texto .= "Saludos,\n";
        $texto .= "El equipo de TuLook\n";
        $texto .= "contacto@tulook.com | +57 (1) 234 5678 | www.tulook.com\n\n";
        $texto .= "© " . date('Y') . " TuLook. Todos los derechos reservados.";
        
        return $texto;
    }

    // ============================================
    //  REGISTRAR ERRORES
    // ============================================
    private function registrarError($error) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] ERROR PHPMailer: " . $error . "\n";
        $logFile = __DIR__ . '/../correo_errores.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    // ============================================
    //  OBTENER BASE URL
    // ============================================
    private function getBaseUrl() {
        // Intenta obtener de constante global
        if (defined('BASE_URL')) {
            return BASE_URL;
        }
        
        // O construirla dinámicamente
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['PHP_SELF'] ?? '');
        
        return rtrim($protocol . $host . $path, '/') . '/';
    }

    // ============================================
    //  MÉTODO DE PRUEBA
    // ============================================
    public function pruebaEnvio($toEmail = 'looktu541@gmail.com') {
        try {
            $cliente = [
                'nombre' => 'Cliente de Prueba',
                'email' => $toEmail
            ];
            
            $datosPrueba = [
                'factura' => [
                    'ID_Factura' => 'TEST001',
                    'Codigo_Acceso' => 'PED-TEST-001',
                    'Fecha_Factura' => date('Y-m-d H:i:s'),
                    'Metodo_Pago' => 'Tarjeta',
                    'Monto_Total' => '199.990',
                    'Direccion_Completa' => 'Calle 123, Bogotá',
                    'Nombre' => 'Cliente',
                    'Apellido' => 'de Prueba',
                    'Correo' => $toEmail,
                    'Tipo_Documento' => 'CC',
                    'N_Documento' => '12345678',
                    'Celular' => '3001234567'
                ],
                'items' => [
                    [
                        'Nombre_Producto' => 'Camiseta Premium',
                        'Especificaciones' => 'Color: Negro | Talla: M',
                        'Cantidad' => 2,
                        'Precio_Unitario' => 47990,
                        'Precio_Original' => 59990,
                        'Subtotal' => 95980
                    ]
                ],
                'descuentos_ganados' => [
                    [
                        'Codigo' => 'TEST20',
                        'Valor' => '20',
                        'Tipo' => 'Porcentaje',
                        'Valido_Hasta' => date('Y-m-d', strtotime('+30 days'))
                    ]
                ]
            ];

            $resultado = $this->enviarConfirmacionCompra(
                $cliente, 
                $datosPrueba['factura'], 
                $datosPrueba['items'],
                $datosPrueba['descuentos_ganados']
            );

            return $resultado;

        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => 'Error en prueba: ' . $e->getMessage()
            ];
        }
    }

    // ============================================
    //  ENVIAR NOTIFICACIÓN DE CAMBIO DE ESTADO
    // ============================================
    public function enviarNotificacionEstado($cliente, $factura, $estadoAnterior, $estadoNuevo, $motivo = '', $datosAdicionales = []) {
        $mail = new PHPMailer(true);

        try {
            // CONFIGURACIÓN SMTP
            $this->configurarSMTP($mail);

            // REMITENTE Y DESTINATARIO
            $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
            $mail->addAddress($cliente['email'], $cliente['nombre']);
            
            // COPIA OCULTA PARA LA TIENDA
            $mail->addBCC($this->cfg['from_email'], 'TuLook - Cambio de estado pedido');

            // ASUNTO SEGÚN EL ESTADO
            $asunto = $this->generarAsuntoNotificacion($factura['Codigo_Acceso'] ?? $factura['ID_Factura'], $estadoNuevo);
            $mail->Subject = $asunto;

            // GENERAR Y ADJUNTAR PDF SI ES NECESARIO
            $pdfPath = null;
            if (in_array($estadoNuevo, ['Enviado', 'Entregado', 'Anulado', 'Devuelto'])) {
                $datosPdf = [
                    'factura' => $factura,
                    'items' => $datosAdicionales['items'] ?? [],
                    'cliente' => $cliente
                ];
                $pdfPath = $this->generarPdfFactura($datosPdf);
                
                if (file_exists($pdfPath)) {
                    $mail->addAttachment($pdfPath, "Factura_" . ($factura['Codigo_Acceso'] ?? $factura['ID_Factura']) . "_TuLook.pdf");
                }
            }

            // CUERPO DEL CORREO
            $mail->isHTML(true);
            $mail->Body = $this->generarCuerpoNotificacion($cliente, $factura, $estadoAnterior, $estadoNuevo, $motivo, $datosAdicionales);
            
            // VERSIÓN TEXTO PLANO
            $mail->AltBody = $this->generarCuerpoTextoPlanoNotificacion($cliente, $factura, $estadoAnterior, $estadoNuevo, $motivo, $datosAdicionales);

            // ENVIAR
            $enviado = $mail->send();

            // LIMPIAR ARCHIVO TEMPORAL
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            if ($enviado) {
                return [
                    'success' => true, 
                    'message' => 'Notificación enviada correctamente'
                ];
            } else {
                throw new Exception("No se pudo enviar la notificación");
            }

        } catch (Exception $e) {
            $this->registrarError($mail->ErrorInfo);
            return [
                'success' => false, 
                'message' => 'Error al enviar notificación: ' . $mail->ErrorInfo
            ];
        }
    }

    // ============================================
    //  GENERAR ASUNTO DE NOTIFICACIÓN
    // ============================================
    private function generarAsuntoNotificacion($codigoPedido, $estadoNuevo) {
        $asuntos = [
            'Confirmado' => "✅ Pedido {$codigoPedido} confirmado - TuLook",
            'Preparando' => "📦 Pedido {$codigoPedido} en preparación - TuLook",
            'Enviado' => "🚚 Pedido {$codigoPedido} enviado - TuLook",
            'Retrasado' => "⚠️ Retraso en tu pedido {$codigoPedido} - TuLook",
            'Devuelto' => "↩️ Pedido {$codigoPedido} devuelto - TuLook",
            'Entregado' => "🎉 ¡Pedido {$codigoPedido} entregado! - TuLook",
            'Anulado' => "❌ Pedido {$codigoPedido} anulado - TuLook"
        ];
        
        return $asuntos[$estadoNuevo] ?? "📋 Actualización de pedido {$codigoPedido} - TuLook";
    }

    // ============================================
    //  GENERAR CUERPO HTML DE NOTIFICACIÓN
    // ============================================
    private function generarCuerpoNotificacion($cliente, $factura, $estadoAnterior, $estadoNuevo, $motivo = '', $datosAdicionales = []) {
        $fechaActual = date('d/m/Y H:i');
        $codigoPedido = $factura['Codigo_Acceso'] ?? $factura['ID_Factura'];
        
        // Configurar colores según la paleta
        $colorHeader = '#4A5B7D'; // Color de fondo para el header
        $colorBoton = '#2A3448';
        $colorFondo = '';
        $icono = '';
        
        // Definir colores e iconos según el estado
        switch($estadoNuevo) {
            case 'Confirmado':
                $colorFondo = '#f8f9fa';
                $icono = 'fa-check-circle';
                break;
            case 'Preparando':
                $colorFondo = '#f8f9fa';
                $icono = 'fa-box-open';
                break;
            case 'Enviado':
                $colorFondo = '#f8f9fa';
                $icono = 'fa-truck';
                break;
            case 'Retrasado':
                $colorFondo = '#fff9e6';
                $icono = 'fa-clock';
                break;
            case 'Devuelto':
                $colorFondo = '#ffe6e6';
                $icono = 'fa-undo';
                break;
            case 'Entregado':
                $colorFondo = '#f0fff4';
                $icono = 'fa-box-check';
                break;
            case 'Anulado':
                $colorFondo = '#ffe6e6';
                $icono = 'fa-ban';
                break;
            default:
                $colorFondo = '#f8f9fa';
                $icono = 'fa-info-circle';
        }
        
        // Mensajes detallados según el estado
        $mensajes = [
            'Confirmado' => [
                'titulo' => '¡Tu pedido ha sido confirmado!',
                'mensaje' => 'Tu pedido ha sido validado y confirmado exitosamente. Pronto comenzaremos con la preparación y empaquetado de tus productos en nuestras instalaciones.',
                'detalle_extra' => 'Nuestro equipo verificó todos los datos y el pago fue procesado correctamente.'
            ],
            'Preparando' => [
                'titulo' => 'Tu pedido está siendo preparado',
                'mensaje' => 'Estamos preparando y empaquetando tu pedido con el máximo cuidado en nuestras instalaciones. Cada producto está siendo revisado y embalado de forma segura para su transporte.',
                'detalle_extra' => 'El proceso incluye verificación de calidad, empaque seguro y etiquetado para el envío.'
            ],
            'Enviado' => [
                'titulo' => '¡Tu pedido está en camino!',
                'mensaje' => 'Tu pedido ha sido entregado a la transportadora y está en camino hacia la dirección de entrega proporcionada. El paquete ya fue despachado y está en tránsito.',
                'detalle_extra' => 'Puedes seguir el estado de tu envío utilizando la información de seguimiento proporcionada.'
            ],
            'Retrasado' => [
                'titulo' => 'Información importante sobre tu pedido',
                'mensaje' => 'Lamentamos informarte que tu pedido está experimentando un retraso en la entrega. Estamos trabajando para resolver esta situación lo antes posible.',
                'detalle_extra' => 'Hemos contactado a la transportadora para agilizar el proceso y te mantendremos informado sobre cualquier novedad.'
            ],
            'Devuelto' => [
                'titulo' => 'Tu pedido ha sido devuelto',
                'mensaje' => 'Tu pedido ha sido devuelto a nuestras instalaciones por la transportadora. Una vez recibamos el paquete, nuestro equipo lo revisará y te contactaremos para coordinar los siguientes pasos.',
                'detalle_extra' => 'Los motivos pueden incluir: dirección incorrecta, ausencia en el domicilio, o problemas con el paquete.'
            ],
            'Entregado' => [
                'titulo' => '¡Pedido entregado exitosamente!',
                'mensaje' => 'Tu pedido ha sido entregado satisfactoriamente en la dirección especificada. Esperamos que disfrutes tus productos y que cumplan con tus expectativas.',
                'detalle_extra' => 'Recuerda que cuentas con garantía y soporte para cualquier consulta sobre tus productos.'
            ],
            'Anulado' => [
                'titulo' => 'Pedido anulado',
                'mensaje' => 'Tu pedido ha sido anulado según lo solicitado. El proceso de cancelación ha sido completado y cualquier cargo relacionado ha sido revertido según nuestras políticas.',
                'detalle_extra' => 'Si tienes preguntas sobre el proceso de anulación o necesitas asistencia adicional, nuestro equipo de soporte está disponible para ayudarte.'
            ]
        ];
        
        $infoEstado = $mensajes[$estadoNuevo] ?? [
            'titulo' => 'Actualización de pedido', 
            'mensaje' => 'Tu pedido ha sido actualizado.',
            'detalle_extra' => ''
        ];
        
        // Mensajes específicos para transiciones especiales
        if ($estadoAnterior === 'Preparando' && $estadoNuevo === 'Confirmado') {
            $infoEstado['mensaje'] = 'El proceso de preparación y empaquetado de tu pedido ha sido cancelado. Tu pedido ha sido regresado al estado "Confirmado" para ser preparado posteriormente.';
            if (!empty($motivo)) {
                $infoEstado['detalle_extra'] = "Motivo de la cancelación del proceso: " . $motivo;
            }
        }
        
        if ($estadoAnterior === 'Enviado' && $estadoNuevo === 'Preparando') {
            $infoEstado['titulo'] = 'Envío cancelado - Pedido en preparación nuevamente';
            $infoEstado['mensaje'] = 'El envío de tu pedido ha sido cancelado. El paquete ha sido recuperado por nuestro equipo y será preparado nuevamente en nuestro almacén para un nuevo intento de envío.';
            if (!empty($motivo)) {
                $infoEstado['detalle_extra'] = "Motivo de la cancelación del envío: " . $motivo . "\n\nEl pedido volverá a nuestro almacén para ser verificado y preparado nuevamente.";
            } else {
                $infoEstado['detalle_extra'] = "El pedido volverá a nuestro almacén para ser verificado y preparado nuevamente para el envío.";
            }
        }

        if ($estadoAnterior === 'Devuelto' && $estadoNuevo === 'Preparando') {
            $infoEstado['titulo'] = 'Pedido devuelto - En preparación nuevamente';
            $infoEstado['mensaje'] = 'Tu pedido que fue devuelto a nuestras instalaciones ahora está siendo preparado nuevamente. Hemos recibido el paquete, realizado las verificaciones necesarias y procederemos con el reempaque para un nuevo envío.';
            if (!empty($motivo)) {
                $infoEstado['detalle_extra'] = "Proceso de re-preparación: " . $motivo . "\n\nEl producto será inspeccionado, reacondicionado y empaquetado para garantizar su perfecto estado.";
            } else {
                $infoEstado['detalle_extra'] = "El producto será inspeccionado, reacondicionado y empaquetado cuidadosamente para garantizar su perfecto estado antes del nuevo envío.";
            }
        }
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($infoEstado['titulo']) ?> - TuLook</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #f5f5f5;
                }
                .container { 
                    background: white; 
                    border-radius: 12px; 
                    overflow: hidden; 
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
                    margin: 20px auto;
                }
                .header { 
                    background: <?= $colorHeader ?>; 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header-title {
                    font-size: 32px;
                    font-weight: bold;
                    margin: 0;
                    color: white;
                    letter-spacing: 1px;
                }
                .header-icon {
                    font-size: 48px;
                    margin-bottom: 15px;
                    color: white;
                }
                .content { 
                    padding: 30px; 
                    background: <?= $colorFondo ?>; 
                }
                .estado-info {
                    background: white;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    border: 1px solid #dee2e6;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                .info-row { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 10px; 
                    padding: 8px 0;
                    border-bottom: 1px solid #f0f0f0;
                }
                .info-label { 
                    color: #1B202D; 
                    font-weight: 600;
                    width: 45%;
                }
                .info-value { 
                    font-weight: 600; 
                    color: #2A3448;
                    width: 50%;
                    text-align: right;
                }
                .motivo-section {
                    background: white;
                    border-left: 4px solid #2A3448;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 0 8px 8px 0;
                }
                .btn-primary { 
                    background: <?= $colorBoton ?>; 
                    color: white; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 6px; 
                    display: inline-block; 
                    margin-top: 20px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    border: none;
                    cursor: pointer;
                }
                .btn-primary:hover {
                    background: #1B202D;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                .signature { 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 1px solid #dee2e6; 
                    color: #1B202D;
                }
                .footer { 
                    padding: 20px; 
                    background: #1B202D; 
                    color: white; 
                    text-align: center; 
                }
                .badge-estado {
                    display: inline-block;
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-weight: 600;
                    font-size: 12px;
                    margin: 0 5px;
                }
                .badge-anterior {
                    background: #e9ecef;
                    color: #6c757d;
                }
                .badge-nuevo {
                    background: #2A3448;
                    color: white;
                }
                .detalles-envio {
                    background: white;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 15px 0;
                    border: 1px solid #dee2e6;
                }
                .alert {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 15px 0;
                    color: #856404;
                }
                .download-section {
                    background: #e9ecef;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                .download-icon {
                    font-size: 36px;
                    color: #2A3448;
                    margin-bottom: 10px;
                }
                .text-center { text-align: center; }
                .text-muted { color: #6c757d; }
                .mt-3 { margin-top: 15px; }
                .mb-3 { margin-bottom: 15px; }
                .fecha-cambio {
                    background: #f0f9ff;
                    padding: 10px;
                    border-radius: 6px;
                    margin: 10px 0;
                    border-left: 3px solid #2A3448;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 class="header-title">TuLook</h1>
                    <div class="header-icon">
                        <i class="fas <?= $icono ?>"></i>
                    </div>
                    <div style="font-size: 20px; margin-top: 10px;">
                        <?= htmlspecialchars($infoEstado['titulo']) ?>
                    </div>
                </div>
                
                <div class="content">
                    <p style="font-size: 16px;">Hola <strong style="color: #1B202D;"><?= htmlspecialchars($cliente['nombre']) ?></strong>,</p>
                    
                    <p><?= $infoEstado['mensaje'] ?></p>
                    
                    <?php if (!empty($infoEstado['detalle_extra'])): ?>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                        <p style="margin: 0; color: #2A3448;"><?= $infoEstado['detalle_extra'] ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="estado-info">
                        <h3 style="color: #1B202D; margin-bottom: 20px; text-align: center;">
                            <i class="fas fa-info-circle"></i> Información del pedido
                        </h3>
                        
                        <div class="info-row">
                            <span class="info-label">Código del pedido:</span>
                            <span class="info-value"><?= htmlspecialchars($codigoPedido) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Fecha de actualización:</span>
                            <span class="info-value"><?= $fechaActual ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Cambio de estado:</span>
                            <span class="info-value">
                                <span class="badge-estado badge-anterior"><?= htmlspecialchars($estadoAnterior) ?></span>
                                <i class="fas fa-arrow-right text-muted"></i>
                                <span class="badge-estado badge-nuevo"><?= htmlspecialchars($estadoNuevo) ?></span>
                            </span>
                        </div>
                        
                        <?php if ($estadoNuevo === 'Retrasado' && !empty($datosAdicionales['fecha_estimada'])): ?>
                        <div class="fecha-cambio">
                            <div class="info-row">
                                <span class="info-label">Nueva fecha estimada:</span>
                                <span class="info-value">
                                    <strong><?= date('d/m/Y', strtotime($datosAdicionales['fecha_estimada'])) ?></strong>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($estadoNuevo === 'Enviado'): ?>
                            <?php if (!empty($datosAdicionales['transportadora'])): ?>
                            <div class="info-row">
                                <span class="info-label">Transportadora:</span>
                                <span class="info-value"><?= htmlspecialchars($datosAdicionales['transportadora']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($datosAdicionales['numero_guia'])): ?>
                            <div class="info-row">
                                <span class="info-label">Número de guía:</span>
                                <span class="info-value"><?= htmlspecialchars($datosAdicionales['numero_guia']) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($datosAdicionales['fecha_estimada'])): ?>
                            <div class="info-row">
                                <span class="info-label">Fecha estimada de entrega:</span>
                                <span class="info-value">
                                    <strong><?= date('d/m/Y', strtotime($datosAdicionales['fecha_estimada'])) ?></strong>
                                </span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($motivo)): ?>
                    <div class="motivo-section">
                        <h4 style="color: #1B202D; margin-bottom: 10px;">
                            <i class="fas fa-comment-alt"></i> Información adicional
                        </h4>
                        <p style="color: #2A3448; background: #f8f9fa; padding: 10px; border-radius: 4px;"><?= nl2br(htmlspecialchars($motivo)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($estadoNuevo === 'Enviado' && !empty($datosAdicionales['numero_guia'])): ?>
                    <div class="detalles-envio">
                        <h4 style="color: #1B202D; margin-bottom: 15px;">
                            <i class="fas fa-shipping-fast"></i> Información de seguimiento
                        </h4>
                        <p>Puedes rastrear el estado de tu pedido utilizando el número de guía: <strong><?= htmlspecialchars($datosAdicionales['numero_guia']) ?></strong></p>
                        <?php if (!empty($datosAdicionales['transportadora'])): ?>
                        <p>Transportadora: <strong><?= htmlspecialchars($datosAdicionales['transportadora']) ?></strong></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($estadoNuevo === 'Retrasado'): ?>
                    <div class="alert">
                        <h4 style="margin-top: 0;">
                            <i class="fas fa-exclamation-triangle"></i> ¿Necesitas ayuda?
                        </h4>
                        <p>Si tienes preguntas sobre el retraso de tu pedido o necesitas asistencia, nuestro equipo de soporte está disponible para ayudarte.</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($estadoNuevo === 'Anulado'): ?>
                    <div class="alert" style="background: #ffe6e6; border-color: #ffcccc;">
                        <h4 style="margin-top: 0; color: #342a56;">
                            <i class="fas fa-info-circle"></i> Información importante
                        </h4>
                        <p>Si tienes preguntas sobre la anulación de tu pedido o necesitas asistencia con reembolsos, contáctanos para más detalles.</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Sección de descarga de PDF para estados importantes -->
                    <?php if (in_array($estadoNuevo, ['Enviado', 'Entregado', 'Anulado', 'Devuelto'])): ?>
                    <div class="download-section">
                        <div class="download-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h4 style="color: #2A3448; margin-bottom: 10px;">Factura disponible para descarga</h4>
                        <p style="color: #6c757d; margin-bottom: 15px;">
                            Tu factura oficial está adjunta a este correo en formato PDF. 
                            Conserva este documento para cualquier consulta o garantía.
                        </p>
                        <p style="font-size: 14px; color: #1e2944;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Archivo adjunto:</strong> Factura_<?= $codigoPedido ?>_TuLook.pdf
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <a href="<?= $this->getBaseUrl() ?>?c=Checkout&a=detalleCompra&id=<?= $factura['ID_Factura'] ?>" class="btn-primary">
                            <i class="fas fa-eye me-1"></i> Ver detalle de mi pedido
                        </a>
                    </div>
                    
                    <div class="signature">
                        <p>Saludos,<br>
                        <strong>El equipo de TuLook</strong></p>
                        <p class="text-muted" style="margin-top: 10px;">
                            📧 contacto@tulook.com | 📞 +57 (1) 234 5678 | 🌐 www.tulook.com
                        </p>
                    </div>
                </div>
                
                <div class="footer">
                    <p style="margin: 0; font-size: 14px;">© <?= date('Y') ?> TuLook. Todos los derechos reservados.</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">
                        Este es un correo automático, por favor no responder a este mensaje.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    // ============================================
    //  GENERAR CUERPO TEXTO PLANO DE NOTIFICACIÓN
    // ============================================
    private function generarCuerpoTextoPlanoNotificacion($cliente, $factura, $estadoAnterior, $estadoNuevo, $motivo = '', $datosAdicionales = []) {
        $fechaActual = date('d/m/Y H:i');
        $codigoPedido = $factura['Codigo_Acceso'] ?? $factura['ID_Factura'];
        
        $texto = "ACTUALIZACIÓN DE PEDIDO - TuLook\n";
        $texto .= "================================\n\n";
        $texto .= "Hola {$cliente['nombre']},\n\n";
        
        // Mensaje detallado según el estado
        switch($estadoNuevo) {
            case 'Confirmado':
                $texto .= "✅ Tu pedido ha sido confirmado exitosamente.\n";
                $texto .= "Tu pedido ha sido validado y confirmado exitosamente. Pronto comenzaremos con la preparación y empaquetado de tus productos en nuestras instalaciones.\n";
                break;
            case 'Preparando':
                $texto .= "📦 Tu pedido está siendo preparado y empaquetado.\n";
                $texto .= "Estamos preparando y empaquetando tu pedido con el máximo cuidado en nuestras instalaciones. Cada producto está siendo revisado y embalado de forma segura para su transporte.\n";
                break;
            case 'Enviado':
                $texto .= "🚚 Tu pedido ha sido enviado y está en camino.\n";
                $texto .= "Tu pedido ha sido entregado a la transportadora y está en camino hacia la dirección de entrega proporcionada. El paquete ya fue despachado y está en tránsito.\n";
                break;
            case 'Retrasado':
                $texto .= "⚠️ Tu pedido está experimentando un retraso en la entrega.\n";
                $texto .= "Lamentamos informarte que tu pedido está experimentando un retraso en la entrega. Estamos trabajando para resolver esta situación lo antes posible.\n";
                break;
            case 'Devuelto':
                $texto .= "↩️ Tu pedido ha sido devuelto a nuestras instalaciones.\n";
                $texto .= "Tu pedido ha sido devuelto a nuestras instalaciones por la transportadora. Una vez recibamos el paquete, nuestro equipo lo revisará y te contactaremos para coordinar los siguientes pasos.\n";
                break;
            case 'Entregado':
                $texto .= "🎉 ¡Tu pedido ha sido entregado exitosamente!\n";
                $texto .= "Tu pedido ha sido entregado satisfactoriamente en la dirección especificada. Esperamos que disfrutes tus productos y que cumplan con tus expectativas.\n";
                break;
            case 'Anulado':
                $texto .= "❌ Tu pedido ha sido anulado.\n";
                $texto .= "Tu pedido ha sido anulado según lo solicitado. El proceso de cancelación ha sido completado y cualquier cargo relacionado ha sido revertido según nuestras políticas.\n";
                break;
            default:
                $texto .= "📋 Tu pedido ha sido actualizado.\n";
        }
        
        // Mensajes específicos para transiciones especiales
        if ($estadoAnterior === 'Preparando' && $estadoNuevo === 'Confirmado') {
            $texto .= "\nEl proceso de preparación y empaquetado de tu pedido ha sido cancelado. Tu pedido ha sido regresado al estado 'Confirmado' para ser preparado posteriormente.\n";
            if (!empty($motivo)) {
                $texto .= "Motivo de la cancelación del proceso: {$motivo}\n";
            }
        }
        
        if ($estadoAnterior === 'Enviado' && $estadoNuevo === 'Preparando') {
            $texto .= "\nEl envío de tu pedido ha sido cancelado. El paquete ha sido recuperado y será preparado nuevamente para un nuevo intento de envío.\n";
            if (!empty($motivo)) {
                $texto .= "Motivo de la cancelación del envío: {$motivo}\n";
            }
        }
        
        $texto .= "\n";
        $texto .= "Detalles del pedido:\n";
        $texto .= "Código del pedido: {$codigoPedido}\n";
        $texto .= "Fecha de actualización: {$fechaActual}\n";
        $texto .= "Cambio de estado: {$estadoAnterior} → {$estadoNuevo}\n";
        
        if (!empty($datosAdicionales['fecha_estimada'])) {
            $texto .= "Fecha estimada de entrega: " . date('d/m/Y', strtotime($datosAdicionales['fecha_estimada'])) . "\n";
        }
        
        if (!empty($datosAdicionales['transportadora'])) {
            $texto .= "Transportadora: {$datosAdicionales['transportadora']}\n";
        }
        
        if (!empty($datosAdicionales['numero_guia'])) {
            $texto .= "Número de guía: {$datosAdicionales['numero_guia']}\n";
        }
        
        $texto .= "\n";
        
        if (!empty($motivo)) {
            $texto .= "Información adicional:\n";
            $texto .= "{$motivo}\n\n";
        }
        
        if ($estadoNuevo === 'Enviado' && !empty($datosAdicionales['numero_guia'])) {
            $texto .= "Puedes rastrear tu pedido usando el número de guía proporcionado.\n\n";
        }
        
        // Información de descarga de PDF
        if (in_array($estadoNuevo, ['Enviado', 'Entregado', 'Anulado', 'Devuelto'])) {
            $texto .= "FACTURA DISPONIBLE PARA DESCARGA:\n";
            $texto .= "Tu factura oficial está adjunta a este correo en formato PDF.\n";
            $texto .= "Nombre del archivo: Factura_{$codigoPedido}_TuLook.pdf\n";
            $texto .= "Conserva este documento para cualquier consulta o garantía.\n\n";
        }
        
        $texto .= "Puedes ver el detalle de tu pedido en:\n";
        $texto .= $this->getBaseUrl() . "?c=Checkout&a=detalleCompra&codigo={$codigoPedido}\n\n";
        
        $texto .= "Saludos,\n";
        $texto .= "El equipo de TuLook\n";
        $texto .= "contacto@tulook.com | +57 (1) 234 5678 | www.tulook.com\n\n";
        $texto .= "© " . date('Y') . " TuLook. Todos los derechos reservados.\n";
        $texto .= "Este es un correo automático, por favor no responder a este mensaje.";
        
        return $texto;
    }

    // ============================================
    //  ENVIAR NOTIFICACIÓN DE CAMBIO DE FECHA ESTIMADA
    // ============================================
    public function enviarNotificacionCambioFecha($cliente, $factura, $fechaAnterior, $fechaNueva, $motivo = '', $datosAdicionales = []) {
        $mail = new PHPMailer(true);

        try {
            // CONFIGURACIÓN SMTP
            $this->configurarSMTP($mail);

            // REMITENTE Y DESTINATARIO
            $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
            $mail->addAddress($cliente['email'], $cliente['nombre']);
            
            // COPIA OCULTA PARA LA TIENDA
            $mail->addBCC($this->cfg['from_email'], 'TuLook - Cambio fecha entrega');

            // ASUNTO
            $codigoPedido = $factura['Codigo_Acceso'] ?? $factura['ID_Factura'];
            $mail->Subject = "📅 Fecha de entrega actualizada - Pedido {$codigoPedido} - TuLook";

            // GENERAR Y ADJUNTAR PDF
            $pdfPath = null;
            $datosPdf = [
                'factura' => $factura,
                'items' => $datosAdicionales['items'] ?? [],
                'cliente' => $cliente
            ];
            $pdfPath = $this->generarPdfFactura($datosPdf);
            
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, "Factura_{$codigoPedido}_TuLook.pdf");
            }

            // CUERPO DEL CORREO
            $mail->isHTML(true);
            $mail->Body = $this->generarCuerpoCambioFecha($cliente, $factura, $fechaAnterior, $fechaNueva, $motivo, $datosAdicionales);
            
            // VERSIÓN TEXTO PLANO
            $mail->AltBody = $this->generarCuerpoTextoPlanoCambioFecha($cliente, $factura, $fechaAnterior, $fechaNueva, $motivo, $datosAdicionales);

            // ENVIAR
            $enviado = $mail->send();

            // LIMPIAR ARCHIVO TEMPORAL
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            if ($enviado) {
                return [
                    'success' => true, 
                    'message' => 'Notificación de cambio de fecha enviada correctamente'
                ];
            } else {
                throw new Exception("No se pudo enviar la notificación");
            }

        } catch (Exception $e) {
            $this->registrarError($mail->ErrorInfo);
            return [
                'success' => false, 
                'message' => 'Error al enviar notificación: ' . $mail->ErrorInfo
            ];
        }
    }

    // ============================================
    //  GENERAR CUERPO HTML DE CAMBIO DE FECHA
    // ============================================
    private function generarCuerpoCambioFecha($cliente, $factura, $fechaAnterior, $fechaNueva, $motivo = '', $datosAdicionales = []) {
        $fechaActual = date('d/m/Y H:i');
        $codigoPedido = $factura['Codigo_Acceso'] ?? $factura['ID_Factura'];
        $fechaAnteriorFormateada = date('d/m/Y', strtotime($fechaAnterior));
        $fechaNuevaFormateada = date('d/m/Y', strtotime($fechaNueva));
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Fecha de entrega actualizada - TuLook</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: #f5f5f5;
                }
                .container { 
                    background: white; 
                    border-radius: 12px; 
                    overflow: hidden; 
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
                    margin: 20px auto;
                }
                .header { 
                    background: #4A5B7D; 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header-title {
                    font-size: 32px;
                    font-weight: bold;
                    margin: 0;
                    color: white;
                    letter-spacing: 1px;
                }
                .header-icon {
                    font-size: 48px;
                    margin-bottom: 15px;
                    color: white;
                }
                .content { 
                    padding: 30px; 
                    background: #f8f9fa; 
                }
                .estado-info {
                    background: white;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                    border: 1px solid #dee2e6;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                }
                .info-row { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 10px; 
                    padding: 8px 0;
                    border-bottom: 1px solid #f0f0f0;
                }
                .info-label { 
                    color: #1B202D; 
                    font-weight: 600;
                    width: 45%;
                }
                .info-value { 
                    font-weight: 600; 
                    color: #2A3448;
                    width: 50%;
                    text-align: right;
                }
                .fecha-cambio-section {
                    background: #f0f9ff;
                    border-left: 4px solid #2A3448;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 0 8px 8px 0;
                }
                .fecha-comparacion {
                    display: flex;
                    justify-content: space-around;
                    align-items: center;
                    margin: 20px 0;
                }
                .fecha-item {
                    text-align: center;
                    padding: 15px;
                    border-radius: 8px;
                    min-width: 45%;
                }
                .fecha-anterior {
                    background: #ffe6e6;
                    border: 2px solid #ffcccc;
                }
                .fecha-nueva {
                    background: #f0fff4;
                    border: 2px solid #d4edda;
                }
                .fecha-flecha {
                    font-size: 24px;
                    color: #2A3448;
                }
                .motivo-section {
                    background: white;
                    border-left: 4px solid #2A3448;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 0 8px 8px 0;
                }
                .btn-primary { 
                    background: #2A3448; 
                    color: white; 
                    padding: 12px 30px; 
                    text-decoration: none; 
                    border-radius: 6px; 
                    display: inline-block; 
                    margin-top: 20px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    border: none;
                    cursor: pointer;
                }
                .btn-primary:hover {
                    background: #1B202D;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                }
                .signature { 
                    margin-top: 30px; 
                    padding-top: 20px; 
                    border-top: 1px solid #dee2e6; 
                    color: #1B202D;
                }
                .footer { 
                    padding: 20px; 
                    background: #1B202D; 
                    color: white; 
                    text-align: center; 
                }
                .download-section {
                    background: #e9ecef;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                .download-icon {
                    font-size: 36px;
                    color: #2A3448;
                    margin-bottom: 10px;
                }
                .text-center { text-align: center; }
                .text-muted { color: #6c757d; }
                .mt-3 { margin-top: 15px; }
                .mb-3 { margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1 class="header-title">TuLook</h1>
                    <div class="header-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div style="font-size: 20px; margin-top: 10px;">
                        Fecha de entrega actualizada
                    </div>
                </div>
                
                <div class="content">
                    <p style="font-size: 16px;">Hola <strong style="color: #1B202D;"><?= htmlspecialchars($cliente['nombre']) ?></strong>,</p>
                    
                    <p>Te informamos que hemos actualizado la fecha estimada de entrega de tu pedido. Hemos revisado los tiempos de entrega y realizado los ajustes necesarios para brindarte información más precisa.</p>
                    
                    <?php if ($fechaAnterior && strtotime($fechaAnterior) > 0): ?>
                    <div class="fecha-comparacion">
                        <div class="fecha-item fecha-anterior">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">Fecha anterior</div>
                            <div style="font-size: 18px; font-weight: bold; color: #342a56;"><?= $fechaAnteriorFormateada ?></div>
                        </div>
                        
                        <div class="fecha-flecha">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        
                        <div class="fecha-item fecha-nueva">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">Nueva fecha</div>
                            <div style="font-size: 18px; font-weight: bold; color: #1e2944;"><?= $fechaNuevaFormateada ?></div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="fecha-comparacion" style="justify-content: center;">
                        <div class="fecha-item fecha-nueva" style="min-width: 70%;">
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 5px;">Fecha estimada establecida</div>
                            <div style="font-size: 18px; font-weight: bold; color: #1e2944;"><?= $fechaNuevaFormateada ?></div>
                            <div style="font-size: 12px; color: #6c757d; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Se ha establecido una fecha estimada de entrega para tu pedido
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="estado-info">
                        <h3 style="color: #1B202D; margin-bottom: 20px; text-align: center;">
                            <i class="fas fa-info-circle"></i> Información del pedido
                        </h3>
                        
                        <div class="info-row">
                            <span class="info-label">Código del pedido:</span>
                            <span class="info-value"><?= htmlspecialchars($codigoPedido) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Fecha de actualización:</span>
                            <span class="info-value"><?= $fechaActual ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Nueva fecha estimada:</span>
                            <span class="info-value" style="color: #1e2944; font-weight: bold;"><?= $fechaNuevaFormateada ?></span>
                        </div>
                        
                        <?php if (!empty($datosAdicionales['transportadora'])): ?>
                        <div class="info-row">
                            <span class="info-label">Transportadora:</span>
                            <span class="info-value"><?= htmlspecialchars($datosAdicionales['transportadora']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($datosAdicionales['numero_guia'])): ?>
                        <div class="info-row">
                            <span class="info-label">Número de guía:</span>
                            <span class="info-value"><?= htmlspecialchars($datosAdicionales['numero_guia']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($motivo)): ?>
                    <div class="motivo-section">
                        <h4 style="color: #1B202D; margin-bottom: 10px;">
                            <i class="fas fa-comment-alt"></i> Motivo del cambio
                        </h4>
                        <p style="color: #2A3448; background: #f8f9fa; padding: 10px; border-radius: 4px;"><?= nl2br(htmlspecialchars($motivo)) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin: 15px 0;">
                        <h4 style="margin-top: 0; color: #856404;">
                            <i class="fas fa-info-circle"></i> Información importante
                        </h4>
                        <p style="color: #856404; margin: 0;">
                            <strong>Nota:</strong> La fecha estimada de entrega puede variar dependiendo de factores externos como condiciones climáticas, tráfico o eventos imprevistos. Te mantendremos informado sobre el estado de tu envío.
                        </p>
                    </div>
                    
                    <!-- Sección de descarga de PDF -->
                    <div class="download-section">
                        <div class="download-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h4 style="color: #2A3448; margin-bottom: 10px;">Factura disponible para descarga</h4>
                        <p style="color: #6c757d; margin-bottom: 15px;">
                            Tu factura oficial está adjunta a este correo en formato PDF. 
                            Conserva este documento para cualquier consulta o garantía.
                        </p>
                        <p style="font-size: 14px; color: #1e2944;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Archivo adjunto:</strong> Factura_<?= $codigoPedido ?>_TuLook.pdf
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <a href="<?= $this->getBaseUrl() ?>?c=Checkout&a=detalleCompra&id=<?= $factura['ID_Factura'] ?>" class="btn-primary">
                            <i class="fas fa-eye me-1"></i> Ver detalle de mi pedido
                        </a>
                    </div>
                    
                    <div class="signature">
                        <p>Saludos,<br>
                        <strong>El equipo de TuLook</strong></p>
                        <p class="text-muted" style="margin-top: 10px;">
                            📧 contacto@tulook.com | 📞 +57 (1) 234 5678 | 🌐 www.tulook.com
                        </p>
                    </div>
                </div>
                
                <div class="footer">
                    <p style="margin: 0; font-size: 14px;">© <?= date('Y') ?> TuLook. Todos los derechos reservados.</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">
                        Este es un correo automático, por favor no responder a este mensaje.
                    </p>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    // ============================================
    //  GENERAR CUERPO TEXTO PLANO DE CAMBIO DE FECHA
    // ============================================
    private function generarCuerpoTextoPlanoCambioFecha($cliente, $factura, $fechaAnterior, $fechaNueva, $motivo = '', $datosAdicionales = []) {
        $fechaActual = date('d/m/Y H:i');
        $codigoPedido = $factura['Codigo_Acceso'] ?? $factura['ID_Factura'];
        
        // Formatear la fecha nueva
        $fechaNuevaFormateada = date('d/m/Y', strtotime($fechaNueva));
        
        $texto = "CAMBIO DE FECHA DE ENTREGA - TuLook\n";
        $texto .= "===================================\n\n";
        $texto .= "Hola {$cliente['nombre']},\n\n";
        $texto .= "Te informamos que hemos actualizado la fecha estimada de entrega de tu pedido.\n\n";
        
        // VERIFICAR SI HAY FECHA ANTERIOR VÁLIDA
        if ($fechaAnterior && strtotime($fechaAnterior) > 0) {
            $fechaAnteriorFormateada = date('d/m/Y', strtotime($fechaAnterior));
            $texto .= "RESUMEN DEL CAMBIO:\n";
            $texto .= "Fecha anterior: {$fechaAnteriorFormateada}\n";
            $texto .= "Nueva fecha: {$fechaNuevaFormateada}\n\n";
        } else {
            $texto .= "FECHA ESTIMADA ESTABLECIDA:\n";
            $texto .= "Se ha establecido una fecha estimada de entrega para tu pedido.\n";
            $texto .= "Fecha estimada: {$fechaNuevaFormateada}\n\n";
        }
        
        $texto .= "DETALLES DEL PEDIDO:\n";
        $texto .= "Código del pedido: {$codigoPedido}\n";
        $texto .= "Fecha de actualización: {$fechaActual}\n";
        $texto .= "Nueva fecha estimada: {$fechaNuevaFormateada}\n";
        
        if (!empty($datosAdicionales['transportadora'])) {
            $texto .= "Transportadora: {$datosAdicionales['transportadora']}\n";
        }
        
        if (!empty($datosAdicionales['numero_guia'])) {
            $texto .= "Número de guía: {$datosAdicionales['numero_guia']}\n";
        }
        
        $texto .= "\n";
        
        if (!empty($motivo)) {
            $texto .= "MOTIVO DEL CAMBIO:\n";
            $texto .= "{$motivo}\n\n";
        }
        
        $texto .= "INFORMACIÓN IMPORTANTE:\n";
        $texto .= "La fecha estimada de entrega puede variar dependiendo de factores externos como condiciones climáticas, tráfico o eventos imprevistos. Te mantendremos informado sobre el estado de tu envío.\n\n";
        
        $texto .= "FACTURA DISPONIBLE PARA DESCARGA:\n";
        $texto .= "Tu factura oficial está adjunta a este correo en formato PDF.\n";
        $texto .= "Nombre del archivo: Factura_{$codigoPedido}_TuLook.pdf\n";
        $texto .= "Conserva este documento para cualquier consulta o garantía.\n\n";
        
        $texto .= "Puedes ver el detalle de tu pedido en:\n";
        $texto .= $this->getBaseUrl() . "?c=Checkout&a=detalleCompra&id={$factura['ID_Factura']}\n\n";
        
        $texto .= "Saludos,\n";
        $texto .= "El equipo de TuLook\n";
        $texto .= "contacto@tulook.com | +57 (1) 234 5678 | www.tulook.com\n\n";
        $texto .= "© " . date('Y') . " TuLook. Todos los derechos reservados.\n";
        $texto .= "Este es un correo automático, por favor no responder a este mensaje.";
        
        return $texto;
    }
}