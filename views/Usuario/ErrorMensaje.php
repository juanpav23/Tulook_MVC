<?php
// views/usuario/ErrorMensaje.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - TuLook</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Basta.css">
    <!-- Agregar Google Fonts para tipografías más atractivas -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <style>
        .error-container {
            width: 100%;
            max-width: 380px;
            padding: 50px 30px !important;
            background: #fff;
            position: absolute;
            border-radius: 80px;
            border: 3px solid #8bb9ef;
            box-shadow: 0 8px 32px 0 rgba(0, 119, 255, 0.15);
            text-align: center;
        }
        
        .error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .error-title {
            color: #1F2937;
            margin-bottom: 20px;
            font-size: 28px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
        }
        
        .error-message {
            color: #555;
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .error-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid #e74c3c;
            text-align: left;
        }
        
        .btn-volver {
            padding: 12px 40px;
            border: none;
            font-size: 16px;
            background: #1F2937;
            color: white;
            cursor: pointer;
            outline: none;
            border-radius: 80px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-volver:hover {
            background: #1F2937;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .error-list {
            text-align: left;
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .error-list li {
            margin-bottom: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <main>
        <div class="contenedor__todo">
            <div class="caja__trasera">
                <div class="caja__trasera-login">
                    <h3>¿Ya tienes una cuenta?</h3>
                    <p>Inicia sesión para entrar en la página</p>
                    <button id="btn_iniciar_sesion">Iniciar sesión</button>
                </div>
                <div class="caja__trasera_register">
                    <h3>¿Aún no tienes una cuenta?</h3>
                    <p>Regístrate para que puedas iniciar sesión</p>
                    <button id="btn__registrarse">Registrarse</button>
                </div>
            </div>

            <div class="contenedor__login_register">
                <!-- CONTENEDOR DE ERROR -->
                <div class="error-container">
                    <div class="error-icon">⚠️</div>
                    <h2 class="error-title">Error de Validación</h2>
                    <div class="error-message">
                        <?php 
                        if (isset($_SESSION['error_message'])) {
                            echo '<div class="error-details">';
                            echo htmlspecialchars($_SESSION['error_message']);
                            
                            // Mostrar detalles adicionales si existen
                            if (isset($_SESSION['error_details'])) {
                                echo '<ul class="error-list">';
                                foreach ($_SESSION['error_details'] as $detail) {
                                    echo '<li>' . htmlspecialchars($detail) . '</li>';
                                }
                                echo '</ul>';
                                unset($_SESSION['error_details']);
                            }
                            
                            echo '</div>';
                            unset($_SESSION['error_message']);
                        } else {
                            echo '<div class="error-details">Ha ocurrido un error inesperado.</div>';
                        }
                        ?>
                    </div>
                    <!-- CORREGIDO: Usar la misma URL que en el login -->
                    <a href="<?php echo BASE_URL; ?>?c=Usuario&a=login" class="btn-volver">Volver al Login</a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Script para manejar los botones - CORREGIDO
        document.getElementById('btn_iniciar_sesion').addEventListener('click', function() {
            // Redirigir al formulario de login
            window.location.href = "<?php echo BASE_URL; ?>?c=Usuario&a=login";
        });

        document.getElementById('btn__registrarse').addEventListener('click', function() {
            // Redirigir al formulario de registro (mostrar el formulario)
            // Si tienes un método específico para mostrar el registro, úsalo aquí
            // Si no, redirige al login y muestra el formulario de registro
            window.location.href = "<?php echo BASE_URL; ?>?c=Usuario&a=login";
        });
    </script>
</body>
</html>