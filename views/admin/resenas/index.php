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
    --card-shadow: 0 6px 20px rgba(10, 20, 30, 0.08);
    --card-shadow-hover: 0 12px 36px rgba(10, 20, 30, 0.12);
    --transition: all 0.28s cubic-bezier(.2,.9,.2,1);
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
      <?php foreach ($resenas as $r): ?>
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
                      <h6 class="mb-0 fw-700" style="color:var(--primary-dark);"><?= htmlspecialchars($r['Nombre'] . ' ' . ($r['Apellido'] ?? '')) ?></h6>
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

                  <div class="row g-3 mt-3">
                    <div class="col-auto">
                      <div class="d-flex gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success" style="font-size:0.85rem;padding:6px 10px;"><i class="fas fa-thumbs-up me-1"></i>+<?= (int)$r['util_positivo'] ?></span>
                        <span class="badge bg-danger bg-opacity-10 text-danger" style="font-size:0.85rem;padding:6px 10px;"><i class="fas fa-thumbs-down me-1"></i>-<?= (int)$r['util_negativo'] ?></span>
                      </div>
                    </div>
                    <div class="col-auto">
                      <?php if ((int)($r['reportes_count'] ?? 0) > 0): ?>
                        <span class="badge bg-danger" style="font-size:0.85rem;padding:6px 10px;"><i class="fas fa-flag me-1"></i><?= (int)($r['reportes_count'] ?? 0) ?> reporte<?= (int)($r['reportes_count'] ?? 0) > 1 ? 's' : '' ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-3 pt-3 border-top text-end">
                <a href="<?= BASE_URL ?>?c=Admin&a=resenaVer&id=<?= (int)$r['ID_Resena'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye me-1"></i>Ver detalle</a>
                <form method="post" action="<?= BASE_URL ?>?c=Admin&a=resenaEliminar" style="display:inline-block;margin-left:8px;" onsubmit="return confirm('¿Eliminar esta reseña?');">
                  <input type="hidden" name="id_resena" value="<?= (int)$r['ID_Resena'] ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Eliminar</button>
                </form>
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
