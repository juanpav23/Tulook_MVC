<?php
class Chatbot {
    
    // Respuestas predefinidas basadas en palabras clave específicas
    private $responses = [
        // TUS RESPUESTAS ACTUALES (las mantengo)
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
            'response' => 'En TuLook tenemos:  Ropa (jeans, camisetas, camisas, sudaderas, bóxers) 👟 Calzado (tenis, zapatos, sandalias) 🕶️ Accesorios (relojes, gafas, perfumes, morrales) para Hombre, Mujer y Niños.'
        ],
        'categorias' => [
            'keywords' => ['categorías', 'tipos de ropa', 'qué ropa', 'clases'],
            'response' => ' Nuestras categorías principales: ROPA (Jeans, Pantalonetas, Camisetas, Camisas, Sudaderas, Bóxers, Lencería) - CALZADO (Tenis, Sandalias, Botas, Chanclas, Crocs) - ACCESORIOS (Gorras, Sombreros, Relojes, Perfumes, Gafas, Morrales, Billeteras, Correas, Llaveros)'
        ],
        'tallas' => [
            'keywords' => ['tallas', 'tamaños', 'medidas', 'qué talla', 'talla'],
            'response' => ' Contamos con diversas tallas: XS, S, M, L, XL, XXL, XXXL y tallas numéricas (28-44). Para niños: 8, 10, 12, 14, 16 años. Consulta nuestra guía de tallas en cada producto.'
        ],
        'precios' => [
            'keywords' => ['precios', 'costos', 'barato', 'caro', 'cuánto cuesta', 'valor'],
            'response' => ' Tenemos precios competitivos desde $25,000 hasta $500,000. Los precios varían según el producto, material y diseño. Revisa los detalles específicos en cada artículo.'
        ],
        'envios' => [
            'keywords' => ['envío', 'domicilio', 'entrega', 'cuánto tarda', 'envían', 'recibo'],
            'response' => ' Realizamos envíos a nivel nacional. Tiempo estimado: 2-5 días hábiles. El costo de envío depende de tu ubicación.'
        ],
        'devoluciones' => [
            'keywords' => ['devolución', 'cambio', 'garantía', 'reembolso', 'arrepentimiento'],
            'response' => ' Aceptamos devoluciones dentro de los 30 días posteriores a la compra, con etiquetas intactas y en perfecto estado.'
        ],
        'soporte' => [
            'keywords' => ['soporte', 'problema', 'error', 'ayuda técnica', 'administración', 'contactar', 'hablar con alguien', 'asesor'],
            'response' => ' Para soporte técnico o contacto directo: Teléfono: +57 1 123 4567 | WhatsApp: +57 300 123 4567 | Email: soporte@tulook.com Horario: Lunes a Viernes 8:00 AM - 6:00 PM'
        ],
        'generos' => [
            'keywords' => ['hombre', 'mujer', 'niños', 'niñas', 'géneros', 'para quién'],
            'response' => ' Tenemos productos para: HOMBRE - MUJER - NIÑOS/NIÑAS. Cada categoría tiene diseños exclusivos.'
        ],
        'colores' => [
            'keywords' => ['colores', 'color', 'disponibles', 'tonos'],
            'response' => ' Disponemos de una amplia gama de colores: Blanco, Negro, Azul, Rojo, Verde, Amarillo, Morado, Gris, Rosado, y muchos más. Revisa las opciones en cada producto.'
        ],
        'pagos' => [
            'keywords' => ['pago', 'tarjeta', 'efectivo', 'pse', 'métodos de pago', 'cómo pagar'],
            'response' => ' Aceptamos: Tarjeta de Crédito/Débito - Efectivo - PSE (Pagos Seguros en Línea)'
        ],
        'stock' => [
            'keywords' => ['disponible', 'stock', 'existencia', 'hay', 'tienen'],
            'response' => ' La disponibilidad varía por producto. Revisa el stock en tiempo real en la página de cada artículo.'
        ],
        'ubicacion' => [
            'keywords' => ['dónde están', 'ubicación', 'tienda física', 'local', 'dirección'],
            'response' => ' Actualmente somos una tienda online. Puedes contactarnos a través de nuestros canales digitales.'
        ],
        
        // NUEVAS RESPUESTAS PARA ALMACÉN DE ROPA
        
        'materiales' => [
            'keywords' => ['material', 'tela', 'algodón', 'poliéster', 'lino', 'seda', 'mezclilla', 'jean'],
            'response' => ' Usamos materiales de alta calidad: Algodón 100%, Poliéster, Lino, Seda, Mezclilla, Tencel, Viscosa y mezclas. Cada producto especifica su composición en la descripción.'
        ],
        
