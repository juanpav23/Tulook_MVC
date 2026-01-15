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
            'password' => 'tadntwyjvtynuftr',
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
        $tmp = $tempDir . "/factura_{$factura['ID_Factura']}_" . time() . ".pdf";
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
            $mail->Subject = "✅ Confirmación de compra #{$factura['ID_Factura']} - TuLook";

            // GENERAR Y ADJUNTAR PDF - PASANDO TODOS LOS DATOS NECESARIOS
            $datosPdf = [
                'factura' => $factura,
                'items' => $items,
                'descuentos_ganados' => $descuentos_ganados,
                'cliente' => $cliente // ¡IMPORTANTE! Pasar el cliente al PDF
            ];
            $pdfPath = $this->generarPdfFactura($datosPdf);
            
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, "Factura_{$factura['ID_Factura']}_TuLook.pdf");
            }

            // DETERMINAR TIPO DE CORREO
            $tieneDescuentosGanados = !empty($descuentos_ganados);
            
            // CUERPO DEL CORREO
            $mail->isHTML(true);
            $mail->Body = $this->generarCuerpoCorreo($cliente, $factura, $items, $tieneDescuentosGanados, $descuentos_ganados);
            
            // VERSIÓN TEXTO PLANO
            $mail->AltBody = $this->generarCuerpoTextoPlano($cliente, $factura, $tieneDescuentosGanados, $descuentos_ganados);

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
    //  GENERAR CUERPO HTML DEL CORREO
    // ============================================
    private function generarCuerpoCorreo($cliente, $factura, $items, $tieneDescuentosGanados, $descuentos_ganados) {
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
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background: #f8f9fa; }
                .footer { padding: 20px; background: #343a40; color: white; text-align: center; border-radius: 0 0 10px 10px; }
                .logo { font-size: 32px; font-weight: bold; margin-bottom: 10px; }
                .greeting { font-size: 18px; margin-bottom: 20px; }
                .order-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #dee2e6; }
                .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
                .info-label { color: #6c757d; }
                .info-value { font-weight: 600; }
                .total-row { border-top: 2px solid #dee2e6; padding-top: 15px; margin-top: 15px; font-size: 20px; color: #28a745; }
                .discount-section { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; }
                .discount-title { color: #856404; font-weight: 700; font-size: 16px; margin-bottom: 10px; }
                .discount-code { background: #28a745; color: white; padding: 10px 20px; border-radius: 20px; font-weight: bold; display: inline-block; margin: 5px; }
                .btn-primary { background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
                .signature { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; }
                .product-summary { background: #e9ecef; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .product-item { display: flex; justify-content: space-between; margin-bottom: 8px; }
                .product-name { flex: 2; }
                .product-qty { flex: 1; text-align: center; }
                .product-price { flex: 1; text-align: right; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">TuLook</div>
                <div class="greeting">¡Gracias por tu compra, <?= htmlspecialchars($cliente['nombre']) ?>!</div>
            </div>
            
            <div class="content">
                <p>Hemos recibido tu pedido exitosamente. Aquí están los detalles:</p>
                
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Número de factura:</span>
                        <span class="info-value">#<?= str_pad($factura['ID_Factura'], 6, '0', STR_PAD_LEFT) ?></span>
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
                    <h4>Resumen de productos:</h4>
                    <?php foreach ($items as $item): ?>
                    <div class="product-item">
                        <div class="product-name"><?= htmlspecialchars($item['Nombre_Producto'] ?? $item['Producto'] ?? 'Producto') ?></div>
                        <div class="product-qty">x<?= $item['Cantidad'] ?? 1 ?></div>
                        <div class="product-price">$<?= number_format(($item['Precio_Unitario'] ?? 0) * ($item['Cantidad'] ?? 1), 0, ',', '.') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <p>Tu factura está adjunta en este correo en formato PDF.</p>
                
                <?php if ($tieneDescuentosGanados): ?>
                <div class="discount-section">
                    <div class="discount-title">🎁 ¡Felicidades! Ganaste descuentos</div>
                    <p>Por tu compra has ganado los siguientes códigos de descuento para tu próxima compra:</p>
                    
                    <?php foreach ($descuentos_ganados as $descuento): ?>
                        <div class="discount-code">
                            <?= htmlspecialchars($descuento['Codigo'] ?? '') ?> 
                            -<?= htmlspecialchars($descuento['Valor'] ?? '') ?><?= $descuento['Tipo'] == 'Porcentaje' ? '%' : '' ?>
                        </div>
                        <p><small>Válido hasta: <?= date('d/m/Y', strtotime($descuento['Valido_Hasta'] ?? '+30 days')) ?></small></p>
                    <?php endforeach; ?>
                    
                    <p><em>Guarda estos códigos y úsalos en tu próxima compra en nuestro sitio web.</em></p>
                </div>
                <?php endif; ?>
                
                <p><strong>Estado de tu pedido:</strong> Tu pedido está siendo preparado. Te notificaremos cuando sea enviado.</p>
                
                <p>Puedes ver el detalle de tu pedido en nuestro sitio web o contactarnos si tienes alguna pregunta.</p>
                
                <a href="<?= $this->getBaseUrl() ?>?c=Checkout&a=detalleCompra&id=<?= $factura['ID_Factura'] ?>" class="btn-primary">
                    Ver detalle de mi pedido
                </a>
                
                <div class="signature">
                    <p>Saludos,<br>
                    <strong>El equipo de TuLook</strong></p>
                    <p><small>📧 contacto@tulook.com | 📞 +57 (1) 234 5678 | 🌐 www.tulook.com</small></p>
                </div>
            </div>
            
            <div class="footer">
                <p>© <?= date('Y') ?> TuLook. Todos los derechos reservados.</p>
                <p><small>Este es un correo automático, por favor no responder a este mensaje.</small></p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    // ============================================
    //  GENERAR CUERPO TEXTO PLANO
    // ============================================
    private function generarCuerpoTextoPlano($cliente, $factura, $tieneDescuentosGanados, $descuentos_ganados) {
        $texto = "CONFIRMACIÓN DE COMPRA - TuLook\n";
        $texto .= "================================\n\n";
        $texto .= "¡Gracias por tu compra, {$cliente['nombre']}!\n\n";
        $texto .= "Detalles de tu pedido:\n";
        $texto .= "Número de factura: #{$factura['ID_Factura']}\n";
        $texto .= "Fecha: " . date('d/m/Y H:i', strtotime($factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'))) . "\n";
        $texto .= "Método de pago: {$factura['Metodo_Pago']}\n";
        $texto .= "Total: $" . number_format($factura['Monto_Total'] ?? 0, 0, ',', '.') . "\n\n";
        
        if ($tieneDescuentosGanados) {
            $texto .= "¡FELICIDADES! GANASTE DESCUENTOS:\n";
            foreach ($descuentos_ganados as $descuento) {
                $texto .= "- Código: {$descuento['Codigo']} ";
                $texto .= "-{$descuento['Valor']}" . ($descuento['Tipo'] == 'Porcentaje' ? '%' : '') . "\n";
            }
            $texto .= "\n";
        }
        
        $texto .= "Tu factura está adjunta en formato PDF.\n\n";
        $texto .= "Estado: Tu pedido está siendo preparado.\n\n";
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
}