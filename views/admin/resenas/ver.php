<?php
if (!isset($resena) || !$resena) {
  echo '<div class="p-4"><div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Reseña no encontrada.</div></div>'; return;
}
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

  .resena-detail-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    color: white;
    padding: 32px 28px;
    border-radius: var(--border-radius);
    margin-bottom: 28px;
    box-shadow: var(--card-shadow);
  }

  .resena-detail-header h3 {
    font-weight: 800;
    letter-spacing: 0.5px;
    margin: 0;
  }

  .resena-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 24px;
    box-shadow: var(--card-shadow);
    margin-bottom: 24px;
    border: 1px solid rgba(42, 52, 72, 0.04);
    transition: var(--transition);
  }

  .resena-section:hover {
    box-shadow: var(--card-shadow-hover);
  }

  .resena-section h5 {
    color: var(--primary-dark);
    font-weight: 800;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 2px solid rgba(58, 74, 107, 0.12);
    font-size: 1.1rem;
    letter-spacing: 0.3px;
  }

  .resena-main-content {
    display: flex;
    gap: 20px;
    align-items: flex-start;
  }

  .resena-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-blue), var(--secondary-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 800;
    font-size: 2rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(58, 74, 107, 0.2);
  }

  .resena-info-block {
    flex: 1;
  }

  .resena-info-block h5 {
    color: var(--primary-dark);
    font-weight: 800;
    margin: 0 0 8px 0;
    font-size: 1.15rem;
    border: none;
    padding: 0;
  }

  .resena-meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin: 8px 0 12px 0;
  }

  .resena-meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    color: var(--text-light);
  }

  .resena-meta-item i {
    color: var(--accent-blue);
  }

  .resena-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--primary-dark);
    margin: 12px 0 8px 0;
  }

  .resena-body {
    color: var(--text-dark);
    line-height: 1.7;
    font-size: 0.95rem;
  }

  .foto-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
  }

  .foto-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: var(--transition);
  }

  .foto-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  }

  .foto-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
  }

  .response-item {
    background: linear-gradient(135deg, rgba(58, 74, 107, 0.03) 0%, rgba(74, 91, 125, 0.03) 100%);
    border-left: 4px solid var(--accent-blue);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 14px;
    transition: var(--transition);
  }

  .response-item:hover {
    background: linear-gradient(135deg, rgba(58, 74, 107, 0.06) 0%, rgba(74, 91, 125, 0.06) 100%);
  }

  .response-author {
    font-weight: 700;
    color: var(--primary-dark);
    font-size: 0.95rem;
  }

  .response-date {
    font-size: 0.8rem;
    color: var(--text-light);
  }

  .response-text {
    color: var(--text-dark);
    line-height: 1.6;
    margin-top: 8px;
    font-size: 0.95rem;
  }

  .report-item {
    background: #f8f9fa;
    border-left: 4px solid var(--accent-blue);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 14px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
  }

  .report-content {
    flex: 1;
  }

  .report-motivo {
    font-weight: 700;
    color: var(--primary-dark);
    font-size: 0.95rem;
  }

  .report-fecha {
    font-size: 0.8rem;
    color: var(--text-light);
    margin-top: 2px;
  }

  .report-descripcion {
    color: var(--text-dark);
    font-size: 0.9rem;
    margin-top: 8px;
    line-height: 1.5;
  }

  .report-estado {
    margin-top: 10px;
  }

  .form-response {
    background: linear-gradient(135deg, rgba(58, 74, 107, 0.02) 0%, rgba(74, 91, 125, 0.02) 100%);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid rgba(58, 74, 107, 0.06);
  }

  .form-response textarea {
    border: 1px solid rgba(42, 52, 72, 0.08);
    border-radius: 8px;
    transition: var(--transition);
  }

  .form-response textarea:focus {
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 0.2rem rgba(58, 74, 107, 0.15);
  }

  .badge-success {
    background: #28a745;
  }

  .badge-pending {
    background: #0d6efd;
  }

  .badge-discarded {
    background: #6c757d;
  }

  .badge.bg-info {
    background: var(--accent-blue) !important;
    color: white !important;
  }

  @media (max-width: 768px) {
    .resena-main-content {
      flex-direction: column;
      gap: 16px;
    }

    .resena-avatar {
      width: 80px;
      height: 80px;
      font-size: 1.5rem;
    }

    .foto-gallery {
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }

    .foto-item img {
      height: 160px;
    }
  }
</style>

