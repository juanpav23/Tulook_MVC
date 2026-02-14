<?php
// Verificación más robusta al inicio del archivo
if (isset($_SESSION['usuario'])) {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: " . BASE_URL);
    exit;
}

// Verificar si venimos de aceptar términos
$mostrarRegistroDirecto = false;
if (isset($_GET['registro']) && $_GET['registro'] == 1 && isset($_GET['terminos']) && $_GET['terminos'] == 'aceptados') {
    $mostrarRegistroDirecto = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>TuLook - Login</title>

    <!-- META TAGS PARA PREVENIR CACHE -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">    

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Basta.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        /* ============================================
           ESTILOS CORREGIDOS PARA MODALES
           ============================================ */
        
        /* Modal de motivo - Scroll corregido */
        .motivo-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            overflow-y: auto !important;
        }

        .motivo-modal-content {
            background-color: #fff;
            margin: 30px auto;
            padding: 25px;
            border-radius: 15px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            border: 2px solid #2f3e53;
            position: relative;
            font-family: 'Poppins', sans-serif;
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .motivo-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }

        .motivo-modal-title {
            color: #2f3e53;
            font-size: 22px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
        }

        .motivo-modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #666;
            cursor: pointer;
            transition: color 0.3s;
            padding: 0 10px;
        }

        .motivo-modal-close:hover {
            color: #2f3e53;
        }

        .motivo-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
        }

        .motivo-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
        }

        .motivo-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .motivo-label {
            font-weight: 600;
            color: #2f3e53;
            min-width: 130px;
            font-size: 14px;
        }

        .motivo-value {
            flex: 1;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
            word-break: break-word;
        }

        .motivo-alert {
            background: #e8ebf0;
            border-left: 4px solid #2A3448;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 13px;
            color: #2A3448;
        }

        .motivo-alert i {
            color: #2A3448;
            margin-right: 8px;
        }

        /* 🔴 NUEVO: Alerta para límite de solicitudes rechazadas */
        .motivo-alert-danger {
            background: #fff5f5;
            border-left: 6px solid #dc3545;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            font-size: 14px;
            color: #721c24;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .motivo-alert-danger i {
            color: #dc3545;
            margin-right: 12px;
            font-size: 24px;
        }

        .motivo-alert-danger strong {
            color: #dc3545;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }

        .motivo-alert-danger p {
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .motivo-alert-danger .contact-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .motivo-alert-danger .contact-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .motivo-loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        /* ============================================
           BOTÓN VER MOTIVO - MEJORADO Y CENTRADO
           ============================================ */
        .btn-ver-motivo {
            padding: 10px 20px;
            background: linear-gradient(145deg, #2f3e53, #1a2537);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 12px rgba(47, 62, 83, 0.3);
            margin: 5px auto;
            width: fit-content;
            min-width: 150px;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .btn-ver-motivo i {
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .btn-ver-motivo:hover {
            background: linear-gradient(145deg, #1a2537, #0f1a24);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(47, 62, 83, 0.4);
        }

        .btn-ver-motivo:hover i {
            transform: scale(1.1);
        }

        .btn-ver-motivo:active {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(47, 62, 83, 0.4);
        }

        .btn-ver-motivo:disabled {
            background: linear-gradient(145deg, #6c757d, #5a6268);
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-ver-motivo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        /* ============================================
           MODAL DE SOLICITUD DE REACTIVACIÓN - DISEÑO MEJORADO
           ============================================ */
        .reactivacion-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            overflow-y: auto;
            animation: modalBackdropFade 0.3s ease;
        }

        @keyframes modalBackdropFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .reactivacion-modal-content {
            background-color: #fff;
            margin: 40px auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            border: none;
            position: relative;
            animation: modalSlideUp 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
        }

        @keyframes modalSlideUp {
            from { 
                opacity: 0; 
                transform: translateY(50px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0);
            }
        }

        .reactivacion-modal-header {
            background: linear-gradient(135deg, #2f3e53 0%, #1a2537 100%);
            color: white;
            padding: 25px 30px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reactivacion-modal-icon {
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .reactivacion-modal-icon i {
            font-size: 30px;
            color: white;
        }

        .reactivacion-modal-title {
            flex: 1;
        }

        .reactivacion-modal-title h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            color: white;
        }

        .reactivacion-modal-title p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .reactivacion-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .reactivacion-modal-close:hover {
            background: rgba(255,255,255,0.2);
            transform: rotate(90deg);
        }

        .reactivacion-modal-body {
            padding: 30px;
        }

        .reactivacion-user-info {
            background: linear-gradient(to right, #f8f9fa, #ffffff);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .reactivacion-user-avatar {
            background: #2f3e53;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .reactivacion-user-details {
            flex: 1;
        }

        .reactivacion-user-details h4 {
            margin: 0;
            color: #2f3e53;
            font-size: 18px;
            font-weight: 600;
        }

        .reactivacion-user-details span {
            color: #6c757d;
            font-size: 14px;
            display: block;
            margin-top: 4px;
        }

        .reactivacion-form-group {
            margin-bottom: 25px;
        }

        .reactivacion-form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2f3e53;
            font-weight: 600;
            font-size: 15px;
        }

        .reactivacion-form-group label i {
            margin-right: 8px;
            color: #2f3e53;
        }

        .reactivacion-textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .reactivacion-textarea:focus {
            border-color: #2f3e53;
            background: white;
            box-shadow: 0 0 0 4px rgba(47, 62, 83, 0.1);
            outline: none;
        }

        .reactivacion-textarea::placeholder {
            color: #adb5bd;
            font-style: italic;
        }

        .reactivacion-counter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .reactivacion-counter-progress {
            flex: 1;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-right: 15px;
            overflow: hidden;
        }

        .reactivacion-progress-bar {
            height: 100%;
            width: 0%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .reactivacion-counter-text {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
            min-width: 120px;
            text-align: right;
        }

        .reactivacion-counter-text span {
            font-weight: 700;
            color: #2f3e53;
        }

        .reactivacion-tips {
            background: #cbd0db;
            border-left: 4px solid #3A4A6B;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .reactivacion-tips h5 {
            color: #000000;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reactivacion-tips ul {
            margin: 0;
            padding-left: 20px;
            color: #000000;
            font-size: 13px;
        }

        .reactivacion-tips li {
            margin-bottom: 5px;
        }

        .reactivacion-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .reactivacion-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .reactivacion-btn-cancel {
            background: white;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .reactivacion-btn-cancel:hover {
            background: #f8f9fa;
            color: #495057;
            border-color: #adb5bd;
        }

        .reactivacion-btn-primary {
            background: linear-gradient(145deg, #2f3e53, #1a2537);
            color: white;
            box-shadow: 0 4px 12px rgba(47, 62, 83, 0.2);
        }

        .reactivacion-btn-primary:hover:not(:disabled) {
            background: linear-gradient(145deg, #1a2537, #0f1a24);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(47, 62, 83, 0.3);
        }

        .reactivacion-btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-contacto-soporte {
            padding: 12px 16px;
            background: linear-gradient(145deg, #2A3448, #1e2735);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            margin-top: 15px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(42, 52, 72, 0.2);
        }

        .btn-contacto-soporte:hover {
            background: linear-gradient(145deg, #1e2735, #151c26);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(42, 52, 72, 0.3);
        }

        .btn-contacto-soporte:disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
            box-shadow: none;
        }

        .btn-loading {
            position: relative;
            color: transparent !important;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .contacto-confirm-modal {
            display: none;
            position: fixed;
            z-index: 11000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            overflow-y: auto;
        }

        .contacto-confirm-content {
            background-color: #fff;
            margin: 50px auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            border: none;
            position: relative;
            animation: modalSlideUp 0.3s ease;
            text-align: center;
        }

        .contacto-confirm-content i {
            font-size: 70px;
            color: #28a745;
            margin-bottom: 20px;
            display: block;
            text-align: center;
            animation: checkPop 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        @keyframes checkPop {
            from { 
                opacity: 0; 
                transform: scale(0.5);
            }
            to { 
                opacity: 1; 
                transform: scale(1);
            }
        }

        .contacto-confirm-content h3 {
            color: #2f3e53;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 700;
        }

        .contacto-confirm-content p {
            color: #333;
            margin-bottom: 25px;
            white-space: pre-line;
            text-align: left;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.6;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
        }

        .contacto-confirm-content button {
            padding: 12px 30px;
            background: linear-gradient(145deg, #2f3e53, #1a2537);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(47, 62, 83, 0.2);
        }

        .contacto-confirm-content button:hover {
            background: linear-gradient(145deg, #1a2537, #0f1a24);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(47, 62, 83, 0.3);
        }

        .solicitud-pendiente {
            background: #bfcce4;
            border-left: 4px solid #5B6E8F;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            color: #000000;
        }

        .solicitud-aprobada {
            background: #d4edda;
            border-left: 4px solid #1B202D;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            color: #155724;
        }

        .btn-iniciar-sesion {
            background: #1B202D;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-iniciar-sesion:hover {
            background: #2A3448;
            transform: translateY(-2px);
        }

        /* ============================================
           NUEVO: ESTILOS PARA TÉRMINOS Y CONDICIONES EN LOGIN
           ============================================ */
        .terminos-checkbox-wrapper {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            border-left: 4px solid #2f3e53;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .terminos-checkbox-wrapper:hover {
            border-color: #2f3e53;
            background: #ffffff !important;
            box-shadow: 0 4px 12px rgba(47, 62, 83, 0.1);
        }
        
        .terminos-checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #2f3e53;
            transform: scale(1.2);
            margin-right: 5px;
        }
        
        .terminos-checkbox-wrapper a {
            color: #2f3e53;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid #2f3e53;
            padding-bottom: 2px;
            transition: all 0.2s ease;
        }
        
        .terminos-checkbox-wrapper a:hover {
            background: #2f3e53;
            color: white !important;
            border-bottom-color: transparent !important;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        #terminos-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 10px;
            display: none;
            padding: 8px;
            background: #ffe6e6;
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .terminos-checkbox-wrapper {
                padding: 12px !important;
            }
            
            .terminos-checkbox-wrapper div[style*="flex"] {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
        }

        @media (max-width: 768px) {
            .motivo-modal-content,
            .reactivacion-modal-content,
            .contacto-confirm-content {
                margin: 20px auto;
                width: 95%;
            }
            
            .motivo-info {
                max-height: 250px;
            }
            
            .motivo-item {
                flex-direction: column;
            }
            
            .motivo-label {
                margin-bottom: 5px;
                min-width: auto;
            }
            
            .reactivacion-modal-header {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .reactivacion-user-info {
                flex-direction: column;
                text-align: center;
            }
            
            .reactivacion-modal-footer {
                flex-direction: column;
            }
            
            .reactivacion-btn {
                width: 100%;
                justify-content: center;
            }
            
            .contacto-confirm-content p {
                max-height: 250px;
            }
        }
    </style>
</head>
<body>

    <!-- Modal para mostrar el motivo de desactivación -->
    <div id="motivoModal" class="motivo-modal">
        <div class="motivo-modal-content">
            <div class="motivo-modal-header">
                <h3 class="motivo-modal-title">Cuenta Desactivada</h3>
                <button class="motivo-modal-close" onclick="cerrarMotivoModal()">&times;</button>
            </div>
            <div id="motivoContent">
                <div class="motivo-loading">
                    <i class="bi bi-hourglass-split" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Cargando información...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVO: Modal mejorado para solicitud de reactivación -->
    <div id="reactivacionModal" class="reactivacion-modal">
        <div class="reactivacion-modal-content">
            <div class="reactivacion-modal-header">
                <div class="reactivacion-modal-icon">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <div class="reactivacion-modal-title">
                    <h3>Solicitar Reactivación</h3>
                    <p>Tu solicitud será revisada por un administrador</p>
                </div>
                <button class="reactivacion-modal-close" onclick="cerrarReactivacionModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="reactivacion-modal-body">
                <div class="reactivacion-user-info" id="reactivacionUserInfo">
                    <!-- Se llenará dinámicamente con JavaScript -->
                </div>
                
                <div class="reactivacion-form-group">
                    <label>
                        <i class="bi bi-pencil-square"></i>
                        Motivo de reactivación
                    </label>
                    <textarea 
                        id="motivoReactivacion" 
                        class="reactivacion-textarea" 
                        rows="5" 
                        placeholder="Por favor, explica detalladamente por qué deseas reactivar tu cuenta. Ejemplos:&#10;• Necesito acceder a mis compras anteriores&#10;• He solucionado el problema que causó la desactivación&#10;• Deseo continuar utilizando los servicios de la plataforma"
                        maxlength="500"
                    ></textarea>
                    
                    <div class="reactivacion-counter">
                        <div class="reactivacion-counter-progress">
                            <div id="reactivacionProgressBar" class="reactivacion-progress-bar" style="width: 0%; background: linear-gradient(90deg, #2f3e53, #4a5f7a);"></div>
                        </div>
                        <div class="reactivacion-counter-text">
                            <span id="reactivacionCharCount">0</span> / 500 caracteres
                        </div>
                    </div>
                </div>
                
                <div class="reactivacion-tips">
                    <h5>
                        <i class="bi bi-lightbulb"></i>
                        Consejos para una solicitud exitosa
                    </h5>
                    <ul>
                        <li>Sé claro y específico sobre tu situación</li>
                        <li>Explica qué medidas has tomado para resolver el problema</li>
                        <li>Mínimo 20 caracteres - Máximo 500 caracteres</li>
                        <li>Tu solicitud será respondida en 24-48 horas</li>
                    </ul>
                </div>
            </div>
            
            <div class="reactivacion-modal-footer">
                <button class="reactivacion-btn reactivacion-btn-cancel" onclick="cerrarReactivacionModal()">
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </button>
                <button class="reactivacion-btn reactivacion-btn-primary" id="btnEnviarReactivacion" disabled>
                    <i class="bi bi-send-check"></i>
                    Enviar Solicitud
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de solicitud enviada -->
    <div id="contactoConfirmModal" class="contacto-confirm-modal">
        <div class="contacto-confirm-content">
            <i class="bi bi-check-circle-fill" style="color: #3A4A6B;"></i>
            <h3>Solicitud Enviada</h3>
            <div id="contactoConfirmMessage" style="white-space: pre-line; text-align: left; background: #f8f9fa; padding: 20px; border-radius: 12px; max-height: 300px; overflow-y: auto; border: 1px solid #e9ecef; margin-bottom: 20px;"></div>
            <button onclick="cerrarContactoConfirmModal()" style="background: linear-gradient(145deg, #2f3e53, #1a2537); color: white; border: none; padding: 12px 30px; border-radius: 50px; font-weight: 600; cursor: pointer; display: inline-block; margin: 0 auto; box-shadow: 0 4px 12px rgba(47,62,83,0.2);">Aceptar</button>
        </div>
    </div>

    <main>
        <div class="contenedor__todo">
            <div class="caja__trasera">
                <div class="caja__trasera-login">
                    <h3>¿Ya tienes una cuenta?</h3>
                    <p>Inicia sesión para entrar en la página</p>
                    <button id="btn_iniciar_sesion">Iniciar sesión</button>
                </div>
                <div class="caja__trasera_register">
                    <h3>¿Aún no tienes una cuenta?</h3>
                    <p>Regístrate para que puedas iniciar sesión</p>
                    <button id="btn__registrarse">Registrarse</button>
                </div>
            </div>

            <div class="contenedor__login_register">
                <!-- LOGIN -->
                <form action="<?php echo BASE_URL . '?c=Usuario&a=login'; ?>" method="POST" class="formulario__login" onsubmit="return validarLogin()">
                    <h2>Iniciar sesión</h2>
                    
                    <?php if (isset($_SESSION['error_message']) && (!isset($_SESSION['error_type']) || $_SESSION['error_type'] !== 'registro')): ?>
                        <div id="error-mensaje-login" class="error-mensaje" style="color:red; text-align:center; margin-bottom:15px; padding:15px; background:#ffe6e6; border-radius:8px; border-left:4px solid #dc3545;">
                            <?php 
                            echo $_SESSION['error_message'];
                            if (isset($_SESSION['error_details'])) {
                                echo '<ul style="text-align:left; margin:10px 0; padding-left:20px;">';
                                foreach ($_SESSION['error_details'] as $detail) {
                                    echo '<li>' . htmlspecialchars($detail) . '</li>';
                                }
                                echo '</ul>';
                                unset($_SESSION['error_details']);
                            }
                            unset($_SESSION['error_message']);
                            unset($_SESSION['error_type']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success_registro'])): ?>
                        <p id="success-mensaje-login" style="color:green; text-align:center; padding:15px; background:#e6ffe6; border-radius:8px; border-left:4px solid #28a745;"><?php echo $_SESSION['success_registro']; unset($_SESSION['success_registro']); ?></p>
                    <?php endif; ?>
                    
                    <input type="email" placeholder="Correo electrónico" name="Correo" id="login_correo" required maxlength="50">
                    <div class="password-input-container">
                        <input type="password" placeholder="Contraseña" name="Contrasena" id="login_contrasena" required minlength="12" maxlength="20">
                        <button type="button" class="password-toggle" onclick="togglePassword('login_contrasena')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="botones-form">
                        <button type="submit">Entrar</button>
                        <button type="button" onclick="limpiarFormularioLogin()">Limpiar</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="<?php echo BASE_URL; ?>?c=Usuario&a=olvidoContrasena" style="color: #2f3e53; text-decoration: none; font-size: 14px;">
                            <i class="bi bi-key me-1"></i> ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </form>

                <!-- REGISTRO -->
                <form action="<?php echo BASE_URL . '?c=Usuario&a=registrar'; ?>" method="POST" class="formulario__register" onsubmit="return validarRegistro()">
                    <h2>Registrarse</h2>

                    <?php if (isset($_SESSION['error_message']) && isset($_SESSION['error_type']) && $_SESSION['error_type'] === 'registro'): ?>
                        <div id="error-mensaje-registro" class="error-mensaje" style="color:red; text-align:left; margin-bottom:15px; padding:15px; background:#ffe6e6; border-radius:8px; border-left:4px solid #dc3545;">
                            <?php 
                            echo htmlspecialchars($_SESSION['error_message']);
                            if (isset($_SESSION['error_details'])) {
                                echo '<ul style="margin:10px 0; padding-left:20px; font-size:12px;">';
                                foreach ($_SESSION['error_details'] as $detail) {
                                    echo '<li>' . htmlspecialchars($detail) . '</li>';
                                }
                                echo '</ul>';
                                unset($_SESSION['error_details']);
                            }
                            unset($_SESSION['error_message']);
                            unset($_SESSION['error_type']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <input type="text" placeholder="Nombre completo" name="Nombre" id="reg_nombre" required minlength="2" maxlength="50" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}" title="Solo letras y espacios (2-50 caracteres)">
                    <input type="text" placeholder="Apellido completo" name="Apellido" id="reg_apellido" required minlength="2" maxlength="50" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}" title="Solo letras y espacios (2-50 caracteres)">

                    <select name="ID_TD" class="form-select" id="reg_tipo_documento" required>
                        <option value="" disabled selected>Seleccione el tipo de documento</option>
                        <?php foreach ($tipo_docs as $doc): ?>
                            <option value="<?php echo $doc['ID_TD']; ?>"><?php echo $doc['Documento']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" placeholder="Número de Documento" name="N_Documento" id="reg_documento" required pattern="[0-9]{7,12}" maxlength="12" title="Solo números (7-12 dígitos)">
                    <input type="text" placeholder="Celular" name="Celular" id="reg_celular" required pattern="[0-9]{10}" maxlength="10" title="Solo números (10 dígitos)">
                    <input type="email" placeholder="Correo Electrónico" name="Correo" id="reg_correo" required maxlength="100">
                    
                    <div class="password-input-container">
                        <input type="password" placeholder="Contraseña" name="Contrasena" id="reg_contrasena" required minlength="12" maxlength="20" title="Mínimo 12 caracteres, 2 números, 1 símbolo, 1 mayúscula y 1 minúscula">
                        <button type="button" class="password-toggle" onclick="togglePassword('reg_contrasena')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small style="display:block; text-align:left; margin:5px 0; color:#666; background:#f8f9fa; padding:8px; border-radius:4px; border-left:4px solid #2f3e53;">
                        <i class="bi bi-shield-check"></i> La contraseña debe tener mínimo 12 y máximo 20 caracteres, 2 números, 1 símbolo (!@#$), 1 mayúscula y 1 minúscula
                    </small>
                    
                    <div class="password-input-container">
                        <input type="password" placeholder="Confirmar Contraseña" name="Confirmar_Contrasena" id="reg_confirmar_contrasena" required maxlength="20">
                        <button type="button" class="password-toggle" onclick="togglePassword('reg_confirmar_contrasena')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <!-- ============================================
                         NUEVO: CHECKBOX DE TÉRMINOS Y CONDICIONES
                         ============================================ -->
                    <div class="terminos-checkbox-wrapper">
                        <div style="display: flex; align-items: flex-start; gap: 15px;">
                            <div style="position: relative; width: 24px; height: 24px; flex-shrink: 0; margin-top: 2px;">
                                <input type="checkbox" name="acepto_terminos" id="acepto_terminos" value="1" style="width: 20px; height: 20px; cursor: pointer; accent-color: #2f3e53;" <?php echo (isset($_GET['terminos']) && $_GET['terminos'] == 'aceptados') ? 'checked' : ''; ?> required>
                            </div>
                            <div style="flex: 1; font-size: 13px; line-height: 1.6; color: #333;">
                                <strong style="color: #2f3e53; font-size: 14px;">Acepto los Términos y Condiciones</strong>
                                <span style="display: block; margin-top: 5px;">
                                    He leído y acepto los 
                                    <a href="<?php echo BASE_URL; ?>?c=Usuario&a=terminos" target="_blank" style="color: #2f3e53; font-weight: 600; text-decoration: none; border-bottom: 2px solid #2f3e53; padding-bottom: 2px;">
                                        Términos y Condiciones
                                    </a> y la Política de Privacidad de TuLook.
                                </span>
                                <small style="display: block; margin-top: 8px; color: #6c757d;">
                                    <i class="bi bi-shield-check"></i> Al marcar esta casilla, aceptas el tratamiento de tus datos personales según nuestra política.
                                </small>
                            </div>
                        </div>
                        <div id="terminos-error">
                            <i class="bi bi-exclamation-triangle-fill"></i> Debes aceptar los Términos y Condiciones para registrarte.
                        </div>
                    </div>

                    <div class="botones-form">
                        <button type="submit">Registrarse</button>
                        <button type="button" onclick="limpiarFormularioRegistro()">Limpiar</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
        // ============================================
        // VARIABLES GLOBALES
        // ============================================
        let correoUsuarioInactivo = '';
        let botonContacto = null;

        // ============================================
        // FUNCIÓN PARA CERRAR SESIÓN Y REDIRIGIR
        // ============================================
        function cerrarSesionYRedirigir() {
            cerrarMotivoModal();
            
            fetch('<?php echo BASE_URL; ?>?c=Usuario&a=logout', {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache'
                }
            }).finally(() => {
                window.location.href = '<?php echo BASE_URL; ?>?c=Usuario&a=login&t=' + Date.now();
            });
        }

        // ============================================
        // FUNCIONES PARA MODALES
        // ============================================
        function mostrarMotivoDesactivacion(correo) {
            correoUsuarioInactivo = correo;
            const modal = document.getElementById('motivoModal');
            const motivoContent = document.getElementById('motivoContent');
            
            motivoContent.innerHTML = `
                <div class="motivo-loading">
                    <i class="bi bi-hourglass-split" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Cargando información...</p>
                </div>
            `;
            
            modal.style.display = 'block';
            obtenerMotivoDesactivacion(correo);
        }

        function cerrarMotivoModal() {
            document.getElementById('motivoModal').style.display = 'none';
        }

        function mostrarReactivacionModal(correo) {
            correoUsuarioInactivo = correo;
            const modal = document.getElementById('reactivacionModal');
            const userInfo = document.getElementById('reactivacionUserInfo');
            const textarea = document.getElementById('motivoReactivacion');
            const charCount = document.getElementById('reactivacionCharCount');
            const progressBar = document.getElementById('reactivacionProgressBar');
            const btnEnviar = document.getElementById('btnEnviarReactivacion');
            
            // Resetear el modal
            textarea.value = '';
            charCount.textContent = '0';
            progressBar.style.width = '0%';
            progressBar.style.background = 'linear-gradient(90deg, #2f3e53, #4a5f7a)';
            btnEnviar.disabled = true;
            
            // Obtener información del usuario
            fetch(`<?php echo BASE_URL; ?>?c=Usuario&a=getMotivoDesactivacion&correo=${encodeURIComponent(correo)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        userInfo.innerHTML = `
                            <div class="reactivacion-user-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="reactivacion-user-details">
                                <h4>${correo}</h4>
                                <span><i class="bi bi-calendar3"></i> Desactivado: ${data.fecha ? new Date(data.fecha).toLocaleDateString('es-ES') : 'Fecha no disponible'}</span>
                            </div>
                        `;
                    } else {
                        userInfo.innerHTML = `
                            <div class="reactivacion-user-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="reactivacion-user-details">
                                <h4>${correo}</h4>
                                <span><i class="bi bi-exclamation-triangle"></i> No se pudo cargar información adicional</span>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    userInfo.innerHTML = `
                        <div class="reactivacion-user-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <div class="reactivacion-user-details">
                            <h4>${correo}</h4>
                            <span><i class="bi bi-exclamation-triangle"></i> No se pudo cargar información adicional</span>
                        </div>
                    `;
                });
            
            modal.style.display = 'block';
        }

        function cerrarReactivacionModal() {
            document.getElementById('reactivacionModal').style.display = 'none';
        }

        function cerrarContactoConfirmModal() {
            document.getElementById('contactoConfirmModal').style.display = 'none';
        }

        // ============================================
        // VALIDACIÓN EN TIEMPO REAL DEL MOTIVO
        // ============================================
        function setupReactivacionValidation() {
            const textarea = document.getElementById('motivoReactivacion');
            const charCount = document.getElementById('reactivacionCharCount');
            const progressBar = document.getElementById('reactivacionProgressBar');
            const btnEnviar = document.getElementById('btnEnviarReactivacion');
            
            if (!textarea) return;
            
            textarea.addEventListener('input', function() {
                const texto = this.value;
                const longitud = texto.length;
                
                // Actualizar contador
                charCount.textContent = longitud;
                
                // Actualizar barra de progreso
                const porcentaje = Math.min((longitud / 500) * 100, 100);
                progressBar.style.width = `${porcentaje}%`;
                
                // Cambiar color de la barra según la longitud
                if (longitud < 20) {
                    progressBar.style.background = 'linear-gradient(90deg, #dc3545, #ff6b6b)';
                } else if (longitud >= 20 && longitud <= 450) {
                    progressBar.style.background = 'linear-gradient(90deg, #ffc107, #ffdb70)';
                } else {
                    progressBar.style.background = 'linear-gradient(90deg, #28a745, #5cb85c)';
                }
                
                // Validar y habilitar/deshabilitar botón
                const textoLimpio = texto.trim();
                if (textoLimpio.length >= 20 && textoLimpio.length <= 500) {
                    btnEnviar.disabled = false;
                } else {
                    btnEnviar.disabled = true;
                }
            });
            
            // Configurar el botón de enviar
            btnEnviar.addEventListener('click', function(e) {
                e.preventDefault();
                const motivo = textarea.value.trim();
                if (motivo.length >= 20 && motivo.length <= 500) {
                    enviarSolicitudReactivacion(correoUsuarioInactivo, motivo);
                }
            });
        }

        // ============================================
        // VERIFICAR ESTADO DE SOLICITUD
        // ============================================
        async function verificarEstadoSolicitud(correo) {
            try {
                const response = await fetch(`<?php echo BASE_URL; ?>?c=Usuario&a=verificarEstadoSolicitud&correo=${encodeURIComponent(correo)}`);
                const data = await response.json();
                
                if (data.success && data.tiene_solicitud) {
                    return {
                        tieneSolicitud: true,
                        estado: data.estado,
                        idSolicitud: data.id_solicitud || 'N/A'
                    };
                }
                return { tieneSolicitud: false };
            } catch (error) {
                console.error('Error verificando estado:', error);
                return { tieneSolicitud: false, error: true };
            }
        }

        // ============================================
        // OBTENER MOTIVO DE DESACTIVACIÓN
        // ============================================
        async function obtenerMotivoDesactivacion(correo) {
            const motivoContent = document.getElementById('motivoContent');
            correoUsuarioInactivo = correo;
            
            try {
                const [estadoSolicitud, response] = await Promise.all([
                    verificarEstadoSolicitud(correo),
                    fetch(`<?php echo BASE_URL; ?>?c=Usuario&a=getMotivoDesactivacion&correo=${encodeURIComponent(correo)}`)
                ]);
                
                const data = await response.json();
                
                if (data.success) {
                    let fechaFormateada = 'No especificada';
                    if (data.fecha) {
                        const fecha = new Date(data.fecha);
                        fechaFormateada = fecha.toLocaleDateString('es-ES', {
                            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                        });
                    }
                    
                    let motivoFormateado = data.motivo || 'Sin motivo especificado';
                    let textoBoton = '<i class="bi bi-envelope"></i> Solicitar Reactivación de Cuenta';
                    let botonDisabled = '';
                    let estiloBoton = '';
                    let mensajeEstado = '';
                    let mensajeBloqueo = '';

                    // VERIFICAR SI EL USUARIO HA ALCANZADO EL LÍMITE DE SOLICITUDES RECHAZADAS
                    try {
                        const limiteResponse = await fetch(`<?php echo BASE_URL; ?>?c=Usuario&a=verificarLimiteRechazos&correo=${encodeURIComponent(correo)}`);
                        const limiteData = await limiteResponse.json();
                        
                        if (limiteData.success && limiteData.limite_alcanzado) {
                            mensajeBloqueo = `
                                <div class="motivo-alert" style="background: #fff5f5; border-left-color: #dc3545; border-left-width: 6px;">
                                    <i class="bi bi-exclamation-triangle-fill" style="color: #dc3545;"></i>
                                    <strong style="color: #dc3545;">Límite de solicitudes alcanzado</strong>
                                    <p style="margin-top: 8px; margin-bottom: 0; color: #721c24;">
                                        Has alcanzado el límite de solicitudes rechazadas (2 máx. en 7 días). 
                                        Por favor, contacta directamente al administrador del sistema.
                                    </p>
                                </div>
                            `;
                            
                            textoBoton = '<i class="bi bi-shield-lock"></i> Límite Alcanzado';
                            botonDisabled = 'disabled';
                            estiloBoton = 'style="background: #6c757d; cursor: not-allowed; opacity: 0.7;"';
                        }
                    } catch (error) {
                        console.error('Error verificando límite de rechazos:', error);
                    }
                    
                    if (data.tiene_historial) {
                        motivoFormateado = `<div class="motivo-historial">${motivoFormateado.replace(/\n/g, '<br>')}</div>`;
                    }
                    
                    if (estadoSolicitud.tieneSolicitud) {
                        if (estadoSolicitud.estado === 'pendiente') {
                            textoBoton = '<i class="bi bi-clock-history"></i> Solicitud Pendiente';
                            botonDisabled = 'disabled';
                            estiloBoton = 'style="background: #5B6E8F; cursor: default; opacity: 0.8;"';
                            mensajeEstado = `
                                <div class="solicitud-pendiente">
                                    <i class="bi bi-info-circle"></i> 
                                    <strong>Tu solicitud está en revisión.</strong>
                                    <p style="margin-top: 8px; margin-bottom: 0; color: #000000;">Recibirás una notificación cuando sea procesada.</p>
                                </div>`;
                        } else if (estadoSolicitud.estado === 'procesada') {
                            textoBoton = '<i class="bi bi-envelope"></i> Solicitar Reactivación';
                            botonDisabled = '';
                            estiloBoton = '';
                            mensajeEstado = `
                                <div class="motivo-alert" style="background: #9297a2; border-left-color: #5d6e8e;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <strong>Tu cuenta fue reactivada anteriormente, pero actualmente está desactivada.</strong>
                                    <p style="margin-top: 8px; margin-bottom: 0;">Debes enviar una nueva solicitud de reactivación.</p>
                                </div>`;
                        }
                    }
                    
                    // Construir HTML completo del modal
                    let modalHTML = `
                        <div class="motivo-info">
                            <div class="motivo-item">
                                <span class="motivo-label">Correo:</span>
                                <span class="motivo-value">${correo}</span>
                            </div>
                            <div class="motivo-item">
                                <span class="motivo-label">Estado:</span>
                                <span class="motivo-value" style="color: #dc3545; font-weight: 600;">Cuenta Desactivada</span>
                            </div>
                            <div class="motivo-item">
                                <span class="motivo-label">Motivo:</span>
                                <span class="motivo-value">${data.motivo || 'Sin motivo especificado'}</span>
                            </div>
                            <div class="motivo-item">
                                <span class="motivo-label">Fecha:</span>
                                <span class="motivo-value">${fechaFormateada}</span>
                            </div>
                            <div class="motivo-item">
                                <span class="motivo-label">Desactivado por:</span>
                                <span class="motivo-value">${data.admin || 'Sistema'}</span>
                            </div>
                        </div>
                    `;
                    
                    if (mensajeBloqueo) {
                        modalHTML += mensajeBloqueo;
                    } else {
                        modalHTML += `
                            <div class="motivo-alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>¿Quieres recuperar tu cuenta?</strong>
                                <p style="margin-top: 8px; margin-bottom: 0; color: #333;">Envía una solicitud explicando tu situación.</p>
                            </div>
                        `;
                    }
                    
                    modalHTML += mensajeEstado;
                    
                    modalHTML += `
                        <div class="btn-ver-motivo-container">
                            <button id="btnSolicitarReactivacion" class="btn-ver-motivo" ${botonDisabled} ${estiloBoton} onclick="mostrarReactivacionModal('${correo}')">
                                ${textoBoton}
                            </button>
                        </div>
                    `;
                    
                    if (!estadoSolicitud.tieneSolicitud && !mensajeBloqueo) {
                        modalHTML += `
                            <p style="font-size: 12px; color: #666; margin-top: 15px; text-align: center;">
                                <i class="bi bi-info-circle"></i> Tu solicitud será visible para los administradores y te responderán en 24-48 horas.
                            </p>
                        `;
                    }
                    
                    motivoContent.innerHTML = modalHTML;
                    botonContacto = document.getElementById('btnSolicitarReactivacion');
                    
                } else {
                    motivoContent.innerHTML = `<div class="motivo-info"><p class="text-center">${data.message || 'No se pudo obtener la información'}</p></div>`;
                }
            } catch (error) {
                console.error('Error:', error);
                motivoContent.innerHTML = `<div class="motivo-info"><p class="text-danger">Error al cargar: ${error.message}</p></div>`;
            }
        }

        // ============================================
        // ENVIAR SOLICITUD DE REACTIVACIÓN
        // ============================================
        async function enviarSolicitudReactivacion(correo, motivo) {
            const btnEnviar = document.getElementById('btnEnviarReactivacion');
            
            btnEnviar.disabled = true;
            btnEnviar.classList.add('btn-loading');
            btnEnviar.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando solicitud...';
            
            try {
                const response = await fetch(`<?php echo BASE_URL; ?>?c=Usuario&a=enviarSolicitudReactivacion`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `correo=${encodeURIComponent(correo)}&motivo_reactivacion=${encodeURIComponent(motivo)}`
                });
                
                const data = await response.json();
                btnEnviar.classList.remove('btn-loading');
                
                if (data.success) {
                    cerrarReactivacionModal();
                    
                    const confirmMessage = document.getElementById('contactoConfirmMessage');
                    if (confirmMessage) {
                        let mensajeFormateado = data.message.replace(/\n/g, '<br>');
                        confirmMessage.innerHTML = mensajeFormateado;
                    }
                    
                    document.getElementById('contactoConfirmModal').style.display = 'block';
                    
                    setTimeout(() => {
                        obtenerMotivoDesactivacion(correo);
                    }, 1000);
                    
                } else {
                    btnEnviar.disabled = false;
                    btnEnviar.innerHTML = '<i class="bi bi-send-check"></i> Enviar Solicitud';
                    alert("❌ " + data.message);
                }
                
            } catch (error) {
                console.error('Error:', error);
                btnEnviar.classList.remove('btn-loading');
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '<i class="bi bi-send-check"></i> Enviar Solicitud';
                alert("❌ Error de conexión. Intenta nuevamente.");
            }
        }

        // ============================================
        // FUNCIONES DE VALIDACIÓN
        // ============================================
        
        // NUEVA FUNCIÓN: Validar términos y condiciones
        function validarTerminos() {
            const terminosCheckbox = document.getElementById('acepto_terminos');
            const terminosError = document.getElementById('terminos-error');
            
            if (!terminosCheckbox || !terminosCheckbox.checked) {
                if (terminosError) {
                    terminosError.style.display = 'block';
                    terminosCheckbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            } else {
                if (terminosError) {
                    terminosError.style.display = 'none';
                }
                return true;
            }
        }

        function validarRegistro() {
            // Validar términos y condiciones primero
            if (!validarTerminos()) {
                showToast('Debes aceptar los Términos y Condiciones para registrarte.', 'warning');
                return false;
            }
            
            // Aquí va tu código existente de validación de registro
            // ... (tu código de validación de nombre, apellido, documento, etc.)
            
            return true;
        }

        function validarLogin() {
            // ... (tu código existente de validación de login)
            return true;
        }

        // Función para mostrar toast
        function showToast(message, type = 'info') {
            // Verificar si ya existe un contenedor de toast
            let container = document.querySelector('.toast-container-custom');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container-custom';
                container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
                document.body.appendChild(container);
            }
            
            const toastId = 'toast-' + Date.now();
            const bgColor = type === 'warning' ? '#dc3545' : '#2f3e53';
            
            const toastHTML = `
                <div id="${toastId}" style="background: ${bgColor}; color: white; padding: 15px 25px; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; animation: slideIn 0.3s ease;">
                    <i class="bi ${type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'}"></i>
                    <span style="flex: 1;">${message}</span>
                    <button onclick="document.getElementById('${toastId}').remove()" style="background: none; border: none; color: white; cursor: pointer;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', toastHTML);
            
            setTimeout(() => {
                const toast = document.getElementById(toastId);
                if (toast) {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 5000);
        }

        function limpiarFormularioLogin() {
            document.getElementById('login_correo').value = '';
            document.getElementById('login_contrasena').value = '';
        }

        function limpiarFormularioRegistro() {
            document.getElementById('reg_nombre').value = '';
            document.getElementById('reg_apellido').value = '';
            document.getElementById('reg_tipo_documento').selectedIndex = 0;
            document.getElementById('reg_documento').value = '';
            document.getElementById('reg_celular').value = '';
            document.getElementById('reg_correo').value = '';
            document.getElementById('reg_contrasena').value = '';
            document.getElementById('reg_confirmar_contrasena').value = '';
            
            // No limpiar el checkbox de términos si ya estaba aceptado
            // Solo se limpia si no venimos de aceptar términos
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('terminos') !== 'aceptados') {
                const terminosCheckbox = document.getElementById('acepto_terminos');
                if (terminosCheckbox) terminosCheckbox.checked = false;
            }
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const button = input.nextElementSibling;
            if (button) {
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
                }
            }
        }

        // ============================================
        // EVENTOS DE VENTANA PARA MODALES
        // ============================================
        window.onclick = function(event) {
            const motivoModal = document.getElementById('motivoModal');
            const reactivacionModal = document.getElementById('reactivacionModal');
            const contactoModal = document.getElementById('contactoConfirmModal');
            
            if (event.target == motivoModal) cerrarMotivoModal();
            if (event.target == reactivacionModal) cerrarReactivacionModal();
            if (event.target == contactoModal) cerrarContactoConfirmModal();
        };

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                cerrarMotivoModal();
                cerrarReactivacionModal();
                cerrarContactoConfirmModal();
            }
        });

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar validación del modal de reactivación
            setupReactivacionValidation();
            
            // Verificar si debemos mostrar el formulario de registro automáticamente
            <?php if ($mostrarRegistroDirecto): ?>
            setTimeout(function() {
                const btnRegistro = document.getElementById('btn__registrarse');
                if (btnRegistro) btnRegistro.click();
            }, 100);
            <?php endif; ?>
            
            // Restricciones de entrada
            document.getElementById('reg_nombre')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });
            
            document.getElementById('reg_apellido')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            });
            
            document.getElementById('reg_documento')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            document.getElementById('reg_celular')?.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Configurar botones de formulario
            const btnLogin = document.getElementById('btn_iniciar_sesion');
            const btnRegistro = document.getElementById('btn__registrarse');
            const formLogin = document.querySelector('.formulario__login');
            const formRegistro = document.querySelector('.formulario__register');
            
            if (btnLogin && formLogin && formRegistro) {
                btnLogin.addEventListener('click', function() {
                    formLogin.style.display = 'block';
                    formRegistro.style.display = 'none';
                    btnLogin.classList.add('active');
                    btnRegistro?.classList.remove('active');
                });
            }
            
            if (btnRegistro && formLogin && formRegistro) {
                btnRegistro.addEventListener('click', function() {
                    formLogin.style.display = 'none';
                    formRegistro.style.display = 'block';
                    btnRegistro.classList.add('active');
                    btnLogin?.classList.remove('active');
                });
            }
            
            // Si hay error de términos, mostrar mensaje
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error') === 'terminos_no_aceptados') {
                const terminosError = document.getElementById('terminos-error');
                if (terminosError) {
                    terminosError.style.display = 'block';
                    terminosError.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Debes aceptar los Términos y Condiciones para registrarte.';
                }
            }
        });

        // Prevenir caché
        window.onpageshow = function(event) {
            if (event.persisted) window.location.reload();
        };

        // Añadir estilo de animación para toasts
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>