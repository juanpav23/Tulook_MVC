<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h2>
        <div>
            <button type="button" class="btn btn-outline-primary me-2" id="btnLimpiar">
                <i class="fas fa-eraser me-1"></i> Limpiar
            </button>
            <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div id="mensajeGlobal" class="alert-message alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?>">
            <div class="alert-content">
                <i class="fas fa-info-circle me-2"></i>
                <span><?= $_SESSION['mensaje'] ?></span>
                <button type="button" class="btn-close-alert" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-user me-2"></i>Información del Usuario</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=guardar" method="post" id="formUsuario">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ID_TD" class="form-label"><strong>Tipo de Documento *</strong></label>
                            <select class="form-select" id="ID_TD" name="ID_TD" required>
                                <option value="" selected disabled>-- Selecciona un tipo --</option>
                                <?php foreach ($tiposDocumento as $tipoDoc): ?>
                                    <option value="<?= $tipoDoc['ID_TD'] ?>"
                                        <?= isset($_SESSION['form_data']['ID_TD']) && $_SESSION['form_data']['ID_TD'] == $tipoDoc['ID_TD'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipoDoc['Documento']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Debes seleccionar un tipo de documento</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="N_Documento" class="form-label"><strong>Número de Documento *</strong></label>
                            <input type="number" class="form-control" id="N_Documento" name="N_Documento" 
                                required min="100000000" max="9999999999" 
                                placeholder="Ingresa solo números (ej: 1038105095)"
                                value="<?= isset($_SESSION['form_data']['N_Documento']) ? htmlspecialchars($_SESSION['form_data']['N_Documento']) : '' ?>">
                            <div class="form-text">El documento debe ser único en el sistema (9-10 dígitos)</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Nombre" class="form-label"><strong>Nombre *</strong></label>
                            <input type="text" class="form-control" id="Nombre" name="Nombre" 
                                required placeholder="Primer nombre"
                                value="<?= isset($_SESSION['form_data']['Nombre']) ? htmlspecialchars($_SESSION['form_data']['Nombre']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Apellido" class="form-label"><strong>Apellido *</strong></label>
                            <input type="text" class="form-control" id="Apellido" name="Apellido" 
                                required placeholder="Primer apellido"
                                value="<?= isset($_SESSION['form_data']['Apellido']) ? htmlspecialchars($_SESSION['form_data']['Apellido']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Correo" class="form-label"><strong>Correo *</strong></label>
                            <input type="email" class="form-control" id="Correo" name="Correo" 
                                required placeholder="correo@ejemplo.com"
                                value="<?= isset($_SESSION['form_data']['Correo']) ? htmlspecialchars($_SESSION['form_data']['Correo']) : '' ?>">
                            <div class="form-text">El correo debe ser único en el sistema</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Celular" class="form-label"><strong>Celular *</strong></label>
                            <input type="tel" class="form-control" id="Celular" name="Celular" 
                                required pattern="\d{10}" placeholder="10 dígitos (ej: 3134454668)"
                                value="<?= isset($_SESSION['form_data']['Celular']) ? htmlspecialchars($_SESSION['form_data']['Celular']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Password" class="form-label"><strong>Contraseña *</strong></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="Password" name="Password" 
                                       required minlength="6" placeholder="Mínimo 6 caracteres">
                                <button type="button" class="btn btn-outline-primary" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">La contraseña será encriptada automáticamente</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ID_Rol" class="form-label"><strong>Rol del Usuario *</strong></label>
                            <select class="form-select" id="ID_Rol" name="ID_Rol" required>
                                <option value="" selected disabled>-- Selecciona un rol --</option>
                                <?php foreach ($roles as $rol): 
                                    // ============================================================
                                    // IMPORTANTE: SOLO MOSTRAR ROLES 2 (Editor) y 3 (Cliente)
                                    // NO MOSTRAR ROL 1 (Administrador)
                                    // ============================================================
                                    // Para permitir asignar rol de administrador en el futuro:
                                    // ----  Se debe crear el usuario y despues cambiar el rol por medio del index
                                    // ============================================================
                                    if ($rol['ID_Rol'] != 1): ?>
                                    <option value="<?= $rol['ID_Rol'] ?>" 
                                        <?= isset($_SESSION['form_data']['ID_Rol']) && $_SESSION['form_data']['ID_Rol'] == $rol['ID_Rol'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($rol['Roles']) ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Solo se pueden asignar roles de Editor o Cliente. <strong>No se puede asignar rol de Administrador.</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary-dark me-2">
                            <i class="fas fa-save me-1"></i> Guardar Usuario
                        </button>
                        <button type="button" class="btn btn-outline-primary me-2" id="btnLimpiar2">
                            <i class="fas fa-eraser me-1"></i> Limpiar Formulario
                        </button>
                        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-outline-primary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Información importante -->
    <div class="alert alert-primary-light mt-3">
        <h6><i class="fas fa-exclamation-circle me-2"></i>Importante</h6>
        <small>
            • Los datos personales (cédula, nombre, email) no se pueden modificar después de crear el usuario<br>
            • La contraseña solo se puede establecer al crear el usuario, no se puede cambiar después<br>
            • El rol del usuario se puede cambiar después desde la lista de usuarios (solo entre Editor y Cliente)<br>
            • Los usuarios se crean activos por defecto<br>
            • <strong>Nota:</strong> Solo se pueden crear usuarios con roles de Editor o Cliente. <strong>No se puede asignar rol de Administrador.</strong><br>
            • <strong>Para añadir un nuevo administrador en el futuro:</strong> Quitar las validaciones marcadas en el código
        </small>
    </div>
</div>

<!-- CSS Compartido -->
<link rel="stylesheet" href="assets/css/usuario.css">

<!-- JS Compartido -->
<script src="assets/js/usuario.js"></script>