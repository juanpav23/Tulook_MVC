<?php
if (!isset($palabras)) $palabras = [];
?>
<style>
  .palabras-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    color: white;
    padding: 28px 24px;
    border-radius: var(--border-radius);
    margin-bottom: 24px;
    box-shadow: var(--card-shadow);
  }

  .palabras-header h3 {
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
  
  .gravedad-badge {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  
  .gravedad-grave {
    background: #dc3545;
    color: white;
  }
  
  .gravedad-media {
    background: #fd7e14;
    color: white;
  }
  
  .gravedad-leve {
    background: #ffc107;
    color: #856404;
  }
  
  .switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 26px;
  }
  
  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }
  
  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--text-light);
    transition: var(--transition);
    border-radius: 26px;
  }
  
  .slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: var(--transition);
    border-radius: 50%;
  }
  
  input:checked + .slider {
    background-color: var(--accent-blue);
  }
  
  input:checked + .slider:before {
    transform: translateX(26px);
  }
  
  .table-palabras {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--card-shadow);
  }
  
  .table-palabras th {
    background: var(--light-bg);
    color: var(--primary-dark);
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 20px;
    border: none;
  }
  
  .table-palabras td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(42, 52, 72, 0.04);
    color: var(--text-dark);
  }
  
  .table-palabras tr:last-child td {
    border-bottom: none;
  }
  
  .table-palabras tr:hover td {
    background: var(--light-bg);
  }
  
  .btn-action {
    padding: 6px 12px;
    border-radius: 8px;
    transition: var(--transition);
    margin: 0 2px;
    border: 1px solid rgba(42, 52, 72, 0.1);
    background: white;
    color: var(--text-dark);
  }
  
  .btn-action:hover {
    border-color: var(--accent-blue);
    color: var(--accent-blue);
    background: var(--light-bg);
    transform: translateY(-1px);
    box-shadow: var(--card-shadow);
  }
  
  .btn-action-danger:hover {
    border-color: #dc3545;
    color: #dc3545;
  }
  
  .btn-primary {
    background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
    border: none;
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
  }
  
  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow-hover);
    color: white;
  }
  
  .btn-secondary {
    background: var(--light-bg);
    border: 1px solid rgba(42, 52, 72, 0.1);
    color: var(--text-dark);
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
  }
  
  .btn-secondary:hover {
    border-color: var(--accent-blue);
    color: var(--accent-blue);
    background: white;
  }
  
  .empty-state {
    background: linear-gradient(135deg, var(--light-bg) 0%, white 100%);
    border-radius: var(--border-radius);
    padding: 60px 20px;
    text-align: center;
    box-shadow: var(--card-shadow);
  }
  
  .empty-state i {
    font-size: 4rem;
    color: var(--light-blue);
    margin-bottom: 20px;
    opacity: 0.5;
  }
  
  .empty-state h5 {
    color: var(--text-dark);
    font-weight: 700;
    margin-bottom: 10px;
  }
  
  .empty-state p {
    color: var(--text-light);
    margin-bottom: 20px;
  }
</style>

<div class="p-4">
  <div class="palabras-header d-flex justify-content-between align-items-center">
    <div>
      <h3><i class="fas fa-ban me-2"></i>Palabras bloqueadas</h3>
      <small style="color: rgba(255,255,255,0.8);">Gestiona las palabras prohibidas en reseñas</small>
    </div>
    <div>
      <a href="<?= BASE_URL ?>?c=Admin&a=palabraForm" class="btn btn-primary btn-sm me-2">
        <i class="fas fa-plus me-1"></i>Nueva palabra
      </a>
      <a href="<?= BASE_URL ?>?c=Admin&a=resenas" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Volver a reseñas
      </a>
    </div>
  </div>

  <?php if (empty($palabras)): ?>
    <div class="empty-state">
      <i class="fas fa-shield-alt"></i>
      <h5>No hay palabras bloqueadas</h5>
      <p class="text-muted">Agrega palabras para moderar automáticamente el contenido de las reseñas</p>
      <a href="<?= BASE_URL ?>?c=Admin&a=palabraForm" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Agregar primera palabra
      </a>
    </div>
  <?php else: ?>
    <div class="table-palabras">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Palabra</th>
            <th>Gravedad</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($palabras as $p): ?>
            <tr>
              <td class="fw-600"><?= htmlspecialchars($p['Palabra']) ?></td>
              <td>
                <span class="gravedad-badge gravedad-<?= strtolower($p['Gravedad'] ?? 'leve') ?>">
                  <i class="fas fa-<?= $p['Gravedad'] == 'Grave' ? 'skull' : ($p['Gravedad'] == 'Media' ? 'exclamation' : 'info-circle') ?>"></i>
                  <?= $p['Gravedad'] ?? 'Leve' ?>
                </span>
              </td>
              <td>
                <label class="switch">
                  <input type="checkbox" 
                         class="estado-toggle" 
                         data-id="<?= (int)$p['ID_Palabra'] ?>"
                         <?= ($p['Activo'] ?? 1) ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </label>
              </td>
              <td class="text-muted small">
                <i class="fas fa-calendar me-1"></i>
                <?= date('d/m/Y', strtotime($p['Fecha_Creacion'] ?? 'now')) ?>
              </td>
              <td class="text-end">
                <a href="<?= BASE_URL ?>?c=Admin&a=palabraForm&id=<?= (int)$p['ID_Palabra'] ?>" 
                   class="btn-action me-1" title="Editar">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="<?= BASE_URL ?>?c=Admin&a=palabraEliminar&id=<?= (int)$p['ID_Palabra'] ?>" 
                   class="btn-action btn-action-danger"
                   onclick="return confirm('¿Estás seguro de eliminar esta palabra?')" title="Eliminar">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.estado-toggle').forEach(toggle => {
  toggle.addEventListener('change', function() {
    const id = this.dataset.id;
    const estado = this.checked ? 1 : 0;
    
    fetch('<?= BASE_URL ?>?c=Admin&a=palabraToggleEstado', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}&estado=${estado}`
    })
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        this.checked = !this.checked;
        alert('Error al cambiar el estado');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      this.checked = !this.checked;
    });
  });
});
</script>