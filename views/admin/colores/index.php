<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-palette me-2"></i>Gestión de Colores</h2>
        <a href="<?= BASE_URL ?>?c=Color&a=crear" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Color
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

    <!-- Barra de Búsqueda -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Colores</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Color&a=index" method="get" class="row g-3">
                <input type="hidden" name="c" value="Color">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Buscar por nombre o código hexadecimal...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Buscar
                    </button>
                </div>
                
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h5>
                            <p class="card-text mb-0">Total de colores en el sistema</p>
                        </div>
                        <h2 class="mb-0"><?= $estadisticas['total'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Colores -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Colores</h5>
        </div>
        <div class="card-body">
            <?php if (empty($colores)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-palette fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay colores registrados</h5>
                    <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
                        <p class="text-muted">No hay resultados para la búsqueda</p>
                        <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-primary">
                            <i class="fas fa-times me-1"></i> Limpiar búsqueda
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Comienza creando tu primer color</p>
                        <a href="<?= BASE_URL ?>?c=Color&a=crear" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Crear primer color
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Código HEX</th>
                                <th>Muestra</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($colores as $color): ?>
                                <tr>
                                    <td>#<?= $color['ID_Color'] ?></td>
                                    <td><strong><?= htmlspecialchars($color['N_Color']) ?></strong></td>
                                    <td>
                                        <code><?= htmlspecialchars($color['CodigoHex']) ?></code>
                                    </td>
                                    <td>
                                        <div class="color-muestra" 
                                             style="background-color: <?= $color['CodigoHex'] ?>; 
                                                    width: 30px; 
                                                    height: 30px; 
                                                    border: 1px solid #ddd; 
                                                    border-radius: 4px;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>?c=Color&a=editar&id=<?= $color['ID_Color'] ?>" 
                                               class="btn btn-outline-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>?c=Color&a=eliminar&id=<?= $color['ID_Color'] ?>" 
                                               class="btn btn-outline-danger" title="Eliminar"
                                               onclick="return confirm('¿Estás seguro de eliminar este color? Esta acción no se puede deshacer.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
</div>