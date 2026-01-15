<?php
// =============================================
// API Chatbot - Respuestas independientes
// =============================================

// Evitar que se incluya layout o vistas
define('API_REQUEST', true);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar cualquier salida previa
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Requerir solo el modelo
require_once '../models/Chatbot.php';

// Configurar headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'response' => 'Método no permitido'
    ]);
    exit();
}

// Obtener mensaje
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

// Instanciar chatbot
$chatbot = new Chatbot();

// Procesar mensaje
if (!empty($message)) {
    $response = $chatbot->getResponse($message);
    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('H:i:s')
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'response' => 'Por favor, escribe un mensaje.'
    ]);
}

exit();

