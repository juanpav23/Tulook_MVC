<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck me-2"></i>Seguimiento de Pedidos Enviados</h2>
        <div>
            <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- Alertas de pedidos atrasados -->
    <?php if (!empty($pedidosAtrasados)): ?>
        <div class="alert alert-danger mb-4">
            <h5><i class="fas fa-exclamation-triangle me-2"></i>Pedidos Atrasados</h5>
            <p>Los siguientes pedidos llevan más de 3 días en estado "Enviado" sin ser entregados:</p>
            <ul class="mb-0">
                <?php foreach ($pedidosAtrasados as $atrasado): ?>
                    <li>
                        <strong>Pedido #<?= $atrasado['ID_Factura'] ?></strong> - 
                        <?= htmlspecialchars($atrasado['Nombre'] . ' ' . $atrasado['Apellido']) ?> -
                        Enviado el <?= date('d/m/Y', strtotime($atrasado['Fecha_Envio'])) ?> 
                        (<span class="text-danger"><?= $atrasado['dias_transcurridos'] ?> días</span>)
                        <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $atrasado['ID_Factura'] ?>" 
                           class="btn btn-sm btn-outline-danger ms-2">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Tabla de Pedidos Enviados -->
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0"><i class="fas fa-truck-loading me-2"></i>Pedidos en Tránsito</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pedidos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay pedidos enviados</h5>
                    <p class="text-muted">Todos los pedidos han sido entregados o aún no han sido enviados.</p>
                    <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i> Ver todos los pedidos
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha Envío</th>
                                <th>Días</th>
                                <th>Guía/Transportadora</th>
                                <th>Estado</th>
                                <th>Notas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): 
                                $diasTranscurridos = !empty($pedido['Fecha_Envio']) ? 
                                    floor((time() - strtotime($pedido['Fecha_Envio'])) / (60 * 60 * 24)) : 0;
                                $esAtrasado = $diasTranscurridos > 3;
                            ?>
                                <tr class="<?= $esAtrasado ? 'table-danger' : '' ?>">
                                    <td>
                                        <strong>#<?= $pedido['ID_Factura'] ?></strong>
                                        <br>
                                        <small class="text-muted"><?= $pedido['Codigo_Acceso'] ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($pedido['Correo']) ?></small>
                                    </td>
                                    <td>
                                        <?= !empty($pedido['Fecha_Envio']) ? date('d/m/Y', strtotime($pedido['Fecha_Envio'])) : 'N/A' ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= !empty($pedido['NombreEnvio']) ? 'Por: ' . $pedido['NombreEnvio'] : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $esAtrasado ? 'danger' : 'info' ?>">
                                            <?= $diasTranscurridos ?> días
                                        </span>
                                        <?php if ($esAtrasado): ?>
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> Atrasado
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pedido['Numero_Guia'])): ?>
                                            <strong>Guía:</strong> <?= $pedido['Numero_Guia'] ?>
                                            <br>
                                            <?php if (!empty($pedido['Transportadora'])): ?>
                                                <strong>Transp:</strong> <?= $pedido['Transportadora'] ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin información</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $getEstadoBadge($pedido['Estado']) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pedido['Notas_Envio'])): ?>
                                            <small><?= htmlspecialchars(substr($pedido['Notas_Envio'], 0, 50)) ?>...</small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin notas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($pedido['Estado'] === 'Enviado'): ?>
                                                <button type="button" class="btn btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEntregado<?= $pedido['ID_Factura'] ?>"
                                                        title="Marcar como entregado">
                                                    <i class="fas fa-box-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-warning"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalRetrasado<?= $pedido['ID_Factura'] ?>"
                                                        title="Marcar como retrasado">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal para marcar como entregado -->
                                <div class="modal fade" id="modalEntregado<?= $pedido['ID_Factura'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-box-check me-2"></i>Marcar como Entregado
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEntregado" method="post">
                                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                                <div class="modal-body">
                                                    <p>¿Confirmar entrega del pedido <strong>#<?= $pedido['ID_Factura'] ?></strong>?</p>
                                                    <div class="mb-3">
                                                        <label for="descripcion<?= $pedido['ID_Factura'] ?>" class="form-label">
                                                            Descripción de la entrega:
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="descripcion<?= $pedido['ID_Factura'] ?>" 
                                                                  name="Descripcion" 
                                                                  rows="3"
                                                                  placeholder="Ej: Producto entregado satisfactoriamente, cliente confirmó recepción, etc."></textarea>
                                                        <div class="form-text">Esta descripción quedará registrada en el historial.</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-check me-1"></i> Confirmar Entrega
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal para marcar como retrasado -->
                                <div class="modal fade" id="modalRetrasado<?= $pedido['ID_Factura'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-clock me-2"></i>Marcar como Retrasado
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post">
                                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                                <input type="hidden" name="Estado" value="Retrasado">
                                                <div class="modal-body">
                                                    <p>¿Marcar pedido <strong>#<?= $pedido['ID_Factura'] ?></strong> como retrasado?</p>
                                                    <div class="mb-3">
                                                        <label for="motivoRetraso<?= $pedido['ID_Factura'] ?>" class="form-label">
                                                            Motivo del retraso:
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="motivoRetraso<?= $pedido['ID_Factura'] ?>" 
                                                                  name="Descripcion" 
                                                                  rows="3" 
                                                                  required
                                                                  placeholder="Ej: Problemas con la transportadora, mal clima, dirección incorrecta, etc."></textarea>
                                                        <div class="form-text">Esta información quedará registrada en el historial.</div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-warning">
                                                        <i class="fas fa-clock me-1"></i> Marcar como Retrasado
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>