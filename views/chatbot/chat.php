<div id="chatbot-container" class="chatbot-container">
    <div class="chatbot-header">
        <div class="chatbot-title">
            <span class="chatbot-icon"><i class="fas fa-robot"></i></span>
            <h5>Asistente Virtual</h5>
        </div>
        <button id="chatbot-close" class="chatbot-close"><i class="fas fa-times"></i></button>
    </div>
    
    <div id="chatbot-messages" class="chatbot-messages">
        <div class="message bot-message">
            <div class="message-content">
                <strong>Asistente:</strong> Hola, soy tu asistente de TuLook. ¿En qué puedo ayudarte? Puedo proporcionarte información sobre productos, categorías, tallas, precios, envíos o contactar con soporte.
            </div>
        </div>
    </div>
    
    <div class="chatbot-suggestions">
        <small>Preguntas frecuentes:</small>
        <div class="suggestion-buttons">
            <button class="suggestion-btn" data-question="¿Qué productos tienen?">Productos</button>
            <button class="suggestion-btn" data-question="¿Qué tallas manejan?">Tallas</button>
            <button class="suggestion-btn" data-question="¿Cómo contacto soporte?">Soporte</button>
        </div>
    </div>
    
    <div class="chatbot-input">
        <input type="text" id="chatbot-input" placeholder="Escribe tu pregunta..." class="form-control">
        <button id="chatbot-send" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<button id="chatbot-toggle" class="chatbot-toggle-btn">
    <i class="fas fa-comments"></i>
    <span class="chat-text">Asistente</span>
</button>

<style>
.chatbot-container {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 380px;
    height: 520px;
    background: white;
    border: 2px solid #2f3e53ff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    display: none;
    flex-direction: column;
    z-index: 10000;
    font-family: 'Poppins', sans-serif;
}

.chatbot-container.active {
    display: flex;
}

.chatbot-header {
    background: linear-gradient(135deg, #2f3e53ff, #1F2937);
    color: white;
    padding: 15px;
    border-radius: 13px 13px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.chatbot-title h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chatbot-icon {
    font-size: 18px;
    color: white;
}

.chatbot-close {
    background: none;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.3s;
}

.chatbot-close:hover {
    background: rgba(255,255,255,0.2);
}

.chatbot-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    max-width: 85%;
    padding: 12px 15px;
    border-radius: 12px;
    line-height: 1.4;
    word-wrap: break-word;
    font-size: 14px;
}

.bot-message {
    background: #e9ecef;
    align-self: flex-start;
    border-bottom-left-radius: 5px;
    border: 1px solid #dee2e6;
}

.user-message {
    background: #2f3e53ff;
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 5px;
}

.message-content {
    font-size: 14px;
}

.chatbot-suggestions {
    padding: 10px 15px;
    border-top: 1px solid #dee2e6;
    background: white;
}

.chatbot-suggestions small {
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
    font-size: 12px;
}

.suggestion-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.suggestion-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s;
    color: #495057;
    font-weight: 500;
}

.suggestion-btn:hover {
    background: #2f3e53ff;
    color: white;
    border-color: #2f3e53ff;
}

.chatbot-input {
    padding: 15px;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 10px;
    background: white;
    border-radius: 0 0 13px 13px;
}

#chatbot-input {
    flex: 1;
    border: 1px solid #ced4da;
    border-radius: 25px;
    padding: 10px 15px;
    outline: none;
    transition: border-color 0.3s;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

#chatbot-input:focus {
    border-color: #2f3e53ff;
    box-shadow: 0 0 0 0.2rem rgba(47, 62, 83, 0.25);
}

#chatbot-send {
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    background: #2f3e53ff;
    border: none;
    transition: all 0.3s;
}

#chatbot-send:hover {
    background: #1F2937;
    transform: scale(1.05);
}

.chatbot-toggle-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(135deg, #2f3e53ff, #1F2937);
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(47, 62, 83, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
}

.chatbot-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(47, 62, 83, 0.4);
}

/* Scrollbar personalizado */
.chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Typing indicator */
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-style: italic;
}

.typing-dots {
    display: flex;
    gap: 3px;
}

.typing-dot {
    width: 6px;
    height: 6px;
    background: #6c757d;
    border-radius: 50%;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dot:nth-child(1) { animation-delay: -0.32s; }
.typing-dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typing {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1); opacity: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotContainer = document.getElementById('chatbot-container');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const suggestionButtons = document.querySelectorAll('.suggestion-btn');
    
    // Alternar visibilidad del chat
    chatbotToggle.addEventListener('click', function() {
        chatbotContainer.classList.toggle('active');
        chatbotInput.focus();
    });
    
    chatbotClose.addEventListener('click', function() {
        chatbotContainer.classList.remove('active');
    });
    
    // Botones de sugerencias
    suggestionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const question = this.getAttribute('data-question');
            chatbotInput.value = question;
            sendMessage();
        });
    });
    
    // Enviar mensaje
    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message === '') return;
        
        // Agregar mensaje del usuario
        addMessage(message, 'user');
        chatbotInput.value = '';
        
        // Mostrar typing indicator
        showTypingIndicator();
        
        // Construir URL - usando ubicación actual del documento
        const currentPath = window.location.pathname;
        let apiUrl = window.location.origin;
        
        // Si estamos en /Tulook_MVC/... extraer la ruta base
        if (currentPath.includes('/Tulook_MVC/')) {
            apiUrl = window.location.origin + '/Tulook_MVC/api/chatbot.php';
        } else {
            // Alternativa: obtener de la ruta actual
            const pathParts = currentPath.split('/').filter(Boolean);
            if (pathParts.length > 0) {
                apiUrl = window.location.origin + '/' + pathParts[0] + '/api/chatbot.php';
            } else {
                apiUrl = window.location.origin + '/api/chatbot.php';
            }
        }
        
        console.log('URL del API:', apiUrl);
        
        // Enviar al servidor
        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                message: message
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error, status=' + response.status);
            }
            return response.text();
        })
        .then(text => {
            console.log('Respuesta raw del servidor:', text);
            try {
                const data = JSON.parse(text);
                console.log('Respuesta parseada:', data);
                removeTypingIndicator();
                if (data.success) {
                    addMessage(data.response, 'bot');
                } else {
                    addMessage('Error al procesar tu mensaje. Intenta nuevamente.', 'bot');
                }
            } catch(e) {
                console.error('Error parseando JSON:', e);
                console.error('Texto recibido:', text.substring(0, 500));
                removeTypingIndicator();
                addMessage('Error de conexión. Intenta nuevamente.', 'bot');
            }
        })
        .catch(error => {
            console.error('Error en fetch:', error);
            removeTypingIndicator();
            addMessage('Error de conexión. Intenta nuevamente.', 'bot');
        });
    }
    
    
    // Agregar mensaje al chat
    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerHTML = `<strong>${sender === 'bot' ? 'Asistente:' : 'Tú:'}</strong> ${text}`;
        
        messageDiv.appendChild(contentDiv);
        chatbotMessages.appendChild(messageDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
    
    // Indicador de typing mejorado
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot-message';
        typingDiv.id = 'typing-indicator';
        
        const typingContent = document.createElement('div');
        typingContent.className = 'typing-indicator';
        typingContent.innerHTML = `
            <strong>Asistente:</strong>
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        
        typingDiv.appendChild(typingContent);
        chatbotMessages.appendChild(typingDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }
    
    function removeTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    // Event listeners
    chatbotSend.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Cerrar chat al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!chatbotContainer.contains(e.target) && !chatbotToggle.contains(e.target)) {
            chatbotContainer.classList.remove('active');
        }
    });
});
</script>