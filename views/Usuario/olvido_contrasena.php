<?php
// views/usuario/olvido_contrasena.php
if (isset($_SESSION['usuario'])) {
    header("Location: " . BASE_URL);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - TuLook</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2f3e53ff;
            --secondary-color: #f8f9fa;
            --text-dark: #343a40;
            --text-light: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .password-recovery-card {
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(to right, var(--primary-color), #1F2937);
            color: white;
            padding: 1.5rem;
            border-bottom: none;
            text-align: center;
        }
        
        .card-header-custom i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .btn-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-custom:hover {
            background: #1F2937;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(47, 62, 83, 0.25);
        }
        
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        
        .step {
            text-align: center;
            z-index: 2;
            background: white;
            padding: 0 10px;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            font-weight: bold;
        }
        
        .step.active .step-number {
            background: var(--primary-color);
            color: white;
        }
        
        .step-text {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .step.active .step-text {
            color: var(--primary-color);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card password-recovery-card">
                    <div class="card-header card-header-custom">
                        <i class="fas fa-lock"></i>
                        <h4 class="mb-0">Recuperar Contraseña</h4>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Mensajes de éxito/error -->
                        <?php if (isset($_SESSION['recovery_message'])): ?>
                            <div class="alert alert-<?= $_SESSION['recovery_type'] === 'success' ? 'success' : 'danger' ?> alert-custom mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-<?= $_SESSION['recovery_type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-3"></i>
                                    <div><?= $_SESSION['recovery_message'] ?></div>
                                </div>
                            </div>
                            <?php unset($_SESSION['recovery_message'], $_SESSION['recovery_type']); ?>
                        <?php endif; ?>

                        <?php if (isset($_GET['token']) && isset($_GET['email'])): ?>
                            <!-- Paso 2: Nueva contraseña -->
                            <div class="step-indicator">
                                <div class="step">
                                    <div class="step-number">1</div>
                                    <div class="step-text">Correo</div>
                                </div>
                                <div class="step active">
                                    <div class="step-number">2</div>
                                    <div class="step-text">Nueva Contraseña</div>
                                </div>
                            </div>
                            
                            <form action="<?= BASE_URL ?>?c=Usuario&a=resetPassword" method="POST" id="resetPasswordForm">
                                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                                <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email']) ?>">
                                
                                <div class="mb-4">
                                    <label for="nueva_contrasena" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-2"></i>Nueva Contraseña
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="nueva_contrasena" 
                                               name="nueva_contrasena" 
                                               required
                                               placeholder="Ingresa tu nueva contraseña"
                                               minlength="12">
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="nueva_contrasena">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Mínimo 12 caracteres, 2 números, 1 símbolo, 1 mayúscula y 1 minúscula</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirmar_contrasena" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-2"></i>Confirmar Nueva Contraseña
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="confirmar_contrasena" 
                                               name="confirmar_contrasena" 
                                               required
                                               placeholder="Confirma tu nueva contraseña"
                                               minlength="12">
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmar_contrasena">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <small id="passwordMatch" class="text-muted"></small>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-custom mb-3">
                                    <i class="fas fa-save me-2"></i>Cambiar Contraseña
                                </button>
                                
                                <a href="<?= BASE_URL ?>?c=Usuario&a=login" class="btn btn-outline-custom">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Login
                                </a>
                            </form>
                        <?php else: ?>
                            <!-- Paso 1: Solicitar correo -->
                            <div class="step-indicator">
                                <div class="step active">
                                    <div class="step-number">1</div>
                                    <div class="step-text">Correo</div>
                                </div>
                                <div class="step">
                                    <div class="step-number">2</div>
                                    <div class="step-text">Nueva Contraseña</div>
                                </div>
                            </div>
                            
                            <p class="text-center mb-4">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                            
                            <form action="<?= BASE_URL ?>?c=Usuario&a=requestPasswordReset" method="POST" id="requestResetForm">
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           required
                                           placeholder="correo@ejemplo.com">
                                </div>
                                
                                <button type="submit" class="btn btn-custom mb-3">
                                    <i class="fas fa-paper-plane me-2"></i>Enviar Enlace de Recuperación
                                </button>
                                
                                <a href="<?= BASE_URL ?>?c=Usuario&a=login" class="btn btn-outline-custom">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Login
                                </a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="alert alert-info alert-custom mt-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-3 fa-lg"></i>
                        <div>
                            <h6 class="alert-heading mb-2">¿No recibiste el correo?</h6>
                            <p class="mb-0">
                                • Revisa tu carpeta de spam<br>
                                • Verifica que hayas ingresado el correo correctamente<br>
                                • El enlace expirará en 1 hora
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle para mostrar/ocultar contraseña
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const target = document.getElementById(this.dataset.target);
                    const icon = this.querySelector('i');
                    
                    if (target.type === 'password') {
                        target.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        target.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Validar coincidencia de contraseñas en tiempo real
            const nuevaContrasena = document.getElementById('nueva_contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            
            if (nuevaContrasena && confirmarContrasena) {
                function checkPasswordMatch() {
                    const matchElement = document.getElementById('passwordMatch');
                    const password = nuevaContrasena.value;
                    const confirm = confirmarContrasena.value;
                    
                    if (confirm.length === 0) {
                        matchElement.textContent = '';
                        matchElement.className = 'text-muted';
                    } else if (password === confirm) {
                        matchElement.textContent = '✓ Las contraseñas coinciden';
                        matchElement.className = 'text-success';
                    } else {
                        matchElement.textContent = '✗ Las contraseñas no coinciden';
                        matchElement.className = 'text-danger';
                    }
                }
                
                nuevaContrasena.addEventListener('input', checkPasswordMatch);
                confirmarContrasena.addEventListener('input', checkPasswordMatch);
                
                // Validar formulario de reset
                const resetForm = document.getElementById('resetPasswordForm');
                if (resetForm) {
                    resetForm.addEventListener('submit', function(e) {
                        const password = nuevaContrasena.value;
                        const confirm = confirmarContrasena.value;
                        
                        if (password.length < 12) {
                            e.preventDefault();
                            alert('La contraseña debe tener mínimo 12 caracteres');
                            return false;
                        }
                        
                        if (password !== confirm) {
                            e.preventDefault();
                            alert('Las contraseñas no coinciden');
                            return false;
                        }
                        
                        // Validación adicional de seguridad
                        const hasNumbers = (password.match(/\d/g) || []).length >= 2;
                        const hasSymbol = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
                        const hasUpper = /[A-Z]/.test(password);
                        const hasLower = /[a-z]/.test(password);
                        
                        if (!hasNumbers || !hasSymbol || !hasUpper || !hasLower) {
                            e.preventDefault();
                            alert('La contraseña debe contener al menos:\n• 2 números\n• 1 símbolo especial\n• 1 letra mayúscula\n• 1 letra minúscula');
                            return false;
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>