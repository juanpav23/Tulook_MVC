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

        // Defaults actualizados
        $this->cfg = array_merge([
            'host' => 'smtp.gmail.com',
            'port' => 465,           // ← 465 por defecto
            'smtp_secure' => 'ssl',  // ← ssl por defecto
            'username' => '',
            'password' => '',
            'from_email' => '',
            'from_name' => 'TuLook'
        ], $this->cfg);
    }

    // ============================
    //  GENERAR PDF
    // ============================
    public function generarPdfFactura(array $data) {
        $factura = $data['factura'];
        $items = $data['items'];

        ob_start();
        include __DIR__ . '/../views/pedidos/factura_pdf.php';
        $html = ob_get_clean();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        $tmp = sys_get_temp_dir() . "/factura_{$factura['ID_Factura']}.pdf";
        file_put_contents($tmp, $output);

        return $tmp;
    }

    // ============================
    //  ENVIAR CORREO
    // ============================
    public function enviarFacturaConAdjunto($toEmail, $toName, $pdfPath, $idFactura) {
        $mail = new PHPMailer(true);

        try {
            // Config SMTP ACTUALIZADA
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->cfg['username'];
            $mail->Password = $this->cfg['password'];
            $mail->SMTPSecure = $this->cfg['smtp_secure']; // 'ssl'
            $mail->Port = $this->cfg['port']; // 465

            // Configuración para XAMPP
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Timeout más largo
            $mail->Timeout = 30;

            $mail->CharSet = 'UTF-8';

            // Origen y destino
            $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
            $mail->addAddress($toEmail, $toName);

            // Adjuntar PDF
            if (file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, "factura_$idFactura.pdf");
            }

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = "Factura #$idFactura - TuLook";
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #333;'>¡Gracias por tu compra en TuLook!</h2>
                    <p>Hola <strong>$toName</strong>,</p>
                    <p>Tu factura #<strong>$idFactura</strong> está lista y adjunta en este correo.</p>
                    <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                    <br>
                    <p>Saludos,<br><strong>El equipo de TuLook</strong></p>
                </div>
            ";

            // Versión texto plano
            $mail->AltBody = "Hola $toName,\n\nGracias por tu compra en TuLook. Adjuntamos tu factura #$idFactura.\n\nSaludos,\nEl equipo de TuLook";

            $mail->send();

            // Limpiar archivo temporal después del envío
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            return ['success' => true, 'message' => 'Correo enviado correctamente'];

        } catch (Exception $e) {
            // Guardar error en log
            $errorMsg = "[" . date('Y-m-d H:i:s') . "] ERROR PHPMailer: " . $mail->ErrorInfo . "\n";
            file_put_contents(__DIR__ . '/../correo_error.log', $errorMsg, FILE_APPEND);

            return ['success' => false, 'message' => $mail->ErrorInfo];
        }
    }

    // ============================
    //  MÉTODO DE PRUEBA
    // ============================
    public function pruebaEnvio($toEmail = 'looktu541@gmail.com') {
        try {
            // Crear un PDF de prueba
            $datosPrueba = [
                'factura' => [
                    'ID_Factura' => 'TEST001',
                    'Fecha' => date('Y-m-d'),
                    'Total' => '99.99'
                ],
                'items' => [
                    ['Producto' => 'Producto Prueba 1', 'Cantidad' => 1, 'Precio' => '99.99']
                ]
            ];

            $pdfPath = $this->generarPdfFactura($datosPrueba);
            
            // Enviar correo de prueba
            $resultado = $this->enviarFacturaConAdjunto(
                $toEmail, 
                'Cliente de Prueba', 
                $pdfPath, 
                'TEST001'
            );

            return $resultado;

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}