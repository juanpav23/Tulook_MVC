<?php
class Chatbot {
    
    // Respuestas predefinidas basadas en palabras clave específicas
    private $responses = [
        'saludo' => [
            'keywords' => ['hola', 'buenos días', 'buenas tardes', 'buenas noches', 'hey', 'saludos'],
            'response' => '¡Hola! Bienvenido a TuLook Moda. ¿En qué puedo ayudarte? Puedes preguntarme sobre: productos, categorías, tallas, precios, envíos o contactar soporte.'
        ],
        'despedida' => [
            'keywords' => ['adios', 'chao', 'hasta luego', 'nos vemos', 'bye', 'gracias'],
            'response' => '¡Hasta luego! Gracias por visitar TuLook. ¡Vuelve pronto!'
        ],
        'productos' => [
            'keywords' => ['productos', 'qué venden', 'qué tienen', 'mercancía', 'artículos'],
            'response' => 'En TuLook tenemos: 👕 Ropa (jeans, camisetas, camisas, sudaderas, bóxers) 👟 Calzado (tenis, zapatos, sandalias) 🕶️ Accesorios (relojes, gafas, perfumes, morrales) para Hombre, Mujer y Niños.'
        ],
        'categorias' => [
            'keywords' => ['categorías', 'tipos de ropa', 'qué ropa', 'clases'],
            'response' => '📋 Nuestras categorías principales: ROPA (Jeans, Pantalonetas, Camisetas, Camisas, Sudaderas, Bóxers, Lencería) - CALZADO (Tenis, Sandalias, Botas, Chanclas, Crocs) - ACCESORIOS (Gorras, Sombreros, Relojes, Perfumes, Gafas, Morrales, Billeteras, Correas, Llaveros)'
        ],
        'tallas' => [
            'keywords' => ['tallas', 'tamaños', 'medidas', 'qué talla', 'talla'],
            'response' => '📏 Contamos con diversas tallas: XS, S, M, L, XL, XXL, XXXL y tallas numéricas (28-44). Para niños: 8, 10, 12, 14, 16 años. Consulta nuestra guía de tallas en cada producto.'
        ],
        'precios' => [
            'keywords' => ['precios', 'costos', 'barato', 'caro', 'cuánto cuesta', 'valor'],
            'response' => '💰 Tenemos precios competitivos desde $25,000 hasta $500,000. Los precios varían según el producto, material y diseño. Revisa los detalles específicos en cada artículo.'
        ],
        'envios' => [
            'keywords' => ['envío', 'domicilio', 'entrega', 'cuánto tarda', 'envían', 'recibo'],
            'response' => '🚚 Realizamos envíos a nivel nacional. Tiempo estimado: 2-5 días hábiles. El costo de envío depende de tu ubicación.'
        ],
        'devoluciones' => [
            'keywords' => ['devolución', 'cambio', 'garantía', 'reembolso', 'arrepentimiento'],
            'response' => '🔄 Aceptamos devoluciones dentro de los 30 días posteriores a la compra, con etiquetas intactas y en perfecto estado.'
        ],
        'soporte' => [
            'keywords' => ['soporte', 'problema', 'error', 'ayuda técnica', 'administración', 'contactar', 'hablar con alguien', 'asesor'],
            'response' => '📞 Para soporte técnico o contacto directo: Teléfono: +57 1 123 4567 | WhatsApp: +57 300 123 4567 | Email: soporte@tulook.com Horario: Lunes a Viernes 8:00 AM - 6:00 PM'
        ],
        'generos' => [
            'keywords' => ['hombre', 'mujer', 'niños', 'niñas', 'géneros', 'para quién'],
            'response' => '👥 Tenemos productos para: HOMBRE - MUJER - NIÑOS/NIÑAS. Cada categoría tiene diseños exclusivos.'
        ],
        'colores' => [
            'keywords' => ['colores', 'color', 'disponibles', 'tonos'],
            'response' => '🎨 Disponemos de una amplia gama de colores: Blanco, Negro, Azul, Rojo, Verde, Amarillo, Morado, Gris, Rosado, y muchos más. Revisa las opciones en cada producto.'
        ],
        'pagos' => [
            'keywords' => ['pago', 'tarjeta', 'efectivo', 'pse', 'métodos de pago', 'cómo pagar'],
            'response' => '💳 Aceptamos: Tarjeta de Crédito/Débito - Efectivo - PSE (Pagos Seguros en Línea)'
        ],
        'stock' => [
            'keywords' => ['disponible', 'stock', 'existencia', 'hay', 'tienen'],
            'response' => '📦 La disponibilidad varía por producto. Revisa el stock en tiempo real en la página de cada artículo.'
        ],
        'ubicacion' => [
            'keywords' => ['dónde están', 'ubicación', 'tienda física', 'local', 'dirección'],
            'response' => '📍 Actualmente somos una tienda online. Puedes contactarnos a través de nuestros canales digitales.'
        ]
    ];
    
    public function getResponse($message) {
        $message = strtolower(trim($message));
        
        // Respuestas exactas para preguntas específicas
        $exactResponses = [
            'hola' => '¡Hola! 😊 Bienvenido a TuLook Moda. ¿En qué puedo ayudarte?',
            'hola!' => '¡Hola! 😊 ¿Listo para encontrar tu estilo perfecto en TuLook?',
            'gracias' => '¡De nada! 😊 ¿Hay algo más en lo que pueda ayudarte?',
            'ok' => '¡Perfecto! ¿Necesitas información sobre algún producto en específico?',
            'sí' => '¡Genial! ¿Sobre qué producto o categoría te gustaría saber?',
            'no' => 'Entendido. Si cambias de opinión, aquí estaré para ayudarte.',
            'adiós' => '¡Hasta luego! 👋 Espero verte pronto en TuLook.',
            'chao' => '¡Chao! 😊 Que tengas un excelente día.'
        ];
        
        // Verificar respuestas exactas primero
        if (array_key_exists($message, $exactResponses)) {
            return $exactResponses[$message];
        }
        
        // Buscar coincidencias con palabras clave
        foreach ($this->responses as $category => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $data['response'];
                }
            }
        }
        
        // Respuesta por defecto
        return "🤔 No estoy seguro de entender tu pregunta. Puedo ayudarte con: información de productos, categorías, tallas, precios, envíos, devoluciones o contactar soporte. ¿Podrías ser más específico?";
    }
}
?>