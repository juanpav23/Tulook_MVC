<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=crear" class="btn btn-primary-dark">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
        </a>
    </div>

    <!-- Mensajes Globales -->
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

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-search me-2"></i>Buscar y Filtrar Usuarios</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="UsuarioAdmin">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar usuario</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary">
                            <i class="fas fa-search text-white"></i>
                        </span>
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
                    <button type="submit" class="btn btn-primary-dark w-100">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-outline-primary w-100">
                        <i class="fas fa-times me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas CORREGIDAS -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Total Usuarios</h5>
                    <h3 class="card-text"><?= count($usuarios) ?></h3>
                    <small><i class="fas fa-database text-primary"></i> En sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Usuarios Activos</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarActivos() ?></h3>
                    <small><i class="fas fa-check-circle text-primary"></i> Habilitados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Usuarios Inactivos</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarInactivos() ?></h3>
                    <small><i class="fas fa-pause-circle text-primary"></i> Deshabilitados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card estadistica-admin">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Administradores</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarPorRol(1) ?></h3>
                    <small><i class="fas fa-crown text-primary"></i> Rol admin</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card estadistica-editor">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Editores</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarEditores() ?></h3>
                    <small><i class="fas fa-edit text-primary"></i> Rol editor</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card estadistica-cliente">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Clientes</h5>
                    <h3 class="card-text"><?= $this->usuarioModel->contarClientes() ?></h3>
                    <small><i class="fas fa-user text-primary"></i> Rol cliente</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-list me-2"></i>Lista de Usuarios</h5>
        </div>
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <div class="no-results text-center py-5">
                    <i class="fas fa-users fa-4x text-primary mb-3"></i>
                    <h5 class="text-primary-dark">No hay usuarios registrados</h5>
                    <p class="text-muted mb-4">Comienza creando el primer usuario.</p>
                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=crear" class="btn btn-primary-dark">
                        <i class="fas fa-user-plus me-1"></i> Crear Primer Usuario
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary-dark">
                            <tr>
                                <th>Cédula</th>
                                <th>Nombres</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): 
                                // Determinar si se puede cambiar el rol
                                $puedeCambiarRol = ($_SESSION['ID_Usuario'] != $usuario['ID_Usuario'] && $usuario['ID_Rol'] != 1);
                                
                                // Determinar si se puede cambiar el estado
                                $puedeCambiarEstado = ($_SESSION['ID_Usuario'] != $usuario['ID_Usuario'] && $usuario['ID_Rol'] != 1);
                                
                                // Determinar clase CSS según el rol
                                $claseRol = '';
                                $iconoRol = '';
                                if ($usuario['ID_Rol'] == 1) {
                                    $claseRol = 'bg-primary-dark';
                                    $iconoRol = '<i class="fas fa-crown me-1"></i>';
                                } elseif ($usuario['ID_Rol'] == 2) {
                                    $claseRol = 'bg-primary';
                                    $iconoRol = '<i class="fas fa-edit me-1"></i>';
                                } elseif ($usuario['ID_Rol'] == 3) {
                                    $claseRol = 'bg-primary-light';
                                    $iconoRol = '<i class="fas fa-user me-1"></i>';
                                }
                            ?>
                                <tr class="hover-shadow-detalle">
                                    <td>
                                        <strong><?= htmlspecialchars($usuario['N_Documento']) ?></strong>
                                        <?php if ($usuario['ID_Usuario'] == 1): ?>
                                            <span class="badge bg-primary ms-1" title="Super Administrador">
                                                <i class="fas fa-crown"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['Nombre']) ?></td>
                                    <td><?= htmlspecialchars($usuario['Apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['Correo']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?= $claseRol ?> d-flex align-items-center">
                                                <?= $iconoRol ?><?= htmlspecialchars($usuario['Nombre_Rol']) ?>
                                            </span>
                                            
                                            <?php if ($puedeCambiarRol): ?>
                                                <!-- Botón para abrir modal de cambio de rol -->
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary cambiar-rol-trigger"
                                                        data-usuario-id="<?= $usuario['ID_Usuario'] ?>"
                                                        data-usuario-nombre="<?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?>"
                                                        data-rol-actual="<?= $usuario['ID_Rol'] ?>"
                                                        data-rol-actual-texto="<?= htmlspecialchars($usuario['Nombre_Rol']) ?>"
                                                        title="Cambiar rol">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            <?php elseif ($_SESSION['ID_Usuario'] == $usuario['ID_Usuario']): ?>
                                                <small class="text-muted">(Tú)</small>
                                            <?php elseif ($usuario['ID_Rol'] == 1): ?>
                                                <small class="text-muted">(Admin)</small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge estado-<?= $usuario['Activo'] ? 'activo' : 'inactivo' ?>" 
                                            <?php if (!$usuario['Activo'] && !empty($usuario['Motivo_Desactivacion'])): ?>
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Motivo: <?= htmlspecialchars($usuario['Motivo_Desactivacion']) ?><?php if ($usuario['Fecha_Desactivacion']): ?><br>Fecha: <?= date('d/m/Y H:i', strtotime($usuario['Fecha_Desactivacion'])) ?><?php endif; ?><?php if ($usuario['Admin_Nombre']): ?><br>Por: <?= htmlspecialchars($usuario['Admin_Nombre'] . ' ' . $usuario['Admin_Apellido']) ?><?php endif; ?>"
                                            <?php endif; ?>>
                                            <?php if ($usuario['Activo']): ?>
                                                <i class="fas fa-check-circle me-1"></i>Activo
                                            <?php else: ?>
                                                <i class="fas fa-pause-circle me-1"></i>Inactivo
                                                <?php if (!empty($usuario['Motivo_Desactivacion'])): ?>
                                                    <i class="fas fa-info-circle ms-1"></i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Cambiar Estado -->
                                            <?php if ($puedeCambiarEstado): ?>
                                                <?php if ($usuario['Activo']): ?>
                                                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=cambiarEstado&id=<?= $usuario['ID_Usuario'] ?>&estado=0" 
                                                       class="btn btn-outline-primary cambiar-estado-btn" 
                                                       data-id="<?= $usuario['ID_Usuario'] ?>" 
                                                       data-nombre="<?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?>"
                                                       data-accion="desactivar"
                                                       title="Desactivar">
                                                        <i class="fas fa-pause"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=cambiarEstado&id=<?= $usuario['ID_Usuario'] ?>&estado=1" 
                                                       class="btn btn-outline-primary cambiar-estado-btn" 
                                                       data-id="<?= $usuario['ID_Usuario'] ?>" 
                                                       data-nombre="<?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?>"
                                                       data-accion="activar"
                                                       title="Activar">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($_SESSION['ID_Usuario'] == $usuario['ID_Usuario']): ?>
                                                    <button class="btn btn-outline-secondary" disabled title="No puedes desactivarte a ti mismo">
                                                        <i class="fas fa-user"></i>
                                                    </button>
                                                <?php elseif ($usuario['ID_Rol'] == 1): ?>
                                                    <button class="btn btn-outline-secondary" disabled title="No se puede desactivar administradores">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-secondary" disabled title="No disponible">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
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

    <!-- Modal de Cambio de Rol -->
    <div class="modal fade" id="modalCambioRol" tabindex="-1" aria-labelledby="modalCambioRolLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title" id="modalCambioRolLabel">
                        <i class="fas fa-user-tag me-2"></i>Cambiar Rol de Usuario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="user-avatar mb-3">
                            <div class="avatar-circle bg-primary-dark">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                            <h5 class="mt-2" id="modalUsuarioNombre"></h5>
                        </div>
                        
                        <div class="rol-change-display mb-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-center">
                                    <div class="rol-badge rol-actual bg-primary-dark" id="modalRolActualBadge">
                                        <i class="fas fa-user-check me-1"></i>
                                        <span id="modalRolActualTexto"></span>
                                    </div>
                                    <small class="text-muted d-block mt-1">Rol Actual</small>
                                </div>
                                
                                <div class="arrow-container">
                                    <i class="fas fa-long-arrow-alt-right fa-2x text-primary"></i>
                                </div>
                                
                                <div class="text-center">
                                    <div class="rol-badge rol-nuevo bg-primary" id="modalRolNuevoBadge">
                                        <i class="fas fa-user-edit me-1"></i>
                                        <span id="modalRolNuevoTexto">Seleccionar</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">Rol Nuevo</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="rol-selection mb-4">
                            <label class="form-label"><strong>Seleccionar Nuevo Rol:</strong></label>
                            <div class="d-flex gap-2 justify-content-center" id="rolesDisponibles">
                                <!-- Los botones se generarán dinámicamente con JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary-dark" id="confirmarCambioRolBtn" disabled>
                        <i class="fas fa-save me-1"></i> Confirmar Cambio
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Compartido -->
<link rel="stylesheet" href="assets/css/usuario.css">

<!-- JS Compartido -->
<script src="assets/js/usuario.js"></script>
<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true
        });
    });
});
</script>

<!-- Formulario oculto para cambio de rol -->
<form id="formCambiarRol" method="post" action="<?= BASE_URL ?>?c=UsuarioAdmin&a=cambiarRol" style="display: none;">
    <input type="hidden" name="ID_Usuario" id="cambiarRolIdUsuario">
    <input type="hidden" name="ID_Rol" id="cambiarRolIdRol">
</form>