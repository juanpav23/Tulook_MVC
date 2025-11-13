<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h2>
        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información del Usuario</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=guardar" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ID_TD" class="form-label"><strong>Tipo de Documento *</strong></label>
                            <select class="form-select" id="ID_TD" name="ID_TD" required>
                                <?php foreach ($tiposDocumento as $tipoDoc): ?>
                                    <option value="<?= $tipoDoc['ID_TD'] ?>">
                                        <?= htmlspecialchars($tipoDoc['Documento']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="N_Documento" class="form-label"><strong>Número de Documento *</strong></label>
                            <input type="text" class="form-control" id="N_Documento" name="N_Documento" 
                                required maxlength="20" placeholder="Número de documento">
                            <div class="form-text">El documento debe ser único en el sistema</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Correo" class="form-label"><strong>Correo *</strong></label>
                            <input type="email" class="form-control" id="Correo" name="Correo" 
                                required placeholder="correo@ejemplo.com">
                            <div class="form-text">El correo debe ser único en el sistema</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Celular" class="form-label"><strong>Celular *</strong></label>
                            <input type="text" class="form-control" id="Celular" name="Celular" 
                                required placeholder="Número de celular">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Password" class="form-label"><strong>Contraseña *</strong></label>
                            <input type="password" class="form-control" id="Password" name="Password" 
                                   required minlength="6" placeholder="Mínimo 6 caracteres">
                            <div class="form-text">La contraseña será encriptada automáticamente</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ID_Rol" class="form-label"><strong>Rol del Usuario</strong></label>
                            <select class="form-select" id="ID_Rol" name="ID_Rol">
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= $rol['ID_Rol'] ?>" 
                                        <?= $rol['ID_Rol'] == 2 ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($rol['Roles']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Por defecto se asigna el rol de Editor. Los administradores pueden cambiar esto después.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i> Crear Usuario
                        </button>
                        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Información importante -->
    <div class="alert alert-warning mt-3">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
        <small>
            • Los datos personales (cédula, nombre, email) no se pueden modificar después de crear el usuario<br>
            • La contraseña solo se puede establecer al crear el usuario, no se puede cambiar después<br>
            • El rol del usuario se puede cambiar después desde la lista de usuarios<br>
            • Los usuarios se crean activos por defecto
        </small>
    </div>
</div>