        'cuidado_ropa' => [
            'keywords' => ['lavar', 'cuidados', 'planchar', 'secar', 'lavadora', 'limpieza'],
            'response' => ' Recomendaciones de cuidado: • Lavar con colores similares • Usar agua fría • Planchar a temperatura media • No usar blanqueador • Revisa siempre la etiqueta de cuidado'
        ],
        
        'ofertas' => [
            'keywords' => ['oferta', 'promoción', 'descuento', 'rebaja', 'liquidación', 'barato'],
            'response' => ' ¡Tenemos ofertas especiales! • Descuentos del 20%-50% en temporada • 2x1 en accesorios seleccionados • Envío gratis en compras superiores a $150,000 • Síguenos en redes para más promociones'
        ],
        
        'nuevos_productos' => [
            'keywords' => ['nuevo', 'novedades', 'último', 'reciente', 'lanzamiento'],
            'response' => ' Novedades de esta temporada: • Colección de verano 2024 • Jeans rotados • Camisetas oversize • Vestidos florales • Zapatos ecológicos ¡Visita nuestra sección "Novedades"!'
        ],
        
        'tallas_especiales' => [
            'keywords' => ['plus size', 'talla grande', 'xxl', 'extragrande', 'talla pequeña', 'xs'],
            'response' => ' Tenemos tallas especiales: • Plus Size (hasta 5XL) • Tallas pequeñas (XS) • Tallas altas • Sección especial para cada tipo de cuerpo'
        ],
        
        'compras_mayor' => [
            'keywords' => ['mayor', 'mayoreo', 'cantidad', 'empresa', 'regalos', 'evento'],
            'response' => ' Compras al por mayor: • Descuentos especiales para compras grandes • Personalización para empresas • Regalos corporativos • Contacta a ventas@tulook.com para cotización'
        ],
        
        'credito' => [
            'keywords' => ['crédito', 'financiación', 'cuotas', 'pagar a plazos', 'financiar'],
            'response' => ' Opciones de crédito: • Hasta 12 cuotas sin interés con tarjetas seleccionadas • Financiación directa con aprobación inmediata • Planes especiales para compras mayores a $300,000 • Consulta con nuestro asesor financiero'
        ],
        
        // 🎉 RESPUESTAS DIVERTIDAS Y DESCONTRACTURADAS 🎉
        
        'moda' => [
            'keywords' => ['moda', 'estilo', 'fashion', 'tendencia', 'trendy'],
            'response' => ' ¡Ah, un amante de la moda! Entonces estás en el lugar correcto. En TuLook no solo vendemos ropa, vendemos actitud y confianza. ¿Cuál es tu estilo? ¿Casual? ¿Elegante? ¿Aventurero? ¡Tenemos de todo!'
        ],
        
        'ayuda' => [
            'keywords' => ['ayuda', 'auxilio', 'me ayudas', 'necesito', 'por favor', 'urgente'],
            'response' => ' ¡Claro que te ayudo! Soy tu asistente de moda favorito. Cuéntame qué necesitas y haré lo posible por dejarte ver como una estrella de cine 🌟'
        ],
        
        'talle_perfecto' => [
            'keywords' => ['qué talla', 'cuál talla', 'mi talla', 'me queda bien', 'me ajusta'],
            'response' => ' La talla perfecta es la que te hace sentir cómodo y seguro. Revisa nuestra guía de tallas detallada en cada producto. Si no estás seguro, siempre puedes contactarnos y te asesoramos sin problema 😉'
        ],
        
        'eres_bot' => [
            'keywords' => ['eres bot', 'eres robot', 'no eres humano', 'eres inteligencia artificial', 'quién eres'],
            'response' => ' Sí, soy un bot (asistente virtual), pero te ayudo con la misma pasión que un humano. Aunque no puedo probarte ropa, ¡puedo darte los mejores consejos de moda! 😄'
        ],
        
        'broma' => [
            'keywords' => ['chiste', 'broma', 'jajaja', 'reír', 'chistoso', 'gracioso'],
            'response' => ' Me encanta tu sentido del humor. Aunque no soy comediante (estoy enfocado en moda), aquí va uno: ¿Por qué los jeans nunca van al cine? ¡Porque siempre se sientan solos! 👖 Bueno, eso fue terrible... mejor déjame ayudarte con algo de moda 😅'
        ],
        
