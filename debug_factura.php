<!DOCTYPE html>
<html>
<head>
    <title>Test IDÉNTICO a tu App</title>
</head>
<body>
    <h1>Test que usa EXACTAMENTE lo mismo que tu app</h1>
    
    <form id="testForm">
        <input type="hidden" name="id_articulo" value="1">
        <input type="hidden" name="id_producto" value="1">
        <input type="hidden" name="calificacion" value="5">
        <input type="hidden" name="titulo" value="Test desde HTML">
        <input type="hidden" name="comentario" value="Esta es una prueba IDÉNTICA a tu app">
    </form>
    
    <button onclick="enviarComoApp()">Enviar COMO TU APP</button>
    
    <div id="resultado" style="margin: 20px; padding: 20px; border: 2px solid #333; min-height: 200px;"></div>
    
    <script>
        async function enviarComoApp() {
            const resultadoDiv = document.getElementById('resultado');
            resultadoDiv.innerHTML = '<p>🔄 Enviando...</p>';
            
            // Usar EXACTAMENTE el mismo código que tu app
            const formData = new FormData();
            formData.append('id_articulo', '1');
            formData.append('id_producto', '1');  
            formData.append('calificacion', '5');
            formData.append('titulo', 'Test desde app');
            formData.append('comentario', 'Esta prueba usa FormData exactamente como tu JavaScript');
            
            console.log('📤 Enviando FormData:', formData);
            
            // URL EXACTA que usa tu app
            const url = 'http://localhost/Tulook_MVC/?c=Resena&a=crear';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                    // NOTA: NO establecer Content-Type header - FormData lo hace automático
                });
                
                console.log('📦 Respuesta:', response);
                
                // Leer como texto primero
                const text = await response.text();
                console.log('📦 Texto respuesta:', text);
                
                let jsonData;
                try {
                    jsonData = JSON.parse(text);
                } catch (e) {
                    jsonData = { raw: text, error: 'No es JSON válido' };
                }
                
                resultadoDiv.innerHTML = `
                    <h3>Resultado:</h3>
                    <p><strong>URL:</strong> ${response.url}</p>
                    <p><strong>Estado:</strong> ${response.status} ${response.statusText}</p>
                    <p><strong>Método:</strong> POST</p>
                    <h4>Respuesta:</h4>
                    <pre style="background: #f0f0f0; padding: 10px;">${JSON.stringify(jsonData, null, 2)}</pre>
                `;
                
                // Verificar headers
                console.log('Headers:', [...response.headers]);
                
            } catch (error) {
                resultadoDiv.innerHTML = `<p style="color: red;">❌ Error: ${error.message}</p>`;
                console.error('Error:', error);
            }
        }
        
        // También probar con fetch simple
        async function testFetchSimple() {
            console.log('Probando fetch simple...');
            const response = await fetch('http://localhost/Tulook_MVC/?c=Resena&a=crear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test=1&id_articulo=1&id_producto=1'
            });
            console.log('Simple fetch status:', response.status);
            console.log('Simple fetch text:', await response.text());
        }
        
        // Ejecutar test simple al cargar
        window.onload = testFetchSimple;
    </script>
</body>
</html>