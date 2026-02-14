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

<!-- Encabezado con título y botón volver -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-primary-dark"><i class="fas fa-inbox me-2"></i>Solicitudes de Reactivación</h2>
    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-1"></i> Volver a Usuarios
    </a>
</div>

<!-- Estadísticas con colores del sistema -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stats-card">
            <div class="card-body">
                <h5 class="card-title text-primary-dark">Pendientes</h5>
                <h3 class="card-text"><?= $estadisticas['pendientes'] ?? 0 ?></h3>
                <small class="text-primary"><i class="fas fa-clock me-1"></i> Esperando revisión</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card">
            <div class="card-body">
                <h5 class="card-title text-primary-dark">Aprobadas</h5>
                <h3 class="card-text"><?= $estadisticas['procesadas'] ?? 0 ?></h3>
                <small class="text-primary"><i class="fas fa-check-circle me-1"></i> Cuentas reactivadas</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card">
            <div class="card-body">
                <h5 class="card-title text-primary-dark">Rechazadas</h5>
                <h3 class="card-text"><?= $estadisticas['rechazadas'] ?? 0 ?></h3>
                <small class="text-primary"><i class="fas fa-times-circle me-1"></i> Solicitudes denegadas</small>
            </div>
        </div>
    </div>
</div>

<!-- Barra de búsqueda y filtros (IGUAL que en index.php) -->
<div class="card mb-4">
    <div class="card-header bg-primary-dark">
        <h5 class="mb-0 text-white"><i class="fas fa-search me-2"></i>Buscar y Filtrar Solicitudes</h5>
    </div>
    <div class="card-body">
        <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion" method="get" class="row g-3 align-items-end">
            <input type="hidden" name="c" value="UsuarioAdmin">
            <input type="hidden" name="a" value="solicitudesReactivacion">
            
            <div class="col-md-4">
                <label for="buscar" class="form-label">Buscar solicitud</label>
                <div class="input-group">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="buscar" 
                           name="buscar" 
                           value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                           placeholder="Nombre, documento o correo">
                </div>
                <div class="form-text">Busca por nombre, apellido, documento o correo</div>
            </div>
            
            <div class="col-md-3">
                <label for="estado" class="form-label">Estado de solicitud</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todas las solicitudes</option>
                    <option value="pendiente" <?= ($_GET['estado'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                    <option value="procesada" <?= ($_GET['estado'] ?? '') === 'procesada' ? 'selected' : '' ?>>Aprobadas</option>
                    <option value="rechazada" <?= ($_GET['estado'] ?? '') === 'rechazada' ? 'selected' : '' ?>>Rechazadas</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary-dark w-100">
                    <i class="fas fa-filter me-1"></i> Aplicar Filtros
                </button>
            </div>
            
            <div class="col-md-2">
                <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion" class="btn btn-outline-primary w-100">
                    <i class="fas fa-times me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Solicitudes -->
<div class="card">
    <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white">
            <i class="fas fa-list me-2"></i>
            Lista de Solicitudes
            <?php if (!empty($_GET['estado'])): ?>
                <span class="badge bg-light text-primary-dark ms-2"><?= ucfirst($_GET['estado']) ?></span>
            <?php else: ?>
                <span class="badge bg-light text-primary-dark ms-2">(Pendientes → Aprobadas → Rechazadas)</span>
            <?php endif; ?>
        </h5>
        <span class="badge bg-light text-primary-dark">
            Total: <?= count($solicitudes) ?> solicitudes
        </span>
    </div>
    <div class="card-body">
        <?php if (empty($solicitudes)): ?>
            <div class="no-results text-center py-5">
                <i class="fas fa-inbox fa-4x text-primary mb-3"></i>
                <h5 class="text-primary-dark">No hay solicitudes para mostrar</h5>
                <p class="text-muted mb-0">
                    <?php if (!empty($_GET['buscar']) || !empty($_GET['estado'])): ?>
                        No se encontraron solicitudes con los filtros aplicados.
                        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion" class="text-primary">Limpiar filtros</a>
                    <?php else: ?>
                        Cuando los usuarios soliciten reactivar su cuenta, aparecerán aquí.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-primary-dark">
                        <tr>
                            <th>Usuario</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Fecha Solicitud</th>
                            <th>Vence</th>
                            <th>Estado</th>
                            <th>Motivo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $sol): 
                            $fechaSolicitud = new DateTime($sol['Fecha_Solicitud']);
                            $fechaExpiracion = isset($sol['Fecha_Expiracion']) ? new DateTime($sol['Fecha_Expiracion']) : null;
                            $ahora = new DateTime();
                            $expirada = $fechaExpiracion && $fechaExpiracion < $ahora;
                            $horasRestantes = $expirada ? 0 : ($fechaExpiracion ? $ahora->diff($fechaExpiracion) : null);
                            
                            // Determinar clase de estado
                            $estadoClase = '';
                            $estadoIcono = '';
                            $estadoTexto = '';
                            
                            if ($sol['Estado'] == 'pendiente') {
                                if ($expirada) {
                                    $estadoClase = 'estado-inactivo';
                                    $estadoIcono = 'fa-hourglass-end';
                                    $estadoTexto = 'Expirada';
                                } else {
                                    $estadoClase = 'bg-secondary text-dark';
                                    $estadoIcono = 'fa-clock';
                                    $estadoTexto = 'Pendiente';
                                }
                            } elseif ($sol['Estado'] == 'procesada') {
                                $estadoClase = 'estado-activo';
                                $estadoIcono = 'fa-check-circle';
                                $estadoTexto = 'Aprobada';
                            } else {
                                $estadoClase = 'estado-inactivo';
                                $estadoIcono = 'fa-times-circle';
                                $estadoTexto = 'Rechazada';
                            }
                        ?>
                            <tr class="hover-shadow-detalle">
                                <td>
                                    <strong><?= htmlspecialchars($sol['Nombre'] . ' ' . $sol['Apellido']) ?></strong>
                                    <?php if (!$sol['Estado_Usuario']): ?>
                                        <span class="badge estado-inactivo ms-1" title="Usuario desactivado">
                                            <i class="fas fa-pause-circle"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($sol['N_Documento']) ?></td>
                                <td>
                                    <div><i class="fas fa-envelope text-primary me-1"></i> <?= htmlspecialchars(substr($sol['Correo'], 0, 20)) ?>...</div>
                                    <?php if (!empty($sol['Celular'])): ?>
                                        <small><i class="fas fa-phone-alt text-primary me-1"></i> <?= htmlspecialchars($sol['Celular']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-light">
                                        <i class="fas fa-calendar-alt me-1"></i> <?= $fechaSolicitud->format('d/m/Y') ?>
                                    </span>
                                    <br>
                                    <small class="text-muted"><?= $fechaSolicitud->format('H:i') ?></small>
                                </td>
                                <td>
                                    <?php if ($sol['Estado'] == 'pendiente'): ?>
                                        <?php if ($expirada): ?>
                                            <span class="badge estado-inactivo">
                                                <i class="fas fa-hourglass-end me-1"></i> Expirada
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-light">
                                                <i class="fas fa-hourglass-half me-1"></i>
                                                <?= $horasRestantes->h ?>h <?= $horasRestantes->i ?>m
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-primary-light">
                                            <i class="fas fa-check me-1"></i>
                                            <?= date('d/m/Y', strtotime($sol['Fecha_Procesamiento'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $estadoClase ?>">
                                        <i class="fas <?= $estadoIcono ?> me-1"></i> <?= $estadoTexto ?>
                                    </span>
                                </td>
                                <td style="max-width: 200px;">
                                    <div class="text-truncate" 
                                         title="<?= htmlspecialchars($sol['Motivo_Reactivacion'] ?? 'Sin motivo especificado') ?>">
                                        <i class="fas fa-comment text-primary me-1"></i>
                                        <?= htmlspecialchars(substr($sol['Motivo_Reactivacion'] ?? 'Sin motivo', 0, 30)) ?>...
                                    </div>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=verSolicitudReactivacion&id=<?= $sol['ID_Solicitud'] ?>" 
                                       class="btn btn-primary-dark btn-sm">
                                        <i class="fas fa-eye me-1"></i> Ver detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Paginación -->
<?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Paginación">
            <ul class="pagination">
                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion&estado=<?= $_GET['estado'] ?? '' ?>&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>&pagina=<?= $pagina - 1 ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion&estado=<?= $_GET['estado'] ?? '' ?>&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>&pagina=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion&estado=<?= $_GET['estado'] ?? '' ?>&buscar=<?= urlencode($_GET['buscar'] ?? '') ?>&pagina=<?= $pagina + 1 ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>

<!-- CSS y JS con BASE_URL -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/usuario.css">
<script src="<?= BASE_URL ?>assets/js/usuario.js"></script>

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