<?php
// Vista de favoritos
if (!isset($favoritos) || !is_array($favoritos)) $favoritos = [];
if (!isset($categorias)) $categorias = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Favoritos - TuLook</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .card {
            transition: transform 0.2s ease;
            border: none;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .btn-favorito {
            transition: all 0.3s ease;
        }
        .favorito-removing {
            opacity: 0.6;
            pointer-events: none;
        }
        .product-image {
            height: 220px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php include "views/layout/nav.php"; ?>

<!-- Contenido -->
<?php if (empty($favoritos)): ?>
    <div class='container my-5'>
        <div class='card shadow-sm border-0'>
            <div class='card-body py-5 text-center'>
                <i class='fas fa-heart-broken fa-3x text-muted mb-3'></i>
                <h3 class='text-muted'>No tienes productos en favoritos</h3>
                <p class='text-muted mb-4'>Agrega algunos productos que te gusten para verlos aquí</p>
                <a href='<?= BASE_URL ?>' class='btn btn-primary btn-lg'>
                    <i class='fas fa-store'></i> Ir a la tienda
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-heart text-danger me-2"></i>Mis Favoritos
            </h2>
            <span class="badge bg-primary fs-6" id="contador-favoritos"><?= count($favoritos) ?> producto(s)</span>
        </div>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <div id="favoritos-container" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-2">
            <?php foreach ($favoritos as $f): ?>
                <?php
                // Resolver nombre y foto correctamente
                $nombre = $f['N_Articulo'] ?? $f['Nombre_Producto'] ?? $f['Nombre'] ?? 'Producto sin nombre';
                $foto = $f['Foto'] ?? $f['FotoArticulo'] ?? $f['FotoProducto'] ?? 'assets/img/no-image.png';
                
                // Asegurar que la ruta de la imagen sea correcta
                if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
                    $foto = 'ImgProducto/' . ltrim($foto, '/');
                }
                $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
                
                // Resolver precio
                $precio = $f['Precio_Final'] ?? $f['Precio_Base'] ?? $f['Precio'] ?? 0;

                // Determinar tipo y ID
                $esProducto = !empty($f['ID_Producto']);
                $id = $esProducto ? $f['ID_Producto'] : $f['ID_Articulo'];
                $tipo = $esProducto ? 'producto' : 'articulo';
                
                $link_ver = BASE_URL . "?c=Producto&a=ver&id=" . $id;
                ?>

                <div class="col" id="favorito-<?= $id ?>">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <img src="<?= $fotoUrl; ?>" 
                                class="card-img-top product-image" 
                                alt="<?= htmlspecialchars($nombre); ?>"
                                onerror="this.src='<?= BASE_URL ?>assets/img/no-image.png'">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-danger">
                                    <i class="fas fa-heart"></i>
                                </span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title fw-bold"><?= htmlspecialchars($nombre); ?></h6>
                            
                            <?php if ($precio > 0): ?>
                            <p class="text-success fw-bold fs-5 mb-2">$<?= number_format($precio, 0, ',', '.'); ?></p>
                            <?php endif; ?>

                            <!-- Información adicional si está disponible -->
                            <?php if (isset($f['N_Talla']) || isset($f['N_Color'])): ?>
                                <div class="mb-2">
                                    <?php if (isset($f['N_Talla'])): ?>
                                        <small class="text-muted me-2">
                                            <i class="fas fa-ruler"></i> <?= htmlspecialchars($f['N_Talla']) ?>
                                        </small>
                                    <?php endif; ?>
                                    <?php if (isset($f['N_Color'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-palette"></i> <?= htmlspecialchars($f['N_Color']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <button type="button" 
                                            class="btn btn-danger btn-sm btn-favorito"
                                            data-id="<?= $id ?>"
                                            data-tipo="<?= $tipo ?>"
                                            data-action="remove">
                                        <i class="fas fa-heart me-1"></i> 
                                        <span class="btn-text">Quitar de Favoritos</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                    <a href="<?= $link_ver ?>" class="btn btn-outline-dark btn-sm">
                                        <i class="fas fa-eye me-1"></i> Ver Detalles
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Agregado el <?= date('d/m/Y', strtotime($f['Fecha'] ?? 'now')) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
            <div>
                <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Seguir Comprando
                </a>
            </div>
            <div class="text-muted" id="contador-texto">
                Mostrando <?= count($favoritos) ?> de <?= count($favoritos) ?> favoritos
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript para manejar favoritos con AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoritosContainer = document.getElementById('favoritos-container');
    const contadorFavoritos = document.getElementById('contador-favoritos');
    const contadorTexto = document.getElementById('contador-texto');

    if (favoritosContainer) {
        favoritosContainer.addEventListener('click', function(e) {
            if (e.target.closest('.btn-favorito')) {
                const btn = e.target.closest('.btn-favorito');
                const id = btn.dataset.id;
                const tipo = btn.dataset.tipo;
                const action = btn.dataset.action;
                
                // Mostrar loading
                const spinner = btn.querySelector('.spinner-border');
                const btnText = btn.querySelector('.btn-text');
                btn.classList.add('favorito-removing');
                spinner.classList.remove('d-none');
                btnText.textContent = 'Eliminando...';

                // Preparar datos para enviar
                const formData = new FormData();
                if (tipo === 'producto') {
                    formData.append('id_producto', id);
                } else {
                    formData.append('id_articulo', id);
                }

                // Enviar petición AJAX
                fetch('<?= BASE_URL ?>?c=Favorito&a=toggleAjax', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Eliminar el producto del DOM
                        const productoElement = document.getElementById(`favorito-${id}`);
                        if (productoElement) {
                            productoElement.style.opacity = '0.5';
                            setTimeout(() => {
                                productoElement.remove();
                                
                                // Actualizar contadores
                                const productosRestantes = document.querySelectorAll('#favoritos-container .col').length;
                                contadorFavoritos.textContent = `${productosRestantes} producto(s)`;
                                contadorTexto.textContent = `Mostrando ${productosRestantes} de ${productosRestantes} favoritos`;
                                
                                // Si no quedan productos, recargar la página para mostrar mensaje vacío
                                if (productosRestantes === 0) {
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                }
                            }, 500);
                        }

                        // Mostrar mensaje de confirmación
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado de favoritos',
                            text: 'El producto se eliminó de tus favoritos',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        // Disparar evento personalizado para notificar a ver.php
                        const event = new CustomEvent('favoritoRemovido', {
                            detail: { id, tipo }
                        });
                        window.dispatchEvent(event);

                    } else {
                        throw new Error(data.message || 'Error al eliminar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar de favoritos',
                        timer: 2000
                    });
                })
                .finally(() => {
                    // Quitar loading
                    btn.classList.remove('favorito-removing');
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Quitar de Favoritos';
                });
            }
        });
    }

    // Escuchar eventos de favoritos removidos desde otras páginas
    window.addEventListener('favoritoRemovido', function(e) {
        console.log('Favorito removido desde otra página:', e.detail);
        // Aquí podrías actualizar la interfaz si es necesario
    });
});

// Función para notificar a ver.php cuando se elimina un favorito
function notificarFavoritoRemovido(id, tipo) {
    localStorage.setItem('ultimoFavoritoRemovido', JSON.stringify({
        id: id,
        tipo: tipo,
        timestamp: Date.now()
    }));
}
</script>

</body>
</html>


