<?php
if (!isset($resenas)) $resenas = [];
?>
<style>
  :root {
    --primary-dark: #1B202D;
    --primary-blue: #2A3448;
    --accent-blue: #3A4A6B;
    --secondary-blue: #4A5B7D;
    --light-blue: #5B6E8F;
    --light-bg: #f5f7fa;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --border-radius: 12px;
    --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --card-shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.12);
    --transition: all 0.3s ease;
  }

  .resenas-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    color: white;
    padding: 28px 24px;
    border-radius: var(--border-radius);
    margin-bottom: 24px;
    box-shadow: var(--card-shadow);
  }

  .resenas-header h3 {
    margin: 0;
    font-weight: 800;
    font-size: 1.8rem;
    letter-spacing: 0.5px;
  }

  .filter-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--card-shadow);
    margin-bottom: 20px;
    border: 1px solid rgba(42, 52, 72, 0.04);
  }

  .filter-card .form-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--primary-dark);
    margin-bottom: 8px;
  }

  .filter-card .form-control,
  .filter-card .form-select {
    border-radius: 8px;
    border: 1px solid rgba(42, 52, 72, 0.06);
    transition: var(--transition);
  }

  .filter-card .form-control:focus,
  .filter-card .form-select:focus {
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 0.2rem rgba(58, 74, 107, 0.15);
  }

  .btn-filter {
    background: linear-gradient(135deg, var(--accent-blue) 0%, var(--secondary-blue) 100%);
    border: none;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    transition: var(--transition);
    padding: 10px 24px;
  }

  .btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow);
    color: white;
  }

  .user-warning-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: var(--light-bg);
    border-radius: 20px;
    padding: 4px 10px;
    font-size: 0.8rem;
    color: var(--text-dark);
    border: 1px solid rgba(58, 74, 107, 0.1);
  }
  
  .user-warning-badge i {
    color: var(--accent-blue);
  }
  
  .user-warning-badge.warning-high {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
  }
  
  .user-warning-badge.warning-high i {
    color: #dc3545;
  }
  
  .badge.bg-secondary {
    background: var(--text-light) !important;
    color: white !important;
  }
  
  .badge.bg-info {
    background: var(--accent-blue) !important;
    color: white !important;
  }

  /* NUEVO: Estilo para el botón de historial */
  .btn-history {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--light-bg);
    color: var(--accent-blue);
    border: 1px solid rgba(58, 74, 107, 0.1);
    transition: var(--transition);
    text-decoration: none;
  }
  
  .btn-history:hover {
    background: var(--accent-blue);
    color: white;
    border-color: var(--accent-blue);
    transform: translateY(-2px);
    box-shadow: var(--card-shadow);
  }
</style>