        'amor' => [
            'keywords' => ['amor', 'quiero', 'enamorado', 'pareja', 'novia', 'novio', 'amor de mi vida'],
            'response' => ' ¡Ah, el amor! Bueno, quizás no pueda ayudarte con eso, pero SÍ puedo ayudarte a verte tan bien que esa persona especial no pueda resistirse. ¿Quieres que te recomendemos un atuendo para impresionar? 😉'
        ],
        
        'fiesta' => [
            'keywords' => ['fiesta', 'fiestas', 'noche', 'salida', 'discoteca', 'evento', 'celebración'],
            'response' => ' ¡Fiesta en casa! Necesitas verte impactante. En TuLook tenemos: • Ropa de noche elegante • Accesorios deslumbrantes • Calzado cómodo para bailar toda la noche • ¡Listos para conquistar la pista!'
        ],
        
        'trabajo' => [
            'keywords' => ['trabajo', 'oficina', 'profesional', 'entrevista', 'negocio', 'empresarial'],
            'response' => ' Profesionalismo + Estilo = El combo perfecto. Tenemos ropa de oficina impecable que grita "sé lo que hago". Camisas, pantalones, accesorios... ¡Todo lo que necesitas para causar buena impresión!'
        ],
        
        'gym' => [
            'keywords' => ['gym', 'ejercicio', 'deportivo', 'deporte', 'actividad física', 'entrenamiento'],
            'response' => ' ¡Activo, me encanta! Tenemos ropa deportiva que combina comodidad y estilo. Porque ir al gym no significa verse mal. Con TuLook te veras fit en la caminadora 🏃‍♀️'
        ],
        
        'lluvia' => [
            'keywords' => ['lluvia', 'mojado', 'agua', 'sombrilla', 'impermeable', 'clima'],
            'response' => ' La lluvia no es excusa para verse mal. En TuLook tenemos abrigos, impermeables y accesorios que te protegen sin sacrificar estilo. ¡Que llueva, tú te ves impecable!'
        ],
        
        'frio' => [
            'keywords' => ['frío', 'invierno', 'sudadera', 'abrigo', 'chaqueta', 'bufanda'],
            'response' => ' Cuando el frío llega, la moda no se va. Tenemos sudaderas cómodas, abrigos elegantes y capas que te mantienen caliente sin verte como abominable hombre de las nieves 🧊'
        ],
        
        'calor' => [
            'keywords' => ['calor', 'verano', 'shorts', 'bermudas', 'ligero', 'tintorería'],
            'response' => ' ¡Es tiempo de lucir las piernas! En TuLook tenemos: • Shorts y bermudas de todos los colores • Camisetas frescas • Sandalias cómodas • Todo lo que necesitas para vencer el calor con estilo 🔥'
        ],
        
        'cansado' => [
            'keywords' => ['cansado', 'agotado', 'cansancio', 'tirado', 'sin energía'],
            'response' => ' Entiendo perfectamente. Pero mira, un atuendo perfecto puede cambiar tu día. Ponte ropa que te haga sentir bien y verás cómo tu energía sube. ¡La ropa adecuada es lo mejor para el alma! 💫'
        ],
        
        'pobre' => [
            'keywords' => ['pobre', 'sin dinero', 'quebrado', 'sin plata', 'estoy pelado'],
            'response' => ' Hey, no te preocupes. En TuLook tenemos opciones para todos los presupuestos. Especialmente nuestras secciones de ofertas y liquidaciones. ¡Verse bien no siempre tiene que ser caro! 😎'
        ],
        
        'rico' => [
            'keywords' => ['rico', 'millonario', 'dinero', 'plata', 'riqueza', 'gastador'],
            'response' => ' ¡Alguien con poder de compra! Perfectamente, tenemos desde piezas premium hasta colecciones exclusivas. En TuLook satisfacemos todos los gustos y presupuestos. ¿Buscas algo especial? 👑'
        ],
        
        'feo' => [
            'keywords' => ['feo', 'horrible', 'desastre', 'sin estilo', 'fuera de moda'],
            'response' => ' ¡Nada que un buen atuendo no pueda arreglar! Créeme, todo el mundo tiene potencial. En TuLook te ayudamos a brillar y encontrar ese estilo que hace que te sientas increíble. ¡No hay personas sin estilo, solo personas sin inspiración! ✨'
        ],
        
