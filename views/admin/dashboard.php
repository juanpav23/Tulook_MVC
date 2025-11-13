<?php
// =============================
// VIEWS/ADMIN/DASHBOARD.PHP
// =============================
?>
<div class="container mt-5">
    <h2 class="mb-4">Panel de Administración</h2>

    <div class="alert alert-secondary">
        <strong>Bienvenido, <?php echo htmlspecialchars($_SESSION['Nombre_Completo'] ?? 'Administrador'); ?> 👋</strong>
    </div>

    <div class="row g-4">
        <!-- Usuarios -->
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Usuarios registrados</h5>
                    <h3><?php echo $counts['usuarios'] ?? 0; ?></h3>
                </div>
            </div>
        </div>

        <!-- Artículos -->
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <i class="fas fa-box-open fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Artículos publicados</h5>
                    <h3><?php echo $counts['articulos'] ?? 0; ?></h3>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <i class="fas fa-tags fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Productos</h5>
                    <h3><?php echo $counts['productos'] ?? 0; ?></h3>
                </div>
            </div>
        </div>

        <!-- Ventas -->
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0">
                <div class="card-body">
                    <i class="fas fa-shopping-cart fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Ventas registradas</h5>
                    <h3><?php echo $counts['ventas'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <div class="text-center">
        <a href="<?php echo BASE_URL; ?>?c=Admin&a=productos" class="btn btn-primary me-2">
            <i class="fas fa-box"></i> Gestionar productos
        </a>
        <a href="<?php echo BASE_URL; ?>?c=Usuario&a=logout" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
    </div>
</div>
</body>
</html>