<div class="p-4">
  <div class="resena-detail-header d-flex justify-content-between align-items-center">
    <div>
      <h3><i class="fas fa-comments me-2"></i>Reseña #<?= (int)$resena['ID_Resena'] ?></h3>
      <small style="color: rgba(255,255,255,0.8); font-weight: 500;"><i class="fas fa-eye me-1"></i>Detalle y gestión completa</small>
    </div>
    <a href="<?= BASE_URL ?>?c=Admin&a=resenas" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Volver</a>
  </div>

  <!-- SECCIÓN PRINCIPAL DE RESEÑA -->
  <div class="resena-section">
    <div class="resena-main-content">
      <div class="resena-avatar"><?= strtoupper(substr($resena['Nombre'] ?? 'U',0,1)) ?></div>
      <div class="resena-info-block">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <h5><?= htmlspecialchars($resena['Nombre'] . ' ' . ($resena['Apellido'] ?? '')) ?></h5>
          
          <?php if (isset($resena['Modificado']) && $resena['Modificado'] == 1): ?>
            <span class="badge bg-info" style="font-size:0.8rem;">
              <i class="fas fa-edit me-1"></i>Editada
            </span>
          <?php endif; ?>
          
          <?php if (isset($resena['palabras_filtradas']) && $resena['palabras_filtradas'] > 0): ?>
            <span class="badge bg-warning text-dark" style="font-size:0.8rem;" title="Esta reseña contenía palabras bloqueadas que fueron filtradas">
              <i class="fas fa-filter me-1"></i>Contenido filtrado
            </span>
          <?php endif; ?>
        </div>
        
        <div class="resena-meta">
          <div class="resena-meta-item">
            <i class="fas fa-envelope"></i>
            <?= htmlspecialchars($resena['Correo'] ?? '') ?>
          </div>
          <div class="resena-meta-item">
            <i class="fas fa-calendar"></i>
            <?= date('d/m/Y H:i', strtotime($resena['Fecha_Resena'])) ?>
          </div>
          <?php if (!empty($resena['Fecha_Actualizacion']) && $resena['Fecha_Actualizacion'] != $resena['Fecha_Resena']): ?>
          <div class="resena-meta-item">
            <i class="fas fa-pen"></i>
            Editada: <?= date('d/m/Y H:i', strtotime($resena['Fecha_Actualizacion'])) ?>
          </div>
          <?php endif; ?>
          <div class="resena-meta-item" style="margin-left: auto;">
            <span class="badge" style="background: linear-gradient(135deg, #ffc107, #ff9800); color: white; font-size: 0.85rem; padding: 8px 14px;">
              <i class="fas fa-star me-1"></i><?= (int)$resena['Calificacion'] ?> Estrellas
            </span>
          </div>
        </div>

        <p class="resena-meta-item" style="margin-top: 10px;">
          <i class="fas fa-box" style="color: var(--accent-blue);"></i>
          <strong><?= htmlspecialchars($resena['N_Articulo'] ?? '') ?></strong>
        </p>

        <div class="resena-title"><?= htmlspecialchars($resena['Titulo'] ?? '(Sin título)') ?></div>
        <div class="resena-body"><?= nl2br(htmlspecialchars($resena['Comentario'])) ?></div>
        
        <?php if (isset($resena['texto_original']) && $resena['texto_original'] != $resena['Comentario']): ?>
        <div class="alert alert-warning mt-3 py-2" style="font-size:0.9rem;">
          <i class="fas fa-info-circle me-1"></i>
          <strong>Texto original contenía palabras filtradas:</strong>
          <div class="text-muted small mt-1"><?= htmlspecialchars($resena['texto_original']) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- FOTOS -->
  <?php if (!empty($fotos)): ?>
    <div class="resena-section">
      <h5><i class="fas fa-images me-2" style="color: var(--accent-blue);"></i>Fotos adjuntas (<?= count($fotos) ?>)</h5>
      <div class="foto-gallery">
        <?php foreach($fotos as $foto):
          $fotoUrl = preg_match('/^https?:\/\//i', $foto['Foto']) ? $foto['Foto'] : rtrim(BASE_URL, '/') . '/' . ltrim($foto['Foto'], '/');
        ?>
          <div class="foto-item">
            <img src="<?= $fotoUrl ?>" alt="foto reseña" loading="lazy">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- RESPUESTAS -->
  <?php if (!empty($respuestas) || (isset($_SESSION['rol']) && ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2))): ?>
    <div class="resena-section">
      <h5><i class="fas fa-reply me-2" style="color: var(--accent-blue);"></i>Respuestas (<?= count($respuestas ?? []) ?>)</h5>
      
      <?php if (!empty($respuestas)): ?>
        <div style="margin-bottom: 20px;">
          <?php foreach($respuestas as $resp): ?>
            <div class="response-item">
              <div class="response-author"><?= htmlspecialchars($resp['Nombre'] . ' ' . ($resp['Apellido'] ?? '')) ?></div>
              <div class="response-date"><i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($resp['Fecha_Respuesta'])) ?></div>
              <div class="response-text"><?= nl2br(htmlspecialchars($resp['Respuesta'])) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['rol']) && ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2)): ?>
        <form method="post" action="<?= BASE_URL ?>?c=Admin&a=resenaResponder" class="form-response">
          <input type="hidden" name="id_resena" value="<?= (int)$resena['ID_Resena'] ?>">
          <div class="mb-3">
            <label class="form-label fw-600" style="color: var(--primary-dark); margin-bottom: 10px;">
              <i class="fas fa-pen-fancy me-1" style="color: var(--accent-blue);"></i>Añadir respuesta
            </label>
            <textarea name="respuesta" class="form-control" rows="4" placeholder="Escribe tu respuesta aquí..." required style="resize: vertical;"></textarea>
            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i>La respuesta será visible para el usuario en la página del producto</small>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-2"></i>Enviar respuesta</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- REPORTES -->
  <div class="resena-section">
    <h5><i class="fas fa-flag me-2" style="color: var(--accent-blue);"></i>Reportes de usuarios (<?= count($reportes ?? []) ?>)</h5>
    
    <?php if (empty($reportes)): ?>
      <div style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.08) 0%, rgba(40, 167, 69, 0.04) 100%); padding: 20px; border-radius: 10px; text-align: center; border: 1px solid rgba(40, 167, 69, 0.1);">
        <i class="fas fa-check-circle" style="font-size: 2.5rem; color: #28a745; display: block; margin-bottom: 10px;"></i>
        <p class="text-muted mb-0" style="font-weight: 500;">No hay reportes para esta reseña</p>
      </div>
    <?php else: ?>
      <div>
        <?php foreach($reportes as $rep): ?>
          <div class="report-item">
            <div class="report-content">
              <div class="report-motivo"><?= htmlspecialchars($rep['Motivo']) ?></div>
              <div class="report-fecha"><i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($rep['Fecha_Reporte'])) ?></div>
              <?php if (!empty($rep['Descripcion'])): ?>
                <div class="report-descripcion"><?= nl2br(htmlspecialchars($rep['Descripcion'])) ?></div>
              <?php endif; ?>
              <div class="report-estado">
                <span class="badge badge-<?= $rep['Estado']=='Revisado' ? 'success' : ($rep['Estado']=='Descartado' ? 'discarded' : 'pending') ?>" style="color: white;">
                  <?php if ($rep['Estado']=='Revisado'): ?>
                    <i class="fas fa-check-circle me-1"></i>Revisado
                  <?php elseif ($rep['Estado']=='Descartado'): ?>
                    <i class="fas fa-times-circle me-1"></i>Descartado
                  <?php else: ?>
                    <i class="fas fa-clock me-1"></i>Pendiente
                  <?php endif; ?>
                </span>
              </div>
            </div>
            
            <div style="text-align: right; white-space: nowrap;">
              <?php if ($rep['Estado'] !== 'Revisado'): ?>
                <form method="post" action="<?= BASE_URL ?>?c=Admin&a=resenaMarcarReporte" style="display: inline-block; margin-bottom: 6px;">
                  <input type="hidden" name="id_reporte" value="<?= (int)$rep['ID_Reporte'] ?>">
                  <input type="hidden" name="accion" value="revisado">
                  <button type="submit" class="btn btn-sm btn-success" title="Marcar como revisado">
                    <i class="fas fa-check me-1"></i>Revisado
                  </button>
                </form>
              <?php endif; ?>
              <?php if ($rep['Estado'] !== 'Descartado'): ?>
                <form method="post" action="<?= BASE_URL ?>?c=Admin&a=resenaMarcarReporte" style="display: inline-block;">
                  <input type="hidden" name="id_reporte" value="<?= (int)$rep['ID_Reporte'] ?>">
                  <input type="hidden" name="accion" value="descartar">
                  <button type="submit" class="btn btn-sm btn-outline-secondary" title="Descartar reporte">
                    <i class="fas fa-times me-1"></i>Descartar
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>