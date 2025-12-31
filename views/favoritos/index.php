<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Mis Favoritos - TuLook</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { padding-top: 70px; background: #f8f9fa; }
        .product-card { transition: all 0.3s ease; border: none; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .product-image { height: 200px; object-fit: cover; }
        .price { color: #e63946; font-weight: 700; }
        .favorito-actions { display: flex; gap: 10px; }
        .empty-favorites { text-align: center; padding: 80px 20px; }
        .empty-favorites i { font-size: 4rem; color: #dee2e6; margin-bottom: 20px; }
    </style>
</head>
<body>

<?php include "views/layout/nav.php"; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">
                <i class="fas fa-heart text-danger me-2"></i>Mis Favoritos
                <?php if ($totalFavoritos > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $totalFavoritos; ?></span>
                <?php endif; ?>
            </h1>
            <p class="text-muted">Productos que has guardado para ver más tarde</p>
        </div>
    </div>

    <?php if (empty($favoritos)): ?>
        <div class="empty-favorites">
            <i class="far fa-heart"></i>
            <h4 class="mb-3">No tienes favoritos aún</h4>
            <p class="text-muted mb-4">Explora nuestros productos y agrega tus favoritos haciendo clic en el corazón ❤️</p>
            <a href="<?php echo BASE_URL; ?>?c=Producto&a=index" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i>Explorar productos
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($favoritos as $favorito): 
                $foto = $favorito['Foto'] ?? '';
                if (!empty($foto)) {
                    if (strpos($foto, 'http') === 0) {
                        $fotoUrl = $foto;
                    } elseif (strpos($foto, 'ImgProducto/') === 0) {
                        $fotoUrl = BASE_URL . $foto;
                    } else {
                        $fotoUrl = BASE_URL . 'ImgProducto/' . ltrim($foto, '/');
                    }
                } else {
                    $fotoUrl = BASE_URL . 'assets/img/placeholder.png';
                }
            ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card product-card h-100">
                        <img src="<?php echo $fotoUrl; ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo htmlspecialchars($favorito['N_Articulo']); ?>"
                             onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.png'">
                        
                        <div class="card-body">
                            <h5 class="card-title h6"><?php echo htmlspecialchars($favorito['N_Articulo']); ?></h5>
                            
                            <p class="price mb-2">$<?php echo number_format($favorito['Precio'], 0, ',', '.'); ?></p>
                            
                            <?php if ($favorito['Stock'] > 0): ?>
                                <p class="text-success small mb-2">
                                    <i class="fas fa-check-circle me-1"></i>Disponible (<?php echo $favorito['Stock']; ?>)
                                </p>
                            <?php else: ?>
                                <p class="text-danger small mb-2">
                                    <i class="fas fa-times-circle me-1"></i>Agotado
                                </p>
                            <?php endif; ?>
                            
                            <p class="text-muted small mb-2">
                                <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($favorito['N_Categoria']); ?>
                            </p>
                        </div>
                        
                        <div class="card-footer bg-white border-0">
                            <div class="favorito-actions">
                                <a href="<?php echo BASE_URL; ?>?c=Producto&a=ver&id=<?php echo $favorito['ID_Articulo']; ?>" 
                                   class="btn btn-outline-primary btn-sm flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </a>
                                
                                <button class="btn btn-outline-danger btn-sm remove-favorite"
                                        data-articulo-id="<?php echo $favorito['ID_Articulo']; ?>"
                                        title="Eliminar de favoritos">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/favoritos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.remove-favorite');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const articuloId = this.getAttribute('data-articulo-id');
            const card = this.closest('.col-md-4, .col-lg-3');
            
            if (!articuloId || isNaN(parseInt(articuloId))) {
                Swal.fire({
                    title: 'Error',
                    text: 'ID de artículo inválido',
                    icon: 'error'
                });
                return;
            }
            
            Swal.fire({
                title: '¿Eliminar de favoritos?',
                text: 'Este producto se eliminará de tu lista de favoritos',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = `<?php echo BASE_URL; ?>?c=Favorito&a=toggleFavorito&id_articulo=${articuloId}`;
                    
                    fetch(url, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (card) card.remove();
                            
                            // Actualizar contador
                            const counter = document.getElementById('favoritos-count');
                            if (counter) {
                                if (data.totalFavoritos > 0) {
                                    counter.textContent = data.totalFavoritos;
                                    counter.style.display = 'block';
                                } else {
                                    counter.style.display = 'none';
                                }
                            }
                            
                            Swal.fire({
                                title: 'Eliminado',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            if (document.querySelectorAll('.col-md-4.mb-4, .col-lg-3.mb-4').length === 0) {
                                location.reload();
                            }
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error de conexión',
                            icon: 'error'
                        });
                    });
                }
            });
        });
    });
});
</script>

</body>
</html>