        'covid' => [
            'keywords' => ['covid', 'pandemia', 'confinamiento', 'cuarentena', 'mascarilla'],
            'response' => ' Tiempos difíciles, pero TuLook sigue contigo. Ropa cómoda para estar en casa, cuidados especiales para mantener la higiene... ¡Juntos saldremos adelante! 💪'
        ],
        
        'cambios' => [
            'keywords' => ['cambio', 'talla diferente', 'no me queda', 'otro color'],
            'response' => ' Proceso de cambios: • 30 días para cambios • Producto sin usar y con etiquetas • Puedes cambiar por talla, color o producto equivalente • Contacta a soporte para iniciar el proceso'
        ],
        
        'horarios' => [
            'keywords' => ['horario', 'atencion', 'hasta cuando', 'abren', 'cierran'],
            'response' => ' Horarios de atención: • Lunes a Viernes: 8:00 AM - 8:00 PM • Sábados: 9:00 AM - 6:00 PM • Domingos: 10:00 AM - 4:00 PM • Festivos: Horario especial (consulta)'
        ],
        
        'envio_gratis' => [
            'keywords' => ['envío gratis', 'sin costo envío', 'free shipping', 'sin domicilio'],
            'response' => ' Envío gratuito: • Para compras superiores a $150,000 • Solo en ciudades principales • Promoción válida por tiempo limitado • Aplican términos y condiciones'
        ],
        
        'ropa_interior' => [
            'keywords' => ['boxer', 'calzon', 'lenceria', 'interior', 'medias', 'calcetines'],
            'response' => ' Ropa interior y lencería: • Boxers/tangas de algodón • Brasieres deportivos • Medias y calcetines • Pijamas • Tallas desde XS hasta 3XL • Variedad de colores y estampados'
        ],
        
        'ropa_deportiva' => [
            'keywords' => ['deporte', 'gym', 'ejercicio', 'deportiva', 'running', 'fitness'],
            'response' => ' Ropa deportiva: • Leggings y tops • Shorts de running • Sudaderas con capucha • Tenis especializados • Materiales transpirables • Tecnología dry-fit'
        ],
        
        'ropa_formal' => [
            'keywords' => ['formal', 'elegante', 'traje', 'camisa formal', 'vestido de fiesta', 'etiqueta'],
            'response' => ' Ropa formal: • Trajes completos • Camisas de vestir • Vestidos de noche • Zapatos de cuero • Accesorios elegantes • Tallas desde 36 hasta 52'
        ],
        
        'guia_tallas' => [
            'keywords' => ['guía de tallas', 'medirme', 'como saber mi talla', 'tabla de tallas'],
            'response' => ' Guía de tallas: • Mide tu pecho, cintura y cadera • Compara con nuestra tabla online • Video tutorial disponible • Puedes pedir ayuda a nuestro asesor virtual • Garantía de cambio si no te queda'
        ],
        
        'ropa_ecologica' => [
            'keywords' => ['ecológico', 'sostenible', 'orgánico', 'eco friendly', 'ambiental'],
            'response' => ' Línea ecológica: • Ropa de algodón orgánico • Procesos sostenibles • Tintes naturales • Empaques biodegradables • Apoyo a comunidades locales'
        ],
        
        'regalos' => [
            'keywords' => ['regalo', 'obsequio', 'cumpleaños', 'aniversario', 'detalle'],
            'response' => ' Opciones para regalos: • Empaque especial gratuito • Tarjeta de felicitación • Kits de regalo • Asesoría personalizada • Envío express disponible'
        ],
        
        'seguimiento' => [
            'keywords' => ['seguir pedido', 'donde está mi pedido', 'número de seguimiento', 'rastrear'],
            'response' => ' Seguimiento de pedidos: • Recibirás número de guía por email • Rastrea en tiempo real • App móvil disponible • Soporte para seguimiento: WhatsApp +57 300 123 4567'
        ],
        
        // 🎪 MÁS RESPUESTAS DIVERTIDAS 🎪
        
        'espejo' => [
            'keywords' => ['espejo', 'me veo', 'como me veo', 'reflejo', 'aspecto'],
            'response' => ' Ah, buscas verse bien, ¿verdad? Ese es nuestro trabajo. Con la ropa adecuada, el espejo será tu mejor amigo. ¡Vamos, elije algo que te haga brillar! ✨'
        ],
        
        'aburrido' => [
            'keywords' => ['aburrido', 'aburrimiento', 'sin nada que hacer', 'me aburro'],
            'response' => ' Perfectamente, aquí viene el remedio: navega nuestro catálogo, descubre nuevos estilos, planifica tu próximo look... ¡La moda nunca es aburrida! 🎨'
        ],
        
