<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - TuLook</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2f3e53ff;
            --secondary-color: #f8f9fa;
            --accent-color: #e83e8c;
            --text-dark: #343a40;
            --text-light: #6c757d;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            color: var(--text-dark);
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), #1F2937);
            color: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .profile-header::after {
            content: "";
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .profile-card {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .profile-card:hover {
            transform: translateY(-5px);
        }

        .card-header-custom {
            background: linear-gradient(to right, var(--primary-color), #1F2937);
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: none;
        }

        .info-item {
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-weight: 500;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .info-value {
            font-weight: 500;
            color: var(--text-dark);
            padding: 0.75rem 1rem;
            background-color: var(--secondary-color);
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .btn-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-custom:hover {
            background: #1F2937;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-logout {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 180px;
        }

        .btn-logout:hover {
            background: #c82333;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

</head>

<body>

<div class="container mt-4 profile-container">

    <!-- ENCABEZADO -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">

                <h2 class="mb-1"><?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?></h2>
                <p class="mb-0 opacity-75">Miembro desde <?= date('Y') ?></p>

            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">

                <div class="header-buttons">

                    <a href="<?= BASE_URL ?>?c=Tienda&a=index" class="btn btn-light btn-custom">
                        <i class="fas fa-store"></i> Volver a la Tienda
                    </a>

                    <form method="POST" action="<?= BASE_URL ?>?c=Usuario&a=logout">
                        <button class="btn btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stats-number">5</div>
                <div class="stats-label">Pedidos Realizados</div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-heart"></i></div>
                <div class="stats-number">12</div>
                <div class="stats-label">Productos Favoritos</div>
            </div>
        </div>
    </div>

    <!-- INFORMACIÓN PERSONAL -->
    <div class="card profile-card">
        <div class="card-header card-header-custom">
            <h5><i class="fas fa-id-card me-2"></i>Información Personal</h5>
        </div>

        <div class="card-body">
            <div class="row g-4">

                <div class="col-md-6 info-item">
                    <div class="info-label">Nombre</div>
                    <div class="info-value"><?= htmlspecialchars($usuario['Nombre']) ?></div>
                </div>

                <div class="col-md-6 info-item">
                    <div class="info-label">Apellido</div>
                    <div class="info-value"><?= htmlspecialchars($usuario['Apellido']) ?></div>
                </div>

                <div class="col-md-6 info-item">
                    <div class="info-label">Correo</div>
                    <div class="info-value"><?= htmlspecialchars($usuario['Correo']) ?></div>
                </div>

                <div class="col-md-6 info-item">
                    <div class="info-label">Celular</div>
                    <div class="info-value"><?= htmlspecialchars($usuario['Celular']) ?></div>
                </div>

                <div class="col-md-6 info-item">
                    <div class="info-label">Documento</div>
                    <div class="info-value"><?= htmlspecialchars($usuario['Documento']) ?></div>
                </div>

                <div class="col-md-6 info-item">
                    <div class="info-label">Número</div>
                    <div class="info-value"><?= htmlspecialchars($usuario['N_Documento']) ?></div>
                </div>

            </div>
        </div>
    </div>

    <!-- =============================
         SECCIÓN: MIS DIRECCIONES
    ============================== -->

    <?php 
        require_once "models/Direccion.php";
        $dirModel = new Direccion($this->db);
        $direcciones = $dirModel->obtenerDireccionesUsuario($_SESSION['ID_Usuario']);
    ?>

    <div class="card profile-card mt-4">
        <div class="card-header card-header-custom d-flex justify-content-between">
            <h5 class="mb-0"><i class="fas fa-location-dot me-2"></i>Mis Direcciones</h5>

            <button class="btn btn-custom btn-sm" data-bs-toggle="modal" data-bs-target="#modalDireccion">
                <i class="fas fa-plus"></i> Nueva Dirección
            </button>
        </div>

        <div class="card-body">

            <?php if (empty($direcciones)): ?>
                <div class="alert alert-warning">No tienes direcciones guardadas.</div>
            <?php else: ?>

                <?php foreach ($direcciones as $dir): ?>
                    <div class="border rounded p-3 mb-3 bg-light">

                        <div class="d-flex justify-content-between">

                            <div>
                                <strong><?= $dir["Direccion"] ?></strong><br>
                                <small><?= $dir["Ciudad"] ?> - <?= $dir["Departamento"] ?></small><br>
                                <small>Código Postal: <?= $dir["CodigoPostal"] ?></small><br>

                                <?php if ($dir["Predeterminada"] == 1): ?>
                                    <span class="badge bg-success mt-2">
                                        <i class="fas fa-check-circle"></i> Predeterminada
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="text-end">

                                <!-- Editar -->
                                <button 
                                    class="btn btn-outline-primary btn-sm mb-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDireccion"
                                    data-id="<?= $dir['ID_Direccion'] ?>"
                                    data-direccion="<?= $dir['Direccion'] ?>"
                                    data-ciudad="<?= $dir['Ciudad'] ?>"
                                    data-departamento="<?= $dir['Departamento'] ?>"
                                    data-codigo="<?= $dir['CodigoPostal'] ?>"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Eliminar -->
                                <a href="<?= BASE_URL ?>?c=Usuario&a=eliminarDireccion&id=<?= $dir['ID_Direccion'] ?>"
                                   class="btn btn-outline-danger btn-sm mb-2"
                                   onclick="return confirm('¿Eliminar esta dirección?')">
                                    <i class="fas fa-trash"></i>
                                </a>

                                <!-- Marcar predeterminada -->
                                <?php if (!$dir["Predeterminada"]): ?>
                                    <a href="<?= BASE_URL ?>?c=Usuario&a=predeterminada&id=<?= $dir['ID_Direccion'] ?>"
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>

    <!-- =============================
         ACCIONES ADICIONALES
    ================================ -->
    <div class="d-flex justify-content-between mt-4">
        <a href="#" class="btn btn-outline-primary"><i class="fas fa-history me-1"></i> Historial de Pedidos</a>

        <a href="<?= BASE_URL ?>?c=Favorito&a=index" class="btn btn-outline-primary">
            <i class="fas fa-heart me-1"></i> Mis Favoritos
        </a>

        <a href="<?= BASE_URL ?>?c=Usuario&a=cambiarContrasena" class="btn btn-outline-primary">
            <i class="fas fa-lock me-1"></i> Cambiar Contraseña
        </a>
    </div>

    <!-- INFORMACIÓN IMPORTANTE -->
    <div class="alert alert-info alert-custom mt-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-3 fa-lg"></i>

            <div>
                <h6 class="alert-heading mb-1">Información importante</h6>
                Tus datos personales no pueden editarse desde esta sección.  
                Para actualizar información, contacta con soporte.
            </div>
        </div>
    </div>

</div>


<!-- ==================================
     MODAL AGREGAR / EDITAR DIRECCIÓN
==================================== -->

<div class="modal fade" id="modalDireccion" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="<?= BASE_URL ?>?c=Usuario&a=guardarDireccion" class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Guardar Dirección</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

            <input type="hidden" name="ID_Direccion" id="dir_id">

            <label>Dirección</label>
            <input type="text" name="Direccion" id="dir_direccion" class="form-control mb-3" required>

            <label>Ciudad</label>
            <input type="text" name="Ciudad" id="dir_ciudad" class="form-control mb-3" required>

            <label>Departamento</label>
            <input type="text" name="Departamento" id="dir_departamento" class="form-control mb-3" required>

            <label>Código Postal</label>
            <input type="text" name="CodigoPostal" id="dir_codigo" class="form-control" required>

        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-custom">Guardar</button>
        </div>

    </form>
  </div>
</div>

<!-- Script para rellenar modal -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalDireccion");

    modal.addEventListener("show.bs.modal", event => {
        let btn = event.relatedTarget;

        document.getElementById("dir_id").value = btn.getAttribute("data-id") || "";
        document.getElementById("dir_direccion").value = btn.getAttribute("data-direccion") || "";
        document.getElementById("dir_ciudad").value = btn.getAttribute("data-ciudad") || "";
        document.getElementById("dir_departamento").value = btn.getAttribute("data-departamento") || "";
        document.getElementById("dir_codigo").value = btn.getAttribute("data-codigo") || "";
    });
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'views/chatbot/chat.php'; ?>

</body>
</html>
