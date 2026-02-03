<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/atributos.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark"><i class="fas fa-list-alt me-2"></i>Gestión de Atributos</h2>
        <a href="<?= BASE_URL ?>?c=Atributo&a=crear" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Atributo
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
    <div class="card mb-4 stats-card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary-dark"><i class="fas fa-filter me-2"></i>Filtrar Atributos</h5>
                <?php if (isset($_GET['buscar']) || isset($_GET['tipo']) || isset($_GET['estado']) || isset($_GET['en_uso'])): ?>
                    <span class="badge bg-primary text-light">Filtros activos</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Atributo&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Atributo">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-2">
                    <label for="buscar" class="form-label text-primary-dark">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary-light border-primary"><i class="fas fa-search text-primary-dark"></i></span>
                        <input type="text" 
                               class="form-control border-primary" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="Valor o tipo...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="tipo" class="form-label text-primary-dark">Tipo</label>
                    <select class="form-select border-primary" id="tipo" name="tipo">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo['ID_TipoAtributo'] ?>" 
                                <?= ($_GET['tipo'] ?? '') == $tipo['ID_TipoAtributo'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['Nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label text-primary-dark">Estado</label>
                    <select class="form-select border-primary" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="activo" <?= ($_GET['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="inactivo" <?= ($_GET['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                    </select>
                </div>
                
                <!-- FILTRO: EN USO -->
                <div class="col-md-2">
                    <label for="en_uso" class="form-label text-primary-dark">En Uso</label>
                    <select class="form-select border-primary" id="en_uso" name="en_uso">
                        <option value="">Todos</option>
                        <option value="si" <?= ($_GET['en_uso'] ?? '') === 'si' ? 'selected' : '' ?>>En uso</option>
                        <option value="no" <?= ($_Get['en_uso'] ?? '') === 'no' ? 'selected' : '' ?>>No en uso</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter me-1"></i> Filtrar
                        </button>
                        <?php if (isset($_GET['buscar']) || isset($_GET['tipo']) || isset($_GET['estado']) || isset($_Get['en_uso'])): ?>
                            <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-outline-primary-light text-primary-dark">
                                <i class="fas fa-times me-1"></i> Limpiar filtros
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <?php 
    // Calcular estadísticas de uso
    $totalEnUso = 0;
    $totalNoEnUso = 0;
    foreach ($atributos as $attr) {
        if ($attr['en_uso']) {
            $totalEnUso++;
        } else {
            $totalNoEnUso++;
        }
    }
    ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title" style="color: #2A3448;">Total Atributos</h5> <!-- CAMBIADO -->
                    <h3 class="card-text" style="color: #2A3448;"><?= $estadisticas['total'] ?? 0 ?></h3> <!-- CAMBIADO -->
                    <small class="text-muted"><i class="fas fa-database me-1"></i> En sistema</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title" style="color: #2A3448;">Activos</h5> <!-- CAMBIADO -->
                    <h3 class="card-text" style="color: #2A3448;"><?= $estadisticas['activos'] ?? 0 ?></h3> <!-- CAMBIADO -->
                    <small class="text-muted"><i class="fas fa-check-circle me-1"></i> Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title" style="color: #2A3448;">En Uso</h5> <!-- CAMBIADO de verde a #2A3448 -->
                    <h3 class="card-text" style="color: #2A3448;"><?= $totalEnUso ?></h3> <!-- CAMBIADO -->
                    <small class="text-muted"><i class="fas fa-box me-1"></i> Usados por productos</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <h5 class="card-title" style="color: #6c757d;">No en Uso</h5> <!-- CAMBIADO a gris -->
                    <h3 class="card-text" style="color: #6c757d;"><?= $totalNoEnUso ?></h3> <!-- CAMBIADO -->
                    <small class="text-muted"><i class="fas fa-box-open me-1"></i> Disponibles</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Atributos -->
    <div class="card stats-card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary-dark"><i class="fas fa-table me-2"></i>Lista de Atributos</h5>
                <span class="badge bg-primary text-light">
                    Mostrando <?= count($atributos) ?> de <?= $estadisticas['total'] ?? 0 ?> atributos
                </span>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($atributos)): ?>
                <div class="text-center py-5 no-results">
                    <i class="fas fa-inbox fa-3x text-primary-light mb-3"></i>
                    <h5 class="text-primary-light">No hay atributos registrados</h5>
                    <?php if (isset($modoBusqueda) && $modoBusqueda): ?>
                        <p class="text-primary-light">No hay resultados para los filtros aplicados</p>
                        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-primary">
                            <i class="fas fa-times me-1"></i> Limpiar filtros
                        </a>
                    <?php else: ?>
                        <p class="text-primary-light">Comienza creando tu primer atributo</p>
                        <a href="<?= BASE_URL ?>?c=Atributo&a=crear" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Crear primer atributo
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Valor</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Uso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            usort($atributos, function($a, $b) {
                                $tipoCompare = strcmp($a['TipoNombre'], $b['TipoNombre']);
                                if ($tipoCompare !== 0) {
                                    return $tipoCompare;
                                }
                                return $a['Orden'] - $b['Orden'];
                            });
                            
                            foreach ($atributos as $attr): 
                                $esUnica = ($attr['ID_AtributoValor'] == 16 || strtolower($attr['Valor']) === 'única');
                                $puedeEliminar = !$attr['en_uso'] && !$esUnica;
                                $puedeEditar = !$esUnica;
                                $puedeCambiarEstado = !$esUnica && (!$attr['en_uso'] || $attr['Activo'] == 1);
                            ?>
                                <tr class="hover-shadow-detalle">
                                    <td>
                                        <span class="badge bg-primary-light text-primary-dark border border-primary">
                                            <?= htmlspecialchars($attr['TipoNombre']) ?>
                                        </span>
                                        <?php if (!empty($attr['TipoDescripcion'])): ?>
                                            <br><small class="text-primary-light"><?= $attr['TipoDescripcion'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-primary-dark"><?= htmlspecialchars($attr['Valor']) ?></strong>
                                        <?php if ($esUnica): ?>
                                            <br><small class="text-warning"><i class="fas fa-shield-alt me-1"></i> Valor universal</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-light text-primary-dark border border-primary">
                                            <i class="fas fa-sort-numeric-up me-1"></i> #<?= $attr['Orden'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $attr['Activo'] ? 'primary' : 'secondary' ?>">
                                            <?= $attr['Activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($attr['en_uso']): ?>
                                            <span class="badge" style="background-color: #2A3448; color: white;"> <!-- CAMBIADO -->
                                                <i class="fas fa-box me-1"></i> En uso
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border border-secondary">
                                                <i class="fas fa-box-open me-1"></i> No en uso
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <!-- Botón Ver Detalles - SIEMPRE del mismo color -->
                                            <a href="<?= BASE_URL ?>?c=Atributo&a=detalle&id=<?= $attr['ID_AtributoValor'] ?>" 
                                            class="btn btn-outline-primary" 
                                            title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Botón Ver Productos en Modal - SOLO si está en uso -->
                                            <?php if ($attr['en_uso']): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-primary" 
                                                        title="Ver productos que usan '<?= htmlspecialchars($attr['Valor']) ?>'"
                                                        onclick="verProductosAtributo(<?= $attr['ID_AtributoValor'] ?>, '<?= htmlspecialchars($attr['Valor']) ?>')">
                                                    <i class="fas fa-box"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="No hay productos usando este atributo">
                                                    <i class="fas fa-box"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puedeEditar): ?>
                                                <a href="<?= BASE_URL ?>?c=Atributo&a=editar&id=<?= $attr['ID_AtributoValor'] ?>" 
                                                class="btn btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="Este valor universal no se puede editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($attr['Activo'] && $puedeCambiarEstado): ?>
                                                <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $attr['ID_AtributoValor'] ?>&estado=0" 
                                                class="btn btn-outline-primary" title="Desactivar">
                                                    <i class="fas fa-pause"></i>
                                                </a>
                                            <?php elseif (!$attr['Activo'] && $puedeCambiarEstado): ?>
                                                <a href="<?= BASE_URL ?>?c=Atributo&a=cambiarEstado&id=<?= $attr['ID_AtributoValor'] ?>&estado=1" 
                                                class="btn btn-outline-primary" title="Activar">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                            <?php elseif (!$puedeCambiarEstado): ?>
                                                <button class="btn btn-outline-secondary" disabled title="No se puede cambiar estado">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($puedeEliminar): ?>
                                                <a href="<?= BASE_URL ?>?c=Atributo&a=eliminar&id=<?= $attr['ID_AtributoValor'] ?>" 
                                                class="btn btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php elseif ($esUnica): ?>
                                                <button class="btn btn-outline-secondary" disabled title="Este valor universal no se puede eliminar">
                                                    <i class="fas fa-shield-alt"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary" disabled title="Este atributo está en uso y no puede eliminarse">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Información del filtro aplicado -->
                <?php if ($modoBusqueda): ?>
                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Mostrando <?= count($atributos) ?> atributos con los filtros aplicados:
                            <?php 
                            $filtrosAplicados = [];
                            if (!empty($_GET['buscar'])) $filtrosAplicados[] = "búsqueda: '{$_GET['buscar']}'";
                            if (!empty($_GET['tipo'])) {
                                $tipoNombre = '';
                                foreach ($tipos as $tipo) {
                                    if ($tipo['ID_TipoAtributo'] == $_GET['tipo']) {
                                        $tipoNombre = $tipo['Nombre'];
                                        break;
                                    }
                                }
                                $filtrosAplicados[] = "tipo: '{$tipoNombre}'";
                            }
                            if (!empty($_GET['estado'])) $filtrosAplicados[] = "estado: '{$_GET['estado']}'";
                            if (!empty($_GET['en_uso'])) {
                                $usoTexto = ($_GET['en_uso'] == 'si') ? 'en uso' : 'no en uso';
                                $filtrados[] = "{$usoTexto}";
                            }
                            echo implode(', ', $filtrosAplicados);
                            ?>
                        </small>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Incluir el script completo -->
<script src="<?= BASE_URL ?>assets/js/atributo.js"></script>