        'triste' => [
            'keywords' => ['triste', 'tristeza', 'deprimido', 'me siento mal', 'mal día'],
            'response' => ' Hey, la terapia del retail es real. Un atuendo nuevo puede subir tus ánimos como nada. En TuLook te aseguro que algo del catálogo te hará sonreír 💚'
        ],
        
        'feliz' => [
            'keywords' => ['feliz', 'felicidad', 'alegre', 'contento', 'emocionado'],
            'response' => ' ¡Esa es la actitud! La felicidad + un buen outfit = receta para el éxito. Celébra ese buen humor con algo nuevo en TuLook. ¡La vida es una pasarela! 🌟'
        ],
        
        'viaje' => [
            'keywords' => ['viaje', 'viajar', 'vacaciones', 'turismo', 'aventura'],
            'response' => ' ¡Las aventuras esperan! En TuLook tenemos ropa de viaje: • Cómoda pero elegante • Duradera • Práctica para todo clima • ¡Queda bien en cada foto del viaje! 📸'
        ],
        
        'playa' => [
            'keywords' => ['playa', 'arena', 'mar', 'swimwear', 'bikini', 'traje de baño'],
            'response' => ' Arena, sol, mar... y TuLook. Tenemos: • Trajes de baño trendy • Pareos coloridos • Sandalias cómodas • Gafas de sol • ¡Todo lo necesario para ser la reina/rey de la playa! 👑'
        ],
        
        'montaña' => [
            'keywords' => ['montaña', 'senderismo', 'trekking', 'camping', 'naturaleza'],
            'response' => ' Para los aventureros que aman la naturaleza: • Ropa cómoda y resistente • Capas para diferentes temperaturas • Calzado apropiado • ¡Disfruta la montaña con estilo! 🏔️'
        ],
        
        'instinto' => [
            'keywords' => ['instinto', 'intuición', 'qué me recomiendas', 'cuál escojo'],
            'response' => ' Mi instinto de asistente de moda dice: sigue tu corazón. Cada pieza en TuLook está elegida para hacerte feliz. ¿Cuál te llama la atención? ¡Eso es lo correcto! 🎯'
        ],
        
        'tiempo' => [
            'keywords' => ['tiempo', 'hora', 'qué hora', 'cuál es la hora'],
            'response' => ' La hora de verte increíble es AHORA. Olvida el reloj, ¡vamos a comprar! 😄 Pero si preguntas en serio, puedes ver la hora en tu dispositivo.'
        ],
        
        'futuro' => [
            'keywords' => ['futuro', 'mañana', 'después', 'próximo', 'siguiente'],
            'response' => ' El futuro es brillante, especialmente si lo enfrentas con un atuendo que te haga sentir confiado. En TuLook preparamos tu guardarropa para todas tus aventuras futuras 🚀'
        ],
        
        'retro' => [
            'keywords' => ['retro', 'vintage', 'nostalgia', 'años 80', 'años 90', 'clásico'],
            'response' => ' ¡Amante del retro! Tenemos colecciones vintage-inspired que traen esos vibes clásicos con un toque moderno. Lo mejor de ambos mundos 😎'
        ],
        
        'futuristico' => [
            'keywords' => ['futurista', 'moderno', 'innovación', 'tecnológico', 'high-tech'],
            'response' => ' ¡El futuro es ahora! Contamos con diseños cutting-edge y tecnologías innovadoras en nuestras prendas. Porque la moda evoluciona constantemente. ¡Sé parte del futuro! 🤖'
        ],
        
        'artista' => [
            'keywords' => ['artista', 'artístico', 'creativo', 'arte', 'pintura', 'música'],
            'response' => '¡Qué grande! Los artistas merecen ropa que refleje su creatividad. Tenemos piezas únicas con diseños que cuentan historias. Tu guardarropa puede ser tu galería 🖼️'
        ],
        
        'naturaleza' => [
            'keywords' => ['naturaleza', 'ecología', 'sostenible', 'verde', 'orgánico', 'ambiental'],
            'response' => ' Nos importa el planeta. Tenemos opciones sustentables y eco-friendly en nuestro catálogo. Porque verse bien no significa dañar la tierra 🌍💚'
        ],
        
        // 🛍️ RESPUESTAS ESPECÍFICAS DE LA TIENDA 🛍️
        
