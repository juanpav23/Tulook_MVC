<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - TuLook</title>
    <!-- Bootstrap 5 CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
        rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" 
        rel="stylesheet">
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
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .profile-avatar i {
            font-size: 2.5rem;
            color: var(--primary-color);
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
            text-align: center;
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
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-logout:hover {
            background: #c82333;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--accent-color);
        }
        
        .stats-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            background: white;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .stats-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .header-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-end;
        }
        
        .header-buttons .btn {
            width: 200px;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem;
                text-align: center;
            }
            
            .profile-avatar {
                margin: 0 auto 1rem;
            }
            
            .header-buttons {
                align-items: center;
                margin-top: 1rem;
            }
            
            .header-buttons .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4 profile-container">
        <!-- Encabezado mejorado -->
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="ms-3">
                            <h2 class="mb-1"><?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?></h2>
                            <p class="mb-0 opacity-75">Miembro desde <?= date('Y') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="header-buttons">
                        <a href="<?= BASE_URL ?>?c=Tienda&a=index" class="btn btn-light btn-custom">
                            <i class="fas fa-store"></i>
                            <span>Volver a la Tienda</span>
                        </a>
                        <form method="POST" action="<?= BASE_URL ?>?c=Usuario&a=logout" class="d-inline">
                            <button type="submit" class="btn btn-logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del usuario (sin valoración promedio) -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stats-number">5</div>
                    <div class="stats-label">Pedidos Realizados</div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stats-number">12</div>
                    <div class="stats-label">Productos Favoritos</div>
                </div>
            </div>
        </div>

        <!-- Tarjeta del Perfil mejorada -->
        <div class="card profile-card">
            <div class="card-header card-header-custom">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Información Personal</h5>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6 info-item">
                        <div class="info-label">Nombre</div>
                        <div class="info-value">
                            <?= htmlspecialchars($usuario['Nombre']) ?>
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Apellido</div>
                        <div class="info-value">
                            <?= htmlspecialchars($usuario['Apellido']) ?>
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Correo Electrónico</div>
                        <div class="info-value">
                            <?= htmlspecialchars($usuario['Correo']) ?>
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Celular</div>
                        <div class="info-value">
                            <?= htmlspecialchars($usuario['Celular']) ?>
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Tipo de Documento</div>
                        <div class="info-value">
                            <?= htmlspecialchars($usuario['Documento']) ?>
                        </div>
                    </div>

                    <div class="col-md-6 info-item">
                        <div class="info-label">Número de Documento</div>
                        <div class="info-value">
                            <?= htmlspecialchars($usuario['N_Documento']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones adicionales -->
        <div class="d-flex justify-content-between mt-4">
            <a href="#" class="btn btn-outline-primary">
                <i class="fas fa-history me-1"></i> Historial de Pedidos
            </a>
            <a href="<?php echo BASE_URL . '?c=Favorito&a=index'; ?>" class="btn btn-outline-primary">
                <i class="fas fa-heart me-1"></i> Mis Favoritos
            </a>
            <a href="<?= BASE_URL ?>?c=Usuario&a=cambiarContrasena" class="btn btn-outline-primary">
                <i class="fas fa-lock me-1"></i> Cambiar Contraseña
            </a>
        </div>

        <!-- Información adicional mejorada -->
        <div class="alert alert-info alert-custom mt-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle me-3 fa-lg"></i>
                <div>
                    <h6 class="alert-heading mb-1">Información importante</h6>
                    Puedes usar tu cuenta para realizar compras en la tienda. Por razones de seguridad, tus datos personales no pueden editarse desde esta sección. Si necesitas actualizar tu información, contacta con nuestro servicio de atención al cliente.
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Incluir el chatbot -->
    <?php include 'views/chatbot/chat.php'; ?>

</body>
</html>