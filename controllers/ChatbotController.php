<?php
require_once 'models/Chatbot.php';

class ChatbotController {
    private $chatbot;
    
    public function __construct($db = null) {
        // No necesita la conexión a BD ya que no guarda historial
        $this->chatbot = new Chatbot();
    }
    
    public function handleMessage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $message = $input['message'] ?? '';
            
            if (!empty($message)) {
                $response = $this->chatbot->getResponse($message);
                
                // NO se guarda en base de datos - mejor rendimiento
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'response' => $response,
                    'timestamp' => date('H:i:s')
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'response' => 'Por favor, escribe un mensaje.'
                ]);
                exit;
            }
        }
        
        // Si no es POST, mostrar la vista del chat
        $this->showChat();
    }
    
    public function showChat() {
        // Esta función podría cargar una vista específica si lo necesitas
        // Por ahora, el chat se integra directamente en el layout
        require_once 'views/chatbot/chat.php';
    }
    
    // Método index por si alguien accede directamente
    public function index() {
        $this->showChat();
    }
}
?>