        'camiseta' => [
            'keywords' => ['camiseta', 'playera', 't-shirt', 'remera', 'musculosa'],
            'response' => ' Las camisetas son la base de cualquier look. Tenemos: • Oversize • Slim fit • Con gráficos • Lisas • Estampadas • Manga corta • Manga larga • Para todos los gustos. ¿Cuál necesitas? 🎨'
        ],
        
        'botas' => [
            'keywords' => ['botas', 'bota', 'botina'],
            'response' => ' Las botas son versátiles y elegantes. Ofrecemos: • Botas al tobillo • Botas altas • Botas de cuero • Botas deportivas • Para invierno o cualquier temporada • ¡Comodidad garantizada! 👞'
        ],
        
        'accesorios' => [
            'keywords' => ['accesorios', 'gorras', 'sombreros', 'relojes', 'gafas', 'mochilas', 'billeteras', 'correas'],
            'response' => ' Los accesorios son los detalles que elevan cualquier look. En TuLook encontrarás: • Gorras y sombreros • Relojes elegantes • Gafas de sol • Morrales • Billeteras • Correas • Llaveros • Perfumes • ¡El toque final perfecto! ✨'
        ],
        
        'ropa_mujer_especial' => [
            'keywords' => ['ropa mujer', 'femenina', 'para mujeres', 'damas', 'chicas'],
            'response' => ' Nuestra colección femenina es espectacular: • Vestidos • Blusas • Faldas • Leggins • Jeans • Accesorios • Toda con estilo y comodidad. ¡Explora y encuentra tu favorita! 💃'
        ],
        
        'ropa_hombre_especial' => [
            'keywords' => ['ropa hombre', 'masculina', 'para hombres', 'caballeros', 'chicos'],
            'response' => ' La colección para caballeros incluye: • Camisas • Camisetas • Pantalones • Jeans • Sudaderas • Chaquetas • Accesorios premium • ¡Elegancia y comodidad garantizadas! 💪'
        ],
        
        'ropa_infantil' => [
            'keywords' => ['ropa niños', 'infantil', 'bebés', 'pequeñitos', 'para niños'],
            'response' => ' Los más pequeños también merecen estilo. Tenemos ropa infantil: • Cómoda y segura • Diseños coloridos • Tallas variadas • Materiales suaves • Perfecta para el juego y la escuela • ¡Los niños lucirán increíbles! 🌈'
        ],
        
        'hoodie' => [
            'keywords' => ['sudadera', 'hoodie', 'capucha', 'sweatshirt', 'sueter'],
            'response' => ' Las sudaderas son comodidad pura. Ofrecemos: • Sudaderas con capucha • Sin capucha • Diferentes colores • Estampadas • Lisas • Ideales para el invierno o casual diario • ¡Tu nueva favorita está aquí! 🤗'
        ],
        
        'faldas_minifaldas' => [
            'keywords' => ['falda', 'minifalda', 'falda larga', 'pollera'],
            'response' => ' Las faldas son feminidad y elegancia. Tenemos: • Faldas cortas • Faldas midi • Faldas maxi • Plisadas • Ajustadas • Acampanadas • De todos los colores • ¡El complemento perfecto! 💫'
        ],
        
        'vestidos_especiales' => [
            'keywords' => ['vestido', 'dress', 'vestidito'],
            'response' => ' Los vestidos son protagonistas absolutos. Descubre: • Vestidos casuales • Elegantes • De fiesta • Estampados • Lisos • Cortos • Largos • Para cualquier ocasión • ¡Te verás hermosa! 👰'
        ],
        
        'carrito' => [
            'keywords' => ['carrito', 'carrito compras', 'carro de compras', 'compra', 'agregar'],
            'response' => ' ¿Listo para comprar? En TuLook es muy fácil: 1) Selecciona los productos que te gustan 2) Agrégalos al carrito 3) Procede al checkout 4) Elige tu método de pago 5) ¡Confirmado! Recibirás tu pedido en 2-5 días. ¡Que disfrutes! 😊'
        ],
        
        'bienvenida_nuevos' => [
            'keywords' => ['soy nuevo', 'primera compra', 'recién llegué', 'primer pedido'],
            'response' => ' ¡Bienvenido a TuLook! Estamos felices de que te unas. Como nuevo cliente: • Puedes navegar nuestro catálogo sin crear cuenta • Necesitarás crear una al checkout • Es muy rápido • Recibirás novedades en tu email • ¡Prepárate para una experiencia increíble! 🌟'
        ],
        
