<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - TuLook</title>
    <!-- Bootstrap 5 CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
        rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" 
        rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2f3e53ff;
            --secondary-color: #f8f9fa;
            --accent-color: #e83e8c;
            --text-dark: #343a40;
            --text-light: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: var(--text-dark);
        }
        
        .password-container {
            max-width: 500px;
            margin: 2rem auto;
        }
        
        .password-card {
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
        }
        
        .btn-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
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
        }
        
        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(47, 62, 83, 0.25);
        }
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #dc3545; width: 25%; }
        .strength-medium { background-color: #ffc107; width: 50%; }
        .strength-strong { background-color: #28a745; width: 75%; }
        .strength-very-strong { background-color: #20c997; width: 100%; }
        
        .requirement-list {
            list-style: none;
            padding-left: 0;
            font-size: 0.9rem;
        }
        
        .requirement-list li {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .requirement-list li i {
            margin-right: 0.5rem;
            width: 16px;
        }
        
        .requirement-met {
            color: #28a745;
        }
        
        .requirement-not-met {
            color: #6c757d;
        }
        
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
        }
    </style>
</head>
<body>
    <div class="container password-container">
        <!-- Botón volver -->
        <div class="mb-3">
            <a href="<?= BASE_URL ?>?c=Usuario&a=perfil" class="btn btn-outline-custom">
                <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
            </a>
        </div>

        <!-- Card principal -->
        <div class="card password-card">
            <div class="card-header card-header-custom text-center">
                <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Cambiar Contraseña</h4>
            </div>

            <div class="card-body p-4">
                <!-- Mensajes de éxito/error -->
                <?php if (isset($mensaje) && !empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?> alert-custom mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-3"></i>
                            <div><?= $mensaje ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>?c=Usuario&a=actualizarContrasena" method="POST" id="formCambiarContrasena">
                    <!-- Contraseña actual -->
                    <div class="mb-4">
                        <label for="contrasena_actual" class="form-label fw-semibold">
                            <i class="fas fa-key me-2"></i>Contraseña Actual
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="contrasena_actual" 
                                   name="contrasena_actual" 
                                   required
                                   placeholder="Ingresa tu contraseña actual">
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="contrasena_actual">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Nueva contraseña -->
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
                        
                        <!-- Indicador de fortaleza -->
                        <div class="mt-2">
                            <div class="password-strength d-none" id="passwordStrength"></div>
                        </div>
                        
                        <!-- Requisitos de contraseña -->
                        <div class="mt-3">
                            <small class="text-muted">La contraseña debe cumplir con:</small>
                            <ul class="requirement-list mt-2">
                                <li id="req-length"><i class="fas fa-circle requirement-not-met"></i> Mínimo 12 caracteres</li>
                                <li id="req-numbers"><i class="fas fa-circle requirement-not-met"></i> Al menos 2 números</li>
                                <li id="req-symbols"><i class="fas fa-circle requirement-not-met"></i> Al menos 1 símbolo especial</li>
                                <li id="req-uppercase"><i class="fas fa-circle requirement-not-met"></i> Al menos 1 letra mayúscula</li>
                                <li id="req-lowercase"><i class="fas fa-circle requirement-not-met"></i> Al menos 1 letra minúscula</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Confirmar nueva contraseña -->
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

                    <!-- Botones -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>?c=Usuario&a=perfil" class="btn btn-outline-secondary me-md-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-custom" id="submitBtn" disabled>
                            <i class="fas fa-save me-2"></i>Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información de seguridad -->
        <div class="alert alert-info alert-custom mt-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-shield-alt me-3 fa-lg"></i>
                <div>
                    <h6 class="alert-heading mb-2">Seguridad de la cuenta</h6>
                    <p class="mb-0">Para proteger tu cuenta, te recomendamos:</p>
                    <ul class="mb-0 mt-2">
                        <li>Usar una contraseña única que no utilices en otros servicios</li>
                        <li>Cambiar tu contraseña regularmente</li>
                        <li>No compartir tu contraseña con nadie</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nuevaContrasena = document.getElementById('nueva_contrasena');
            const confirmarContrasena = document.getElementById('confirmar_contrasena');
            const passwordStrength = document.getElementById('passwordStrength');
            const submitBtn = document.getElementById('submitBtn');
            
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
            
            // Validar fortaleza de contraseña en tiempo real
            nuevaContrasena.addEventListener('input', function() {
                const password = this.value;
                validatePasswordStrength(password);
                checkFormValidity();
            });
            
            // Validar coincidencia de contraseñas
            confirmarContrasena.addEventListener('input', function() {
                checkPasswordMatch();
                checkFormValidity();
            });
            
            // Validar contraseña actual
            document.getElementById('contrasena_actual').addEventListener('input', checkFormValidity);
            
            function validatePasswordStrength(password) {
                let strength = 0;
                const requirements = {
                    length: password.length >= 12,
                    numbers: (password.match(/\d/g) || []).length >= 2,
                    symbols: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password),
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password)
                };
                
                // Actualizar indicadores visuales
                updateRequirement('req-length', requirements.length);
                updateRequirement('req-numbers', requirements.numbers);
                updateRequirement('req-symbols', requirements.symbols);
                updateRequirement('req-uppercase', requirements.uppercase);
                updateRequirement('req-lowercase', requirements.lowercase);
                
                // Calcular fortaleza
                const metRequirements = Object.values(requirements).filter(Boolean).length;
                
                if (metRequirements === 5) {
                    strength = 4; // Muy fuerte
                } else if (metRequirements >= 3) {
                    strength = 3; // Fuerte
                } else if (metRequirements >= 2) {
                    strength = 2; // Media
                } else if (password.length > 0) {
                    strength = 1; // Débil
                }
                
                // Actualizar barra de fortaleza
                updateStrengthBar(strength);
            }
            
            function updateRequirement(elementId, met) {
                const element = document.getElementById(elementId);
                const icon = element.querySelector('i');
                
                if (met) {
                    icon.className = 'fas fa-check-circle requirement-met';
                } else {
                    icon.className = 'fas fa-times-circle requirement-not-met';
                }
            }
            
            function updateStrengthBar(strength) {
                const classes = ['strength-weak', 'strength-medium', 'strength-strong', 'strength-very-strong'];
                passwordStrength.className = 'password-strength ' + (classes[strength - 1] || '');
                passwordStrength.classList.remove('d-none');
            }
            
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
            
            function checkFormValidity() {
                const currentPassword = document.getElementById('contrasena_actual').value;
                const newPassword = nuevaContrasena.value;
                const confirmPassword = confirmarContrasena.value;
                
                // Verificar requisitos básicos
                const hasCurrentPassword = currentPassword.length > 0;
                const hasNewPassword = newPassword.length >= 12;
                const passwordsMatch = newPassword === confirmPassword && confirmPassword.length > 0;
                
                // Verificar requisitos de seguridad
                const requirements = {
                    numbers: (newPassword.match(/\d/g) || []).length >= 2,
                    symbols: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword),
                    uppercase: /[A-Z]/.test(newPassword),
                    lowercase: /[a-z]/.test(newPassword)
                };
                
                const allRequirementsMet = Object.values(requirements).every(Boolean);
                
                const isEnabled = (hasCurrentPassword && hasNewPassword && passwordsMatch && allRequirementsMet);
                submitBtn.disabled = !isEnabled;
                
                // Debug
                console.log('Botón habilitado:', isEnabled);
                console.log('Contraseña actual:', hasCurrentPassword);
                console.log('Nueva contraseña válida:', hasNewPassword);
                console.log('Coinciden:', passwordsMatch);
                console.log('Requisitos cumplidos:', allRequirementsMet);
            }
        });
    </script>
</body>
</html>