<div class="p-4">
  <div class="resenas-header d-flex justify-content-between align-items-center">
    <div>
      <h3><i class="fas fa-comments me-2"></i>Gestión de reseñas</h3>
      <small style="color: rgba(255,255,255,0.8);">Administra, filtra y responde reseñas de usuarios</small>
    </div>
    <a href="<?= BASE_URL ?>?c=Admin&a=index" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
  </div>

  <div class="filter-card">
    <form method="get" class="row g-3">
      <input type="hidden" name="c" value="Admin">
      <input type="hidden" name="a" value="resenas">
      
      <div class="col-md-3">
        <label class="form-label"><i class="fas fa-user me-1" style="color:var(--accent-blue)"></i>Usuario / Correo</label>
        <input type="text" name="usuario" class="form-control" placeholder="Buscar" value="<?= htmlspecialchars($_GET['usuario'] ?? '') ?>">
      </div>
      
      <div class="col-md-3">
        <label class="form-label"><i class="fas fa-box me-1" style="color:var(--accent-blue)"></i>Artículo</label>
        <input type="text" name="articulo" class="form-control" placeholder="Buscar" value="<?= htmlspecialchars($_GET['articulo'] ?? '') ?>">
      </div>
      
      <div class="col-md-2">
        <label class="form-label"><i class="fas fa-star me-1" style="color:var(--accent-blue)"></i>Calificación</label>
        <select name="calificacion" class="form-select">
          <option value="">Todas</option>
          <?php for($i=5;$i>=1;$i--): ?>
            <option value="<?= $i ?>" <?= (isset($_GET['calificacion']) && (int)$_GET['calificacion']==$i) ? 'selected' : '' ?>><?= $i ?> ★</option>
          <?php endfor; ?>
        </select>
      </div>
      
      <div class="col-md-2">
        <label class="form-label"><i class="fas fa-calendar-from me-1" style="color:var(--accent-blue)"></i>Desde</label>
        <input type="date" name="fecha_from" class="form-control" value="<?= htmlspecialchars($_GET['fecha_from'] ?? '') ?>">
      </div>
      
      <div class="col-md-2">
        <label class="form-label"><i class="fas fa-calendar-to me-1" style="color:var(--accent-blue)"></i>Hasta</label>
        <input type="date" name="fecha_to" class="form-control" value="<?= htmlspecialchars($_GET['fecha_to'] ?? '') ?>">
      </div>
      
      <div class="col-12 text-end pt-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.querySelector('form').reset(); window.location='<?= BASE_URL ?>?c=Admin&a=resenas';"><i class="fas fa-times me-1"></i>Limpiar</button>
        <button class="btn btn-filter btn-sm"><i class="fas fa-search me-1"></i>Filtrar</button>
      </div>
    </form>
  </div>

  <?php if (empty($resenas)): ?>
    <div class="card border-0" style="background:linear-gradient(135deg, rgba(58,74,107,0.05) 0%, rgba(74,91,125,0.05) 100%);">
      <div class="card-body text-center py-5">
        <i class="fas fa-inbox" style="font-size:3rem;color:var(--text-light);"></i>
        <h5 class="mt-3 text-muted">No hay reseñas registradas</h5>
        <p class="text-muted small">Las reseñas aparecerán aquí cuando los usuarios las publiquen</p>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-0">
      <?php foreach ($resenas as $r): 
        $warningClass = '';
        $warningIcon = 'fa-exclamation-triangle';
        $warningText = '';
        
        if (isset($r['num_advertencias'])) {
          if ($r['num_advertencias'] >= 3) {
            $warningClass = 'warning-high';
            $warningIcon = 'fa-ban';
            $warningText = 'Usuario bloqueado';
          } elseif ($r['num_advertencias'] > 0) {
            $warningClass = 'warning-high';
            $warningIcon = 'fa-exclamation-circle';
            $warningText = $r['num_advertencias'] . ' advertencia' . ($r['num_advertencias'] > 1 ? 's' : '');
          }
        }
      ?>
        <div class="col-12">
          <div class="card border-0 mb-3" style="box-shadow:var(--card-shadow);transition:var(--transition);">
            <div class="card-body p-4">
              <div class="row align-items-start">
                <div class="col-auto">
                  <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--accent-blue),var(--secondary-blue));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.1rem;"><?= strtoupper(substr($r['Nombre'] ?? 'U',0,1)) ?></div>
                </div>
                <div class="col">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <!-- SECCIÓN MODIFICADA: Ahora incluye el botón de historial -->
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h6 class="mb-0 fw-700" style="color:var(--primary-dark);"><?= htmlspecialchars($r['Nombre'] . ' ' . ($r['Apellido'] ?? '')) ?></h6>
                        
                        <?php if (isset($r['num_advertencias']) && $r['num_advertencias'] > 0): ?>
                          <span class="user-warning-badge <?= $warningClass ?>" title="Usuario con advertencias">
                            <i class="fas <?= $warningIcon ?>"></i>
                            <span><?= $warningText ?: $r['num_advertencias'] . ' adv' ?></span>
                          </span>
                        <?php endif; ?>
                        
                        <!-- NUEVO: Botón para ver historial de advertencias -->
                        <?php if (isset($r['ID_Usuario']) && $r['ID_Usuario']): ?>
                          <a href="<?= BASE_URL ?>?c=Admin&a=usuarioAdvertencias&id=<?= (int)$r['ID_Usuario'] ?>" 
                             class="btn-history" 
                             title="Ver historial de advertencias del usuario">
                            <i class="fas fa-history"></i>
                          </a>
                        <?php endif; ?>
                        
                        <?php if (isset($r['usuario_activo']) && $r['usuario_activo'] == 0): ?>
                          <span class="badge bg-secondary" style="font-size:0.75rem;">
                            <i class="fas fa-lock me-1"></i>Usuario inactivo
                          </span>
                        <?php endif; ?>
                      </div>
                      
                      <div class="small text-muted mt-1">
                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($r['Correo'] ?? '') ?>
                        <span class="ms-2"><i class="fas fa-calendar me-1"></i><?= date('d/m/Y H:i', strtotime($r['Fecha_Resena'])) ?></span>
                      </div>
                    </div>
                    <div class="text-end">
                      <span class="badge" style="background:linear-gradient(135deg,#ffc107,#ff9800);color:white;font-size:0.95rem;padding:8px 12px;"><?= (int)$r['Calificacion'] ?> ★</span>
                    </div>
                  </div>
                  
                  <div class="mt-2">
                    <p class="mb-1 fw-600" style="color:var(--primary-dark);"><?= htmlspecialchars($r['N_Articulo'] ?? '') ?></p>
                    <p class="mb-0 text-muted"><strong><?= htmlspecialchars($r['Titulo'] ?? '(Sin título)') ?></strong></p>
                    <p class="text-muted small mt-1"><?= nl2br(htmlspecialchars(substr($r['Comentario'],0,180))) ?><?= strlen($r['Comentario']) > 180 ? '...' : '' ?></p>
                  </div>

                  <div class="d-flex gap-2 flex-wrap mt-3">
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:0.85rem;padding:6px 10px;">
                      <i class="fas fa-thumbs-up me-1"></i>+<?= (int)($r['util_positivo'] ?? 0) ?>
                    </span>
                    <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:0.85rem;padding:6px 10px;">
                      <i class="fas fa-thumbs-down me-1"></i>-<?= (int)($r['util_negativo'] ?? 0) ?>
                    </span>
                    
                    <?php if ((int)($r['reportes_count'] ?? 0) > 0): ?>
                      <span class="badge bg-danger" style="font-size:0.85rem;padding:6px 10px;">
                        <i class="fas fa-flag me-1"></i><?= (int)($r['reportes_count'] ?? 0) ?> reporte<?= (int)($r['reportes_count'] ?? 0) > 1 ? 's' : '' ?>
                      </span>
                    <?php endif; ?>
                    
                    <?php if (isset($r['palabras_filtradas']) && $r['palabras_filtradas'] > 0): ?>
                      <span class="badge bg-warning text-dark" style="font-size:0.85rem;padding:6px 10px;" title="Esta reseña contenía palabras bloqueadas que fueron filtradas">
                        <i class="fas fa-filter me-1"></i><?= (int)$r['palabras_filtradas'] ?> palabra<?= (int)$r['palabras_filtradas'] > 1 ? 's' : '' ?> filtrada<?= (int)$r['palabras_filtradas'] > 1 ? 's' : '' ?>
                      </span>
                    <?php endif; ?>
                    
                    <?php if (isset($r['Modificado']) && $r['Modificado'] == 1): ?>
                      <span class="badge bg-info text-white" style="font-size:0.85rem;padding:6px 10px;">
                        <i class="fas fa-edit me-1"></i>Editada
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                <div>
                  <?php if (isset($r['tiene_advertencia']) && $r['tiene_advertencia']): ?>
                    <small class="text-danger">
                      <i class="fas fa-exclamation-circle me-1"></i>
                      Esta reseña generó una advertencia
                    </small>
                  <?php endif; ?>
                </div>
                
                <div>
                  <a href="<?= BASE_URL ?>?c=Admin&a=resenaVer&id=<?= (int)$r['ID_Resena'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-eye me-1"></i>Ver detalle
                  </a>
                  <form method="post" action="<?= BASE_URL ?>?c=Admin&a=resenaEliminar" style="display:inline-block;margin-left:8px;" onsubmit="return confirm('¿Eliminar esta reseña?');">
                    <input type="hidden" name="id_resena" value="<?= (int)$r['ID_Resena'] ?>">
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash me-1"></i>Eliminar
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (isset($pagination) && $pagination['last_page'] > 1): ?>
      <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
        <div class="text-muted small">
          <i class="fas fa-info-circle me-1"></i>
          Página <strong><?= $pagination['page'] ?></strong> de <strong><?= $pagination['last_page'] ?></strong> · 
          Total: <strong><?= $pagination['total'] ?></strong> reseña<?= $pagination['total'] > 1 ? 's' : '' ?>
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <?php for($p=1;$p<=$pagination['last_page'];$p++): ?>
              <?php
                $query = $_GET;
                $query['page'] = $p;
                $qs = http_build_query($query);
              ?>
              <li class="page-item <?= $p==$pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= BASE_URL ?>?<?= $qs ?>"><?= $p ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>