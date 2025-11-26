<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Crear instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP - PUERTO 465
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'looktu541@gmail.com';
    $mail->Password = 'tadntwyjvtynuftr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL para puerto 465
    $mail->Port = 465;

    // Configuración para XAMPP
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Timeout más largo
    $mail->Timeout = 30;

    // Remitente y destinatario
    $mail->setFrom('looktu541@gmail.com', 'TuLook Sistema');
    $mail->addAddress('looktu541@gmail.com', 'Administrador TuLook');
    
    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Prueba SMTP - TuLook - ' . date('Y-m-d H:i:s');
    $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #3366cc;">✅ Prueba de Correo Exitosa</h2>
            <p>Este es un correo de prueba enviado desde <strong>TuLook System</strong>.</p>
            <p><strong>Fecha y hora:</strong> ' . date('Y-m-d H:i:s') . '</p>
            <p><strong>Servidor SMTP:</strong> smtp.gmail.com:465</p>
            <p>Si recibes este correo, la configuración SMTP está funcionando correctamente.</p>
            <hr>
            <p style="color: #666;">Saludos,<br>El equipo de TuLook</p>
        </div>
    ';
    
    // Versión texto plano
    $mail->AltBody = 'Prueba de correo exitosa - TuLook - ' . date('Y-m-d H:i:s');

    // Enviar correo
    $mail->send();
    
    echo '✅ CORREO ENVIADO EXITOSAMENTE' . PHP_EOL;
    echo '📧 Revisa la bandeja de entrada de: looktu541@gmail.com' . PHP_EOL;
    echo '⏰ Hora de envío: ' . date('Y-m-d H:i:s') . PHP_EOL;
    echo '🔧 Puerto usado: 465 (SSL)' . PHP_EOL;
    
} catch (Exception $e) {
    echo '❌ ERROR CON PUERTO 465' . PHP_EOL;
    echo '🔧 Mensaje de error: ' . $mail->ErrorInfo . PHP_EOL;
    
    // Probemos con Mailtrap como alternativa
    echo '🔄 Probando con Mailtrap...' . PHP_EOL;
    probarMailtrap();
}

function probarMailtrap() {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración para Mailtrap (SERVICIO ALTERNATIVO)
        $mail->isSMTP();
        $mail->Host = 'live.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = 'api';
        $mail->Password = 'c72e3ff2d2c09b0348888f6d8687b6cc'; // Token de Mailtrap
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->Timeout = 30;

        // Remitente y destinatario
        $mail->setFrom('noreply@tulook.com', 'TuLook Sistema');
        $mail->addAddress('looktu541@gmail.com', 'Administrador TuLook');
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Prueba MAILTRAP - TuLook - ' . date('Y-m-d H:i:s');
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <h2 style="color: #3366cc;">✅ Prueba con Mailtrap Exitosa</h2>
                <p>Este correo se envió usando <strong>Mailtrap SMTP</strong>.</p>
                <p><strong>Fecha y hora:</strong> ' . date('Y-m-d H:i:s') . '</p>
                <p>Mailtrap es una alternativa confiable a Gmail SMTP.</p>
                <hr>
                <p style="color: #666;">Saludos,<br>El equipo de TuLook</p>
            </div>
        ';
        
        $mail->AltBody = 'Prueba con Mailtrap exitosa - ' . date('Y-m-d H:i:s');

        $mail->send();
        
        echo '✅ CORREO ENVIADO CON MAILTRAP' . PHP_EOL;
        echo '📧 Revisa tu bandeja de entrada' . PHP_EOL;
        echo '🚀 Mailtrap funciona mejor en localhost' . PHP_EOL;
        
    } catch (Exception $e) {
        echo '❌ MAILTRAP TAMPOCO FUNCIONA' . PHP_EOL;
        echo '🔧 Error: ' . $mail->ErrorInfo . PHP_EOL;
        echo '💡 SOLUCIONES:' . PHP_EOL;
        echo '   1. Verifica tu conexión a Internet' . PHP_EOL;
        echo '   2. Desactiva temporalmente el firewall' . PHP_EOL;
        echo '   3. Usa un servicio como Mailtrap o SendGrid' . PHP_EOL;
        echo '   4. Prueba desde un hosting real (no localhost)' . PHP_EOL;
    }
}
?>