        'mi_cuenta' => [
            'keywords' => ['mi cuenta', 'perfil', 'usuario', 'acceso', 'login'],
            'response' => ' Tu cuenta en TuLook: • Guarda tus compras favoritas • Acceso rápido al checkout • Historial de pedidos • Ofertas personalizadas • Cambiar datos en cualquier momento • ¡Tu espacio personal! 👤'
        ],
        
        'me_encanta' => [
            'keywords' => ['favoritos', 'guardar', 'me encanta', 'likes', 'wishlist'],
            'response' => ' ¡La función de favoritos es genial! Puedes: • Guardar productos que ames • Crear tu wishlist • Compartir con amigos • Recibir notificaciones si hay cambios de precio • ¡Nunca pierdas esa prenda que amaste! 💕'
        ],
        
        'reseñas' => [
            'keywords' => ['comentarios', 'reseña', 'opinión', 'crítica', 'puntuación', 'rating'],
            'response' => ' Las opiniones de nuestros clientes son oro. En TuLook: • Puedes dejar reseñas de tus compras • Ver opiniones de otros clientes • Calificar con estrellas • Ayudar a otros a elegir bien • ¡Tu voz importa! 🗣️'
        ],
        
        'buscar_filtrar' => [
            'keywords' => ['filtro', 'buscar', 'búsqueda', 'criterio'],
            'response' => ' Usa nuestros filtros para encontrar exactamente lo que quieres: • Por categoría • Por talla • Por color • Por precio • Por género • Por marca • ¡La búsqueda perfecta en segundos! 🎯'
        ],
        
        'carrito_lleno' => [
            'keywords' => ['carrito lleno', 'carrito completo', 'muchos items'],
            'response' => ' ¡Tu carrito tiene cantidad! Perfecto, eso significa que encontraste lo que te encanta. Continúa agregando o procede al checkout cuando estés listo. ¡Vamos a hacerlo realidad! 🎉'
        ],
        
        'descuentos_codigos' => [
            'keywords' => ['código', 'cupón', 'voucher', 'código descuento'],
            'response' => ' ¿Tienes un código de descuento? Excelente: • Agrégalo en el checkout • Se aplicará automáticamente • Verás el ahorro en tu total • Algunos códigos tienen restricciones especiales • ¡Ahorra más con TuLook! 💰'
        ],
        
        'rastrear_pedido' => [
            'keywords' => ['rastrear', 'dónde está', 'estado pedido', 'seguimiento'],
            'response' => ' Para rastrear tu pedido: 1) Entra a Tu Cuenta 2) Ve a Mis Pedidos 3) Selecciona el pedido 4) Verás el número de guía 5) Usa ese número en el transportista • Tendrás updates por email también • ¡Conocerás exactamente dónde está tu compra! 🚚'
        ],
        
        'encontrar_talla' => [
            'keywords' => ['cuál es mi talla', 'no sé talla', 'guía tallas'],
            'response' => ' Para encontrar tu talla perfecta: 1) Abre la guía de tallas del producto 2) Mide según las instrucciones 3) Compara con la tabla 4) Si dudas, contacta soporte • En cada producto hay instrucciones claras • ¡Nunca más una talla incorrecta! ✅'
        ],
        
        'metodos_pago' => [
            'keywords' => ['formas pago', 'métodos de pago', 'cómo pagar'],
            'response' => ' En TuLook aceptamos múltiples formas de pago: • Tarjeta de Crédito/Débito • PSE (Pagos Seguros en Línea) • Efectivo contra entrega • Transferencia bancaria • Billeteras digitales • ¡Elige la que prefieras! 💰'
        ],
        
        'seguridad_datos' => [
            'keywords' => ['segura', 'seguridad', 'confianza', 'protección', 'datos seguros'],
            'response' => ' Tu seguridad es prioridad: • Encriptación SSL de 128 bits • Protección de datos personales • Plataforma certificada • Privacidad garantizada • Políticas claras • ¡Compra con total confianza! 🛡️'
        ],
        
        'mis_compras' => [
            'keywords' => ['historial', 'compras anteriores', 'pedidos anteriores'],
            'response' => ' Tu historial de compras: • Acceso en cualquier momento • Detalles de cada pedido • Recibos digitales • Reordenar productos anteriores • Seguimiento completo • ¡Todo tu histórico en un lugar! 📑'
        ],
        
        'cambio_producto' => [
            'keywords' => ['devolver', 'cambiar producto', 'no me gusta', 'llegó mal'],
            'response' => ' Proceso de cambio/devolución: 1) Contacta soporte en 30 días 2) Explica el motivo 3) Envía la prenda en perfectas condiciones 4) Recibe tu reembolso o cambio • Envío de retorno pagado • ¡Sin complicaciones! ✅'
        ],
        
