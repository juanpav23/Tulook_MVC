<?php
require_once 'models/Chatbot.php';

class ChatbotController {
    private $chatbot;
    
    public function __construct($db = null) {
        // No necesita la conexión a BD ya que no guarda historial
        $this->chatbot = new Chatbot();
    }
    
    public function handleMessage() {
        // Este método ahora solo se usa si se llama vía el routing normal
        // Para AJAX, se usa la API en /api/chatbot.php directamente
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