<?php
// Verificación más robusta al inicio del archivo
if (isset($_SESSION['usuario'])) {
    // Headers adicionales para prevenir cache en la redirección
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: " . BASE_URL);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuLook - Login</title>

    <!-- META TAGS PARA PREVENIR CACHE -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">    

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Basta.css">
    <!-- Agregar Google Fonts para tipografías más atractivas -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
                <!-- LOGIN -->
                <form action="<?php echo BASE_URL . '?c=Usuario&a=login'; ?>" method="POST" class="formulario__login" onsubmit="return validarLogin()">
                    <h2>Iniciar sesión</h2>
                    
                    <!-- Mostrar errores generales -->
                    <?php if (isset($_SESSION['error_message']) && (!isset($_SESSION['error_type']) || $_SESSION['error_type'] !== 'registro')): ?>
                        <div id="error-mensaje-login" class="error-mensaje" style="color:red; text-align:center; margin-bottom:15px; padding:10px; background:#ffe6e6; border-radius:5px;">
                            <?php 
                            echo htmlspecialchars($_SESSION['error_message']);
                            if (isset($_SESSION['error_details'])) {
                                echo '<ul style="text-align:left; margin:10px 0; padding-left:20px;">';
                                foreach ($_SESSION['error_details'] as $detail) {
                                    echo '<li>' . htmlspecialchars($detail) . '</li>';
                                }
                                echo '</ul>';
                                unset($_SESSION['error_details']);
                            }
                            unset($_SESSION['error_message']);
                            unset($_SESSION['error_type']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_registro'])): ?>
                        <p id="success-mensaje-login" style="color:green; text-align:center; padding:10px; background:#e6ffe6; border-radius:5px;"><?php echo $_SESSION['success_registro']; unset($_SESSION['success_registro']); ?></p>
                    <?php endif; ?>
                    
                    <input type="email" placeholder="Correo electrónico" name="Correo" id="login_correo" required maxlength="50">
                    <div class="password-input-container">
                        <input type="password" placeholder="Contraseña" name="Contrasena" id="login_contrasena" required minlength="12" maxlength="20">
                        <button type="button" class="password-toggle" onclick="togglePassword('login_contrasena')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="botones-form">
                        <button type="submit">Entrar</button>
                        <button type="button" onclick="limpiarFormularioLogin()">Limpiar</button>
                    </div>

                    <!-- ENLACE OLVIDO CONTRASEÑA -->
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>?c=Usuario&a=olvidoContrasena" 
                        style="color: #2f3e53; text-decoration: none; font-size: 14px;">
                            <i class="fas fa-key me-1"></i> ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                </form>

                <!-- REGISTRO -->
                <form action="<?php echo BASE_URL . '?c=Usuario&a=registrar'; ?>" method="POST" class="formulario__register" onsubmit="return validarRegistro()">
                    <h2>Registrarse</h2>

                    <!-- Mostrar errores de registro -->
                    <?php if (isset($_SESSION['error_message']) && isset($_SESSION['error_type']) && $_SESSION['error_type'] === 'registro'): ?>
                        <div id="error-mensaje-registro" class="error-mensaje" style="color:red; text-align:center; margin-bottom:15px; padding:10px; background:#ffe6e6; border-radius:5px;">
                            <?php 
                            echo htmlspecialchars($_SESSION['error_message']);
                            if (isset($_SESSION['error_details'])) {
                                echo '<ul style="text-align:left; margin:10px 0; padding-left:20px; font-size:12px;">';
                                foreach ($_SESSION['error_details'] as $detail) {
                                    echo '<li>' . htmlspecialchars($detail) . '</li>';
                                }
                                echo '</ul>';
                                unset($_SESSION['error_details']);
                            }
                            unset($_SESSION['error_message']);
                            unset($_SESSION['error_type']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <input type="text" placeholder="Nombre completo" name="Nombre" id="reg_nombre" required minlength="2" maxlength="50" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}" title="Solo letras y espacios (2-50 caracteres)">
                    <input type="text" placeholder="Apellido completo" name="Apellido" id="reg_apellido" required minlength="2" maxlength="50" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}" title="Solo letras y espacios (2-50 caracteres)">

                    <select name="ID_TD" class="form-select" id="reg_tipo_documento" required>
                        <option value="" disabled selected>Seleccione el tipo de documento</option>
                        <?php foreach ($tipo_docs as $doc): ?>
                            <option value="<?php echo $doc['ID_TD']; ?>"><?php echo $doc['Documento']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" placeholder="Número de Documento" name="N_Documento" id="reg_documento" required pattern="[0-9]{7,12}" maxlength="12" title="Solo números (7-12 dígitos)">
                    <input type="text" placeholder="Celular" name="Celular" id="reg_celular" required pattern="[0-9]{10}" maxlength="10" title="Solo números (10 dígitos)">
                    <input type="email" placeholder="Correo Electrónico" name="Correo" id="reg_correo" required maxlength="100">
                    <div class="password-input-container">
                        <input type="password" placeholder="Contraseña" name="Contrasena" id="reg_contrasena" required minlength="12" maxlength="20" title="Mínimo 12 caracteres, 2 números, 1 símbolo, 1 mayúscula y 1 minúscula">
                        <button type="button" class="password-toggle" onclick="togglePassword('reg_contrasena')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small style="display:block; text-align:left; margin:5px 0; color:#666;">La contraseña debe tener mínimo 12 y máximo 20 caracteres, 2 números, 1 símbolo (!@#$), 1 mayúscula y 1 minúscula</small>
                    <div class="password-input-container">
                        <input type="password" placeholder="Confirmar Contraseña" name="Confirmar_Contrasena" id="reg_confirmar_contrasena" required maxlength="20">
                        <button type="button" class="password-toggle" onclick="togglePassword('reg_confirmar_contrasena')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                        <div class="botones-form">
                            <button type="submit">Registrarse</button>
                            <button type="button" onclick="limpiarFormularioRegistro()">Limpiar</button>
                        </div>
                </form>
            </div>
        </div>
    </main>
    <script>
        // Validación del lado del cliente para registro
        function validarRegistro() {
            const nombre = document.getElementById('reg_nombre').value;
            const apellido = document.getElementById('reg_apellido').value;
            const documento = document.getElementById('reg_documento').value;
            const celular = document.getElementById('reg_celular').value;
            const correo = document.getElementById('reg_correo').value;
            const contrasena = document.getElementById('reg_contrasena').value;
            const confirmarContrasena = document.getElementById('reg_confirmar_contrasena').value;

            let errores = [];

            // Validar nombre (solo letras y espacios)
            const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/;
            if (!nombreRegex.test(nombre)) {
                errores.push('El nombre solo puede contener letras y espacios (2-50 caracteres).');
            }

            // Validar apellido (solo letras y espacios)
            if (!nombreRegex.test(apellido)) {
                errores.push('El apellido solo puede contener letras y espacios (2-50 caracteres).');
            }

            // Validar documento (solo números)
            const docRegex = /^[0-9]{7,12}$/;
            if (!docRegex.test(documento)) {
                errores.push('El documento debe contener solo números (7-12 dígitos).');
            }

            // Validar celular (solo números, 10 dígitos)
            const celularRegex = /^[0-9]{10}$/;
            if (!celularRegex.test(celular)) {
                errores.push('El celular debe contener exactamente 10 dígitos numéricos.');
            }

            // Validar correo
            const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!correoRegex.test(correo)) {
                errores.push('Ingrese un correo electrónico válido (ejemplo: usuario@dominio.com).');
            }

            // Validar contraseña con requisitos específicos
            if (contrasena.length < 12) {
                errores.push('La contraseña debe tener mínimo 12 caracteres.');
            }
            
            // Contar números
            const numeros = (contrasena.match(/[0-9]/g) || []).length;
            if (numeros < 2) {
                errores.push('La contraseña debe tener al menos 2 números.');
            }
            
            // Verificar símbolos
            if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(contrasena)) {
                errores.push('La contraseña debe tener al menos 1 símbolo especial (!@#$%^&* etc.).');
            }
            
            // Verificar mayúsculas
            if (!/[A-Z]/.test(contrasena)) {
                errores.push('La contraseña debe tener al menos 1 letra mayúscula.');
            }
            
            // Verificar minúsculas
            if (!/[a-z]/.test(contrasena)) {
                errores.push('La contraseña debe tener al menos 1 letra minúscula.');
            }

            // Verificar que las contraseñas coincidan
            if (contrasena !== confirmarContrasena) {
                errores.push('Las contraseñas no coinciden.');
            }

            if (errores.length > 0) {
                alert('Errores en el formulario:\n\n• ' + errores.join('\n• '));
                return false;
            }

            return true;
        }

        // Validación del lado del cliente para login
        function validarLogin() {
            const correo = document.getElementById('login_correo').value;
            const contrasena = document.getElementById('login_contrasena').value;

            let errores = [];

            // Validar correo
            const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!correoRegex.test(correo)) {
                errores.push('Ingrese un correo electrónico válido.');
            }

            if (contrasena.length < 12) {
                errores.push('La contraseña debe tener al menos 12 caracteres.');
            }

            if (errores.length > 0) {
                alert('Errores en el inicio de sesión:\n\n• ' + errores.join('\n• '));
                return false;
            }

            return true;
        }

        // Función para limpiar formulario de login
        function limpiarFormularioLogin() {
            document.getElementById('login_correo').value = '';
            document.getElementById('login_contrasena').value = '';
            
            // Limpiar mensajes de error y éxito
            const errorMensaje = document.getElementById('error-mensaje-login');
            const successMensaje = document.getElementById('success-mensaje-login');
            
            if (errorMensaje) {
                errorMensaje.style.display = 'none';
            }
            if (successMensaje) {
                successMensaje.style.display = 'none';
            }
        }

        // Función para limpiar formulario de registro
        function limpiarFormularioRegistro() {
            // Limpiar todos los campos del formulario
            document.getElementById('reg_nombre').value = '';
            document.getElementById('reg_apellido').value = '';
            document.getElementById('reg_tipo_documento').selectedIndex = 0;
            document.getElementById('reg_documento').value = '';
            document.getElementById('reg_celular').value = '';
            document.getElementById('reg_correo').value = '';
            document.getElementById('reg_contrasena').value = '';
            document.getElementById('reg_confirmar_contrasena').value = '';
            
            // Limpiar mensajes de error
            const errorMensaje = document.getElementById('error-mensaje-registro');
            if (errorMensaje) {
                errorMensaje.style.display = 'none';
            }
        }

        // Restricciones en tiempo real para inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Solo letras para nombre y apellido
            document.getElementById('reg_nombre').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });

            document.getElementById('reg_apellido').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });

            // Solo números para documento y celular
            document.getElementById('reg_documento').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            document.getElementById('reg_celular').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Mostrar automáticamente el formulario de registro si hay errores de registro
            <?php if (isset($mostrarRegistro) && $mostrarRegistro): ?>
                setTimeout(function() {
                    register();
                }, 100);
            <?php endif; ?>
        });

        // Script para prevenir que el navegador cachee la página
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };

        // Prevenir que se use el cache al navegar hacia atrás
        if (performance.navigation.type === 2) {
            // Si la página fue cargada desde cache (back/forward)
            location.reload();
        }

        // Limpiar formularios cuando la página se carga desde cache
        window.onload = function() {
            // Limpiar campos sensibles si la página fue cargada desde cache
            if (performance.getEntriesByType("navigation")[0].type === "back_forward") {
                document.getElementById('login_correo').value = '';
                document.getElementById('login_contrasena').value = '';
                document.getElementById('reg_contrasena').value = '';
                document.getElementById('reg_confirmar_contrasena').value = '';
            }
        };

    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>