        'envio_rapido' => [
            'keywords' => ['express', 'urgente', 'rápido', 'mismo día'],
            'response' => ' ¿Necesitas tu compra rápido? Ofrecemos: • Envío express (24 horas) • Envío standard (2-5 días) • Entrega en ciudades principales • Rastreo en tiempo real • ¡Llega cuando la necesitas! 🚀'
        ],
        
        'regalo_especial' => [
            'keywords' => ['regalo', 'regalar', 'cumpleaños', 'navidad', 'aniversario'],
            'response' => ' TuLook es perfecto para regalos: • Envoltorio especial gratuito • Tarjeta personalizada • Opción de regalar directo • Gift cards disponibles • Empaque premium • ¡Sorprende a alguien especial! 💝'
        ],
        
        'eventos_corporativos' => [
            'keywords' => ['bodas', 'graduación', 'eventos', 'colección limitada'],
            'response' => ' Para eventos especiales: • Colecciones exclusivas • Diseños limitados • Prendas premium • Asesoría personalizada • Entregas a tiempo • ¡Sé la estrella del evento! 🌟'
        ],
        
        'app_movil' => [
            'keywords' => ['app', 'celular', 'móvil', 'android', 'ios'],
            'response' => ' ¡Descarga nuestra app! • Compra desde tu celular • Notificaciones de ofertas • Acceso rápido a favoritos • Mejor experiencia visual • Descuentos exclusivos en app • ¡Disponible en iOS y Android! 📲'
        ],
        
        'redes' => [
            'keywords' => ['instagram', 'facebook', 'redes', 'twitter', 'tiktok', 'síguenos'],
            'response' => ' ¡Síguenos en redes! Encontrarás: • Contenido exclusivo • Consejos de moda • Fotos de clientes • Concursos • Promociones especiales • Novedades antes que nadie • @TuLook en Instagram, Facebook y TikTok 💫'
        ],
        
        'email_ofertas' => [
            'keywords' => ['newsletter', 'suscribir', 'email', 'notificaciones'],
            'response' => ' ¡Suscríbete a nuestro newsletter! Recibe: • Ofertas exclusivas • Novedades primero • Cupones especiales • Consejos de moda • Solo lo que importa • Sin spam, promesa • ¡Tu inbox será mejor! 💌'
        ],
        
        'tallas_ninos' => [
            'keywords' => ['talla niños', 'infantil niños', 'pequeñitos talla'],
            'response' => ' Tallas para niños en TuLook: • 8, 10, 12, 14, 16 años • Cómodas y seguras • Diseños coloridos • Materiales suaves • Perfectas para crecer • ¡Nuestros peques estarán felices! 🎨'
        ]
    ];



    
    public function getResponse($message) {
        $message = strtolower(trim($message));
        
        // Respuestas exactas para preguntas específicas (ampliadas)
        $exactResponses = [
            'hola' => '¡Hola!  Bienvenido a TuLook Moda. ¿En qué puedo ayudarte?',
            'hola!' => '¡Hola!  ¿Listo para encontrar tu estilo perfecto en TuLook?',
            'gracias' => '¡De nada!  ¿Hay algo más en lo que pueda ayudarte?',
            'ok' => '¡Perfecto! ¿Necesitas información sobre algún producto en específico?',
            'sí' => '¡Genial! ¿Sobre qué producto o categoría te gustaría saber?',
            'no' => 'Entendido. Si cambias de opinión, aquí estaré para ayudarte.',
            'adiós' => '¡Hasta luego!  Espero verte pronto en TuLook.',
            'chao' => '¡Chao!  Que tengas un excelente día.',
            'crédito' => ' Ofrecemos varias opciones de crédito: Hasta 12 cuotas sin interés, financiación directa y planes especiales para compras grandes. ¿Te gustaría conocer los detalles?',
            'credito' => ' Tenemos planes de crédito disponibles: • Hasta 12 meses sin intereses • Aprobación en minutos • Sin trámites complicados • ¿Necesitas más información?'
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
        
        // Respuesta por defecto mejorada
        return "🤔 No estoy seguro de entender tu pregunta. Puedo ayudarte con: productos, tallas, precios, envíos, devoluciones, ofertas, crédito, cambios, materiales, cuidados de ropa, ropa deportiva/formal, y mucho más. ¿Podrías reformular tu pregunta?";
    }
}
?>