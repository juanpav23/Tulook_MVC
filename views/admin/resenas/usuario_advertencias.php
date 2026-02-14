<?php
if (!isset($usuario) || !$usuario) {
    echo '<div class="p-4"><div class="alert alert-warning">Usuario no encontrado</div></div>';
    return;
}

$advertencias = $advertencias ?? [];
$resenas = $resenas ?? [];
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

  .advertencias-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    color: white;
    padding: 28px 24px;
    border-radius: var(--border-radius);
    margin-bottom: 24px;
    box-shadow: var(--card-shadow);
  }

  .advertencias-header h3 {
    margin: 0;
    font-weight: 800;
    font-size: 1.8rem;
    letter-spacing: 0.5px;
  }
  
  .warning-item {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    transition: var(--transition);
  }
  
  .warning-item:hover {
    transform: translateX(4px);
    box-shadow: var(--card-shadow);
  }
  
  .warning-item.grave {
    background: #f8d7da;
    border-left-color: #dc3545;
  }
  
  .warning-item.media {
    background: #fff3cd;
    border-left-color: #fd7e14;
  }
  
  .usuario-info-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 24px;
    box-shadow: var(--card-shadow);
    margin-bottom: 24px;
    border: 1px solid rgba(42, 52, 72, 0.04);
  }
  
  .info-label {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-bottom: 4px;
  }
  
  .info-value {
    font-weight: 600;
    color: var(--text-dark);
  }
  
  .badge-count {
    font-size: 1.1rem;
    padding: 6px 12px;
  }
  
  .btn-back {
    background: white;
    color: var(--primary-dark);
    border: 1px solid rgba(255,255,255,0.3);
    transition: var(--transition);
  }
  
  .btn-back:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    border-color: white;
  }
  
  .resena-mini-card {
    background: var(--light-bg);
    border-radius: 8px;
    padding: 12px;
    transition: var(--transition);
    border: 1px solid rgba(42, 52, 72, 0.04);
  }
  
  .resena-mini-card:hover {
    background: white;
    border-color: var(--accent-blue);
  }
  
  .stat-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.5rem;
  }
  
  .stat-circle.danger {
    background: #f8d7da;
    color: #dc3545;
  }
  
  .stat-circle.warning {
    background: #fff3cd;
    color: #856404;
  }
  
  .stat-circle.success {
    background: #d4edda;
    color: #28a745;
  }
</style>

<div class="p-4">
  <div class="advertencias-header d-flex justify-content-between align-items-center">
    <div>
      <h3><i class="fas fa-exclamation-triangle me-2"></i>Historial de advertencias</h3>
      <small style="color: rgba(255,255,255,0.8);">
        <i class="fas fa-user me-1"></i><?= htmlspecialchars($usuario['Nombre'] . ' ' . ($usuario['Apellido'] ?? '')) ?> 
        (<?= htmlspecialchars($usuario['Correo']) ?>)
      </small>
    </div>
    <a href="<?= BASE_URL ?>?c=Admin&a=resenas" class="btn btn-back btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Volver a reseñas
    </a>
  </div>
  
  <div class="usuario-info-card">
    <div class="row align-items-center">
      <div class="col-md-auto text-center mb-3 mb-md-0">
        <?php
        $totalAdv = (int)($usuario['Num_Advertencias'] ?? 0);
        if ($totalAdv >= 3):
        ?>
          <div class="stat-circle danger mx-auto">
            <i class="fas fa-ban"></i>
          </div>
        <?php elseif ($totalAdv > 0): ?>
          <div class="stat-circle warning mx-auto">
            <?= $totalAdv ?>
          </div>
        <?php else: ?>
          <div class="stat-circle success mx-auto">
            <i class="fas fa-check"></i>
          </div>
        <?php endif; ?>
      </div>
      <div class="col-md">
        <div class="row">
          <div class="col-sm-6 mb-3">
            <div class="info-label"><i class="fas fa-user me-1" style="color: var(--accent-blue);"></i>Nombre completo</div>
            <div class="info-value"><?= htmlspecialchars($usuario['Nombre'] . ' ' . ($usuario['Apellido'] ?? '')) ?></div>
          </div>
          <div class="col-sm-6 mb-3">
            <div class="info-label"><i class="fas fa-envelope me-1" style="color: var(--accent-blue);"></i>Correo electrónico</div>
            <div class="info-value"><?= htmlspecialchars($usuario['Correo']) ?></div>
          </div>
          <div class="col-sm-6">
            <div class="info-label"><i class="fas fa-exclamation-circle me-1" style="color: var(--accent-blue);"></i>Total advertencias</div>
            <div class="info-value">
              <span class="badge bg-<?= $totalAdv >= 3 ? 'danger' : ($totalAdv > 0 ? 'warning' : 'success') ?> badge-count">
                <?= $totalAdv ?> advertencia<?= $totalAdv != 1 ? 's' : '' ?>
              </span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-label"><i class="fas fa-<?= $usuario['Activo'] ? 'check-circle' : 'ban' ?> me-1" style="color: var(--accent-blue);"></i>Estado</div>
            <div class="info-value">
              <?php if ($usuario['Activo']): ?>
                <span class="badge bg-success">Activo</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactivo</span>
                <?php if (!empty($usuario['Motivo_Desactivacion'])): ?>
                  <small class="d-block text-muted mt-1"><?= htmlspecialchars($usuario['Motivo_Desactivacion']) ?></small>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <?php if (empty($advertencias)): ?>
    <div class="card border-0" style="background:linear-gradient(135deg, rgba(58,74,107,0.05) 0%, rgba(74,91,125,0.05) 100%);">
      <div class="card-body text-center py-5">
        <i class="fas fa-check-circle" style="font-size:3rem;color:var(--text-light);"></i>
        <h5 class="mt-3 text-muted">No hay advertencias para este usuario</h5>
        <p class="text-muted small">Este usuario no tiene historial de infracciones</p>
      </div>
    </div>
  <?php else: ?>
    <h5 class="fw-700 mb-3" style="color: var(--primary-dark);">
      <i class="fas fa-history me-2" style="color: var(--accent-blue);"></i>
      Historial de advertencias (<?= count($advertencias) ?>)
    </h5>
    <div class="row">
      <?php foreach ($advertencias as $adv): 
        $tipoClass = strtolower($adv['Tipo'] ?? 'leve');
        $fecha = new DateTime($adv['Fecha_Advertencia']);
      ?>
        <div class="col-12">
          <div class="warning-item <?= $tipoClass ?>">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
              <div style="flex: 1;">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="badge bg-<?= $tipoClass == 'grave' ? 'danger' : ($tipoClass == 'media' ? 'warning' : 'secondary') ?>">
                    <i class="fas fa-<?= $tipoClass == 'grave' ? 'skull' : ($tipoClass == 'media' ? 'exclamation' : 'info-circle') ?> me-1"></i>
                    <?= $adv['Tipo'] ?? 'Leve' ?>
                  </span>
                  <small class="text-muted">
                    <i class="fas fa-clock me-1"></i><?= $fecha->format('d/m/Y H:i') ?>
                  </small>
                </div>
                <h6 class="fw-700 mb-2" style="color: var(--text-dark);"><?= htmlspecialchars($adv['Motivo'] ?? 'Sin motivo') ?></h6>
                <?php if (!empty($adv['Descripcion'])): ?>
                  <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($adv['Descripcion'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($adv['AdminNombre'])): ?>
                  <div class="small text-muted">
                    <i class="fas fa-user-shield me-1" style="color: var(--accent-blue);"></i>
                    Creada por: <?= htmlspecialchars($adv['AdminNombre'] . ' ' . ($adv['AdminApellido'] ?? 'Sistema')) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($resenas)): ?>
    <div class="mt-5">
      <h5 class="fw-700 mb-3" style="color: var(--primary-dark);">
        <i class="fas fa-comments me-2" style="color: var(--accent-blue);"></i>
        Últimas reseñas del usuario
      </h5>
      <div class="row">
        <?php foreach ($resenas as $r): ?>
          <div class="col-md-6 mb-3">
            <div class="resena-mini-card">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge" style="background: linear-gradient(135deg, #ffc107, #ff9800); color: white;">
                  <?= (int)$r['Calificacion'] ?> ★
                </span>
                <small class="text-muted">
                  <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($r['Fecha_Resena'])) ?>
                </small>
              </div>
              <p class="small mb-2" style="color: var(--text-dark);">
                <strong><?= htmlspecialchars($r['N_Articulo'] ?? '') ?></strong>
              </p>
              <p class="small text-muted mb-2"><?= nl2br(htmlspecialchars(substr($r['Comentario'] ?? '', 0, 100))) ?>...</p>
              <div class="text-end">
                <a href="<?= BASE_URL ?>?c=Admin&a=resenaVer&id=<?= (int)$r['ID_Resena'] ?>" class="btn btn-sm btn-link p-0">
                  Ver reseña completa <i class="fas fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Botón para crear nueva advertencia -->
  <div class="text-end mt-4">
    <button type="button" class="btn btn-warning" onclick="mostrarFormAdvertencia(<?= (int)$usuario['ID_Usuario'] ?>)">
      <i class="fas fa-exclamation-triangle me-2"></i>Crear nueva advertencia
    </button>
  </div>
</div>

<!-- Modal para crear advertencia -->
<div class="modal fade" id="modalAdvertencia" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crear advertencia para usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="post" action="<?= BASE_URL ?>?c=Admin&a=crearAdvertencia">
        <div class="modal-body">
          <input type="hidden" name="id_usuario" id="modal_id_usuario" value="<?= (int)$usuario['ID_Usuario'] ?>">
          
          <div class="mb-3">
            <label class="form-label">Tipo de advertencia</label>
            <select name="tipo" class="form-select" required>
              <option value="Leve">Leve</option>
              <option value="Media">Media</option>
              <option value="Grave">Grave</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="motivo" class="form-control" placeholder="Ej: Lenguaje inapropiado" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Descripción (opcional)</label>
            <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles adicionales..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning">Crear advertencia</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function mostrarFormAdvertencia(idUsuario) {
  document.getElementById('modal_id_usuario').value = idUsuario;
  new bootstrap.Modal(document.getElementById('modalAdvertencia')).show();
}
</script>