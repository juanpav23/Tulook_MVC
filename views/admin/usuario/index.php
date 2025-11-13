<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=crear" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
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

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar y Filtrar Usuarios</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="UsuarioAdmin">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Cédula, nombre o email">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="rol" class="form-label">Rol</label>
                    <select class="form-select" id="rol" name="rol">
                        <option value="">Todos</option>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['ID_Rol'] ?>" <?= ($_GET['rol'] ?? '') == $rol['ID_Rol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['Roles']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Usuarios</h5>
                    <h3 class="card-text"><?= count($usuarios) ?></h3>
                    <small><i class="fas fa-database"></i> En sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Usuarios Activos</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarActivos() ?></h3>
                    <small><i class="fas fa-check-circle"></i> Habilitados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Administradores</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarPorRol(1) ?></h3>
                    <small><i class="fas fa-crown"></i> Rol admin</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Editores</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarPorRol(2) ?></h3>
                    <small><i class="fas fa-edit"></i> Rol editor</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Usuarios</h5>
        </div>
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay usuarios registrados</h5>
                    <p class="text-muted">Comienza creando el primer usuario.</p>
                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=crear" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Crear Primer Usuario
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cédula</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td>
                                        <strong>#<?= $usuario['ID_Usuario'] ?></strong>
                                        <?php if ($usuario['ID_Usuario'] == 1): ?>
                                            <span class="badge bg-danger ms-1" title="Super Administrador">
                                                <i class="fas fa-crown"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['N_Documento']) ?></td>
                                    <td><?= htmlspecialchars($usuario['Nombre_Completo']) ?></td>
                                    <td><?= htmlspecialchars($usuario['Correo']) ?></td>
                                    <td>
                                        <?php if ($_SESSION['ID_Usuario'] == 1 || ($_SESSION['ID_Usuario'] != $usuario['ID_Usuario'] && $usuario['ID_Rol'] != 1)): ?>
                                            <form method="post" action="<?= BASE_URL ?>?c=UsuarioAdmin&a=cambiarRol" class="d-inline">
                                                <input type="hidden" name="ID_Usuario" value="<?= $usuario['ID_Usuario'] ?>">
                                                <select name="ID_Rol" class="form-select form-select-sm" onchange="this.form.submit()" 
                                                    <?= ($_SESSION['ID_Usuario'] == $usuario['ID_Usuario']) ? 'disabled' : '' ?>>
                                                    <?php foreach ($roles as $rol): ?>
                                                        <option value="<?= $rol['ID_Rol'] ?>" 
                                                            <?= $usuario['ID_Rol'] == $rol['ID_Rol'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($rol['Roles']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-<?= $usuario['ID_Rol'] == 1 ? 'danger' : 'warning' ?>">
                                                <?= htmlspecialchars($usuario['Nombre_Rol']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $usuario['Activo'] ? 'success' : 'secondary' ?>">
                                            <?= $usuario['Activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Cambiar Estado -->
                                            <?php if ($_SESSION['ID_Usuario'] != $usuario['ID_Usuario']): ?>
                                                <?php if ($usuario['Activo']): ?>
                                                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=cambiarEstado&id=<?= $usuario['ID_Usuario'] ?>&estado=0" 
                                                       class="btn btn-outline-warning" title="Desactivar"
                                                       onclick="return confirm('¿Estás seguro de desactivar este usuario?')">
                                                        <i class="fas fa-pause"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=cambiarEstado&id=<?= $usuario['ID_Usuario'] ?>&estado=1" 
                                                       class="btn btn-outline-success" title="Activar"
                                                       onclick="return confirm('¿Estás seguro de activar este usuario?')">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="No puedes desactivarte a ti mismo">
                                                    <i class="fas fa-user"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información importante -->
    <div class="alert alert-info mt-3">
        <h6><i class="fas fa-info-circle me-2"></i>Información Importante</h6>
        <small>
            • <strong>Super Administrador (ID 1):</strong> Puede desactivar y cambiar roles de todos los usuarios<br>
            • <strong>Administradores:</strong> No pueden desactivar ni cambiar roles de otros administradores<br>
            • <strong>Editores:</strong> Solo pueden ser gestionados por administradores<br>
            • <strong>Autogestión:</strong> No puedes desactivar o cambiar el rol de tu propio usuario<br>
            • <strong>Datos personales:</strong> Los datos como cédula, nombre y email no se pueden editar por seguridad<br>
            • <strong>Contraseñas:</strong> Solo se pueden establecer al crear nuevos usuarios, no se pueden editar después
        </small>
    </div>
</div>