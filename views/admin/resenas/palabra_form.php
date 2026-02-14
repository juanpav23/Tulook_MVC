<?php
$palabra = $palabra ?? null;
$titulo = $palabra ? 'Editar palabra' : 'Nueva palabra';
?>
<style>
  .form-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    color: white;
    padding: 28px 24px;
    border-radius: var(--border-radius);
    margin-bottom: 24px;
    box-shadow: var(--card-shadow);
  }
  
  .form-header h3 {
    margin: 0;
    font-weight: 800;
    font-size: 1.8rem;
    letter-spacing: 0.5px;
  }
  
  .form-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 32px;
    box-shadow: var(--card-shadow);
    max-width: 600px;
    margin: 0 auto;
  }
  
  .form-label {
    font-weight: 600;
    color: var(--primary-dark);
    margin-bottom: 8px;
    font-size: 0.9rem;
  }
  
  .form-control, .form-select {
    border-radius: 8px;
    border: 1px solid rgba(42, 52, 72, 0.1);
    padding: 12px 16px;
    transition: var(--transition);
  }
  
  .form-control:focus, .form-select:focus {
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 0.2rem rgba(58, 74, 107, 0.15);
    outline: none;
  }
  
  .gravedad-option {
    padding: 10px 16px;
    border-radius: 8px;
    border: 1px solid rgba(42, 52, 72, 0.1);
    cursor: pointer;
    transition: var(--transition);
  }
  
  .gravedad-option:hover {
    border-color: var(--accent-blue);
    background: var(--light-bg);
  }
  
  .gravedad-option.selected {
    border-color: var(--accent-blue);
    background: var(--light-bg);
  }
  
  .gravedad-option input[type="radio"] {
    display: none;
  }
  
  .btn-submit {
    background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
    border: none;
    color: white;
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
  }
  
  .btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: var(--card-shadow-hover);
    color: white;
  }
  
  .btn-cancel {
    background: var(--light-bg);
    border: 1px solid rgba(42, 52, 72, 0.1);
    color: var(--text-dark);
    padding: 12px 32px;
    border-radius: 8px;
    font-weight: 600;
    transition: var(--transition);
    text-decoration: none;
    display: inline-block;
  }
  
  .btn-cancel:hover {
    border-color: var(--accent-blue);
    color: var(--accent-blue);
    background: white;
  }
  
  .form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
  
  .form-check-input:checked {
    background-color: var(--accent-blue);
    border-color: var(--accent-blue);
  }
</style>

<div class="p-4">
  <div class="form-header d-flex justify-content-between align-items-center">
    <div>
      <h3><i class="fas fa-<?= $palabra ? 'edit' : 'plus' ?> me-2"></i><?= $titulo ?></h3>
      <small style="color: rgba(255,255,255,0.8);"><?= $palabra ? 'Modifica' : 'Agrega' ?> palabras para moderar reseñas</small>
    </div>
    <a href="<?= BASE_URL ?>?c=Admin&a=palabrasBloqueadas" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
  </div>

  <div class="form-card">
    <form method="post" action="<?= BASE_URL ?>?c=Admin&a=palabraGuardar">
      <?php if ($palabra): ?>
        <input type="hidden" name="ID_Palabra" value="<?= (int)$palabra['ID_Palabra'] ?>">
      <?php endif; ?>
      
      <div class="mb-4">
        <label class="form-label">
          <i class="fas fa-font me-2" style="color: var(--accent-blue);"></i>Palabra a bloquear
        </label>
        <input type="text" 
               name="Palabra" 
               class="form-control" 
               value="<?= htmlspecialchars($palabra['Palabra'] ?? '') ?>" 
               placeholder="Ej: grosería, insulto, etc."
               required
               maxlength="100">
        <small class="text-muted mt-2 d-block">
          <i class="fas fa-info-circle me-1"></i>La palabra será detectada en mayúsculas, minúsculas y con acentos
        </small>
      </div>
      
      <div class="mb-4">
        <label class="form-label mb-3">
          <i class="fas fa-exclamation-triangle me-2" style="color: var(--accent-blue);"></i>Nivel de gravedad
        </label>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="gravedad-option d-block <?= (($palabra['Gravedad'] ?? 'Leve') == 'Leve') ? 'selected' : '' ?>">
              <input type="radio" name="Gravedad" value="Leve" <?= (($palabra['Gravedad'] ?? 'Leve') == 'Leve') ? 'checked' : '' ?> required>
              <div class="text-center">
                <i class="fas fa-info-circle mb-2" style="color: #ffc107; font-size: 1.5rem;"></i>
                <div class="fw-600">Leve</div>
                <small class="text-muted">Solo filtra, no crea advertencia</small>
              </div>
            </label>
          </div>
          <div class="col-md-4">
            <label class="gravedad-option d-block <?= (($palabra['Gravedad'] ?? '') == 'Media') ? 'selected' : '' ?>">
              <input type="radio" name="Gravedad" value="Media" <?= (($palabra['Gravedad'] ?? '') == 'Media') ? 'checked' : '' ?>>
              <div class="text-center">
                <i class="fas fa-exclamation mb-2" style="color: #fd7e14; font-size: 1.5rem;"></i>
                <div class="fw-600">Media</div>
                <small class="text-muted">Filtra y crea advertencia</small>
              </div>
            </label>
          </div>
          <div class="col-md-4">
            <label class="gravedad-option d-block <?= (($palabra['Gravedad'] ?? '') == 'Grave') ? 'selected' : '' ?>">
              <input type="radio" name="Gravedad" value="Grave" <?= (($palabra['Gravedad'] ?? '') == 'Grave') ? 'checked' : '' ?>>
              <div class="text-center">
                <i class="fas fa-skull mb-2" style="color: #dc3545; font-size: 1.5rem;"></i>
                <div class="fw-600">Grave</div>
                <small class="text-muted">Rechaza y crea advertencia</small>
              </div>
            </label>
          </div>
        </div>
      </div>
      
      <div class="mb-4">
        <div class="form-check">
          <input type="checkbox" 
                 name="Activo" 
                 class="form-check-input" 
                 id="activoCheck"
                 <?= (!isset($palabra) || ($palabra['Activo'] ?? 1)) ? 'checked' : '' ?>>
          <label class="form-check-label fw-600" for="activoCheck" style="color: var(--text-dark);">
            <i class="fas fa-check-circle me-2" style="color: var(--accent-blue);"></i>Activo
          </label>
          <small class="text-muted d-block mt-1">Si está inactiva, la palabra no será bloqueada</small>
        </div>
      </div>
      
      <div class="d-flex gap-3 justify-content-end mt-5">
        <a href="<?= BASE_URL ?>?c=Admin&a=palabrasBloqueadas" class="btn-cancel">
          <i class="fas fa-times me-2"></i>Cancelar
        </a>
        <button type="submit" class="btn-submit">
          <i class="fas fa-save me-2"></i><?= $palabra ? 'Actualizar' : 'Guardar' ?> palabra
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Marcar visualmente la opción seleccionada
document.querySelectorAll('.gravedad-option').forEach(option => {
  option.addEventListener('click', function() {
    document.querySelectorAll('.gravedad-option').forEach(opt => {
      opt.classList.remove('selected');
    });
    this.classList.add('selected');
    this.querySelector('input[type="radio"]').checked = true;
  });
});
</script>