<?php
// Prevenir acceso directo
if (!defined('BASE_URL')) {
    define('BASE_URL', '/tulook/');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>TuLook - Términos y Condiciones</title>

    <!-- META TAGS PARA PREVENIR CACHE -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Fonts y estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Basta.css">
    
    <style>
        /* ============================================
           ESTILOS PARA TÉRMINOS Y CONDICIONES
           COLORES: #2f3e53, #1a2537, #4a5f7a
           ============================================ */
        
        :root {
            --primary-dark: #1a2537;
            --primary: #2f3e53;
            --primary-light: #4a5f7a;
            --accent: #5b6e8f;
            --light-bg: #f8f9fa;
            --border-color: #e9ecef;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #66758b 0%, #2f3e53 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .terminos-container {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
        }

        .terminos-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* HEADER CON GRADIENTE */
        .terminos-header {
            background: linear-gradient(135deg, #2f3e53 0%, #1a2537 100%);
            padding: 30px 40px;
            color: white;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 4px solid rgba(255, 255, 255, 0.1);
        }

        .terminos-icon {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .terminos-icon i {
            font-size: 35px;
            color: white;
        }

        .terminos-title {
            flex: 1;
        }

        .terminos-title h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
        }

        .terminos-title p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 15px;
        }

        .terminos-version {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            margin-top: 10px;
        }

        /* BARRA DE PROGRESO DE LECTURA */
        .reading-progress {
            height: 5px;
            background: rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .progress-bar-custom {
            height: 100%;
            background: linear-gradient(90deg, #4a5f7a, #8a9bb5);
            width: 0%;
            transition: width 0.1s ease;
            border-radius: 0 3px 3px 0;
        }

        /* CONTENIDO PRINCIPAL */
        .terminos-content {
            padding: 40px;
            max-height: 600px;
            overflow-y: auto;
            background: white;
            scroll-behavior: smooth;
        }

        /* ESTILOS DE SCROLLBAR PERSONALIZADO */
        .terminos-content::-webkit-scrollbar {
            width: 8px;
        }

        .terminos-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .terminos-content::-webkit-scrollbar-thumb {
            background: #2f3e53;
            border-radius: 10px;
        }

        .terminos-content::-webkit-scrollbar-thumb:hover {
            background: #1a2537;
        }

        /* SECCIONES */
        .terminos-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e9ecef;
        }

        .terminos-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .terminos-section h2 {
            color: #2f3e53;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Montserrat', sans-serif;
            position: sticky;
            top: 0;
            background: white;
            padding: 15px 0;
            z-index: 10;
            border-bottom: 2px solid #f0f0f0;
        }

        .terminos-section h2 i {
            color: #4a5f7a;
            font-size: 24px;
            width: 32px;
            text-align: center;
        }

        .terminos-section h3 {
            color: #1a2537;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 0 12px;
        }

        .terminos-section p {
            color: #333;
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 15px;
        }

        /* LISTAS */
        .terminos-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
        }

        .terminos-list li {
            padding: 10px 0 10px 30px;
            position: relative;
            line-height: 1.6;
            color: #444;
            border-bottom: 1px dashed #f0f0f0;
        }

        .terminos-list li:last-child {
            border-bottom: none;
        }

        .terminos-list li i {
            position: absolute;
            left: 0;
            top: 12px;
            color: #2f3e53;
            font-size: 16px;
        }

        .terminos-list.numeric {
            list-style: decimal;
            padding-left: 20px;
        }

        .terminos-list.numeric li {
            padding-left: 5px;
            border-bottom: none;
        }

        /* TABLAS DE PLAZOS */
        .plazos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .plazos-table th {
            background: #2f3e53;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }

        .plazos-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #333;
        }

        .plazos-table tr:last-child td {
            border-bottom: none;
        }

        .plazos-table tr:hover td {
            background: rgba(47, 62, 83, 0.05);
        }

        /* CARDS DE INFORMACIÓN */
        .info-card {
            background: linear-gradient(135deg, #f8f9fa, #ffffff);
            border-left: 6px solid #2f3e53;
            padding: 20px 25px;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .info-card i {
            font-size: 28px;
            color: #2f3e53;
            flex-shrink: 0;
        }

        .info-card-content {
            flex: 1;
        }

        .info-card h4 {
            color: #2f3e53;
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .info-card p {
            margin: 0;
            color: #555;
        }

        /* ALERTAS DE EJEMPLOS */
        .example-box {
            background: #fff5e6;
            border: 1px solid #ffd8b8;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            position: relative;
        }

        .example-box::before {
            content: '📌 EJEMPLO';
            position: absolute;
            top: -12px;
            left: 20px;
            background: #2f3e53;
            color: white;
            padding: 4px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .example-box p {
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .example-box i {
            color: #cc7b2e;
        }

        /* FOOTER DEL MODAL */
        .terminos-footer {
            background: #f8f9fa;
            padding: 25px 40px;
            border-top: 2px solid #e9ecef;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* CHECKBOX DE ACEPTACIÓN MEJORADO */
        .terminos-checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .terminos-checkbox-container:hover {
            border-color: #2f3e53;
            box-shadow: 0 4px 12px rgba(47, 62, 83, 0.1);
        }

        .custom-checkbox {
            position: relative;
            width: 28px;
            height: 28px;
            flex-shrink: 0;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 28px;
            width: 28px;
            background-color: white;
            border: 2px solid #2f3e53;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-checkbox:hover input ~ .checkmark {
            background-color: rgba(47, 62, 83, 0.05);
            transform: scale(1.05);
        }

        .custom-checkbox input:checked ~ .checkmark {
            background-color: #2f3e53;
            border-color: #2f3e53;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .custom-checkbox .checkmark:after {
            left: 9px;
            top: 4px;
            width: 8px;
            height: 14px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .checkbox-label {
            flex: 1;
            font-size: 15px;
            line-height: 1.6;
            color: #333;
        }

        .checkbox-label strong {
            color: #2f3e53;
        }

        .checkbox-label a {
            color: #2f3e53;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .checkbox-label a:hover {
            border-bottom-color: #2f3e53;
        }

        /* REQUISITOS DE ACEPTACIÓN */
        .acceptance-requirements {
            display: flex;
            gap: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }

        .requirement i {
            color: #28a745;
        }

        .requirement i.bi-x-circle {
            color: #dc3545;
        }

        /* BOTONES DE ACCIÓN */
        .terminos-actions {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(145deg, #2f3e53, #1a2537);
            color: white;
            box-shadow: 0 8px 20px rgba(47, 62, 83, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(145deg, #1a2537, #0f1a24);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(47, 62, 83, 0.4);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-outline {
            background: transparent;
            color: #2f3e53;
            border: 2px solid #2f3e53;
        }

        .btn-outline:hover {
            background: rgba(47, 62, 83, 0.05);
            transform: translateY(-2px);
        }

        /* MODAL DE CONFIRMACIÓN */
        .confirm-modal {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .confirm-modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: modalPop 0.3s ease;
            border: 2px solid #2f3e53;
        }

        @keyframes modalPop {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .confirm-modal-content i {
            font-size: 70px;
            color: #2f3e53;
            margin-bottom: 20px;
        }

        .confirm-modal-content h3 {
            color: #2f3e53;
            margin-bottom: 15px;
            font-size: 24px;
            font-weight: 700;
        }

        .confirm-modal-content p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .confirm-modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        /* ============================================
           MODAL DE SALIDA - DISEÑO PEQUEÑO Y ELEGANTE
           ============================================ */
        .modal-salida {
            display: none;
            position: fixed;
            z-index: 99999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .modal-salida-content {
            background: white;
            padding: 25px;
            border-radius: 16px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            animation: modalPop 0.3s ease;
            border: 2px solid #dc3545;
            box-shadow: 0 15px 30px rgba(220, 53, 69, 0.15);
        }

        .modal-salida-content i {
            font-size: 45px;
            color: #dc3545;
            margin-bottom: 12px;
        }

        .modal-salida-content h4 {
            color: #dc3545;
            margin-bottom: 10px;
            font-size: 20px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
        }

        .modal-salida-content p {
            color: #666;
            margin-bottom: 12px;
            line-height: 1.5;
            font-size: 14px;
        }

        .modal-salida-content .alert-message {
            background: #fff5f5;
            padding: 10px;
            border-radius: 8px;
            color: #dc3545;
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .modal-salida-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-salida {
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .btn-salida-outline {
            background: transparent;
            color: #2f3e53;
            border: 2px solid #2f3e53;
        }

        .btn-salida-outline:hover {
            background: rgba(47, 62, 83, 0.05);
            transform: translateY(-2px);
        }

        .btn-salida-danger {
            background: linear-gradient(145deg, #dc3545, #b02a37);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-salida-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(220, 53, 69, 0.4);
        }

        /* INDICADOR DE TIEMPO DE LECTURA */
        .reading-time {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
        }

        /* ESTILOS PARA EL BOTÓN DE VOLVER */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2f3e53;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 50px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            background: transparent;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
        }

        .btn-back:hover {
            background: rgba(47, 62, 83, 0.1);
            transform: translateX(-5px);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .terminos-header {
                flex-direction: column;
                text-align: center;
                padding: 25px;
            }

            .terminos-icon {
                width: 60px;
                height: 60px;
            }

            .terminos-icon i {
                font-size: 28px;
            }

            .terminos-title h1 {
                font-size: 26px;
            }

            .terminos-content {
                padding: 25px;
            }

            .terminos-section h2 {
                font-size: 20px;
                position: static;
            }

            .terminos-footer {
                padding: 20px;
            }

            .terminos-checkbox-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .acceptance-requirements {
                flex-direction: column;
                gap: 10px;
            }

            .terminos-actions {
                flex-direction: column-reverse;
                width: 100%;
            }

            .btn {
                width: 100%;
                padding: 12px 24px;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .info-card {
                flex-direction: column;
                text-align: center;
            }

            .plazos-table {
                font-size: 14px;
            }

            .plazos-table th,
            .plazos-table td {
                padding: 8px 10px;
            }

            .modal-salida-content {
                padding: 20px;
                max-width: 350px;
            }

            .modal-salida-actions {
                flex-direction: column;
            }

            .btn-salida {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .terminos-content {
                padding: 20px;
            }

            .terminos-section h2 {
                font-size: 18px;
            }

            .terminos-list li {
                padding-left: 25px;
                font-size: 14px;
            }

            .plazos-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* IMPRESIÓN */
        @media print {
            .terminos-footer,
            .reading-progress,
            .btn,
            .modal-salida {
                display: none;
            }

            .terminos-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .terminos-header {
                background: #2f3e53;
                color: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* ANIMACIONES */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(47, 62, 83, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(47, 62, 83, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(47, 62, 83, 0);
            }
        }

        .btn-primary:not(:disabled) {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="terminos-container">
        <div class="terminos-card">
            <!-- HEADER CON PROGRESO DE LECTURA -->
            <div class="terminos-header">
                <div class="terminos-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="terminos-title">
                    <h1>Términos y Condiciones</h1>
                    <p>TuLook - Plataforma de Comercio Electrónico</p>
                    <span class="terminos-version">
                        <i class="bi bi-tag"></i> Versión 2.0 - Febrero 2026
                    </span>
                    <span class="reading-time ms-3">
                        <i class="bi bi-clock"></i> Tiempo de lectura: 8 min
                    </span>
                </div>
            </div>

            <!-- BARRA DE PROGRESO DE LECTURA -->
            <div class="reading-progress">
                <div class="progress-bar-custom" id="readingProgress"></div>
            </div>

            <!-- CONTENIDO DE TÉRMINOS Y CONDICIONES -->
            <div class="terminos-content" id="terminosContent">
                <!-- 1. INTRODUCCIÓN -->
                <div class="terminos-section">
                    <h2><i class="bi bi-info-circle"></i> 1. Introducción y Aceptación</h2>
                    <p>Bienvenido a <strong>TuLook</strong>. Al acceder, navegar o registrarte en nuestra plataforma, aceptas cumplir y quedar vinculado por los presentes Términos y Condiciones. Si no estás de acuerdo con alguna parte de estos términos, no podrás registrarte ni utilizar nuestros servicios.</p>
                    
                    <div class="info-card">
                        <i class="bi bi-shield-check"></i>
                        <div class="info-card-content">
                            <h4>Compromiso de Transparencia</h4>
                            <p>En TuLook nos comprometemos a mantener términos claros, justos y actualizados. Te notificaremos con 15 días de anticipación cualquier cambio significativo en estas condiciones.</p>
                        </div>
                    </div>
                </div>

                <!-- 2. CAPACIDAD LEGAL -->
                <div class="terminos-section">
                    <h2><i class="bi bi-person-badge"></i> 2. Capacidad Legal</h2>
                    <p>Para registrarte en TuLook debes:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-check-circle"></i> Ser mayor de 18 años o tener autorización legal de tus padres/tutores.</li>
                        <li><i class="bi bi-check-circle"></i> Contar con capacidad legal para contratar según las leyes de tu país.</li>
                        <li><i class="bi bi-check-circle"></i> Proporcionar información veraz, completa y actualizada.</li>
                        <li><i class="bi bi-check-circle"></i> No tener cuentas previamente suspendidas por violaciones a estos términos.</li>
                    </ul>
                    
                    <div class="example-box">
                        <p><i class="bi bi-exclamation-triangle"></i> <strong>Ejemplo:</strong> Si eres menor de edad, un adulto responsable debe crear y gestionar la cuenta. Las compras realizadas por menores sin autorización serán responsabilidad del representante legal.</p>
                    </div>
                </div>

                <!-- 3. REGISTRO Y SEGURIDAD DE CUENTA -->
                <div class="terminos-section">
                    <h2><i class="bi bi-shield-lock"></i> 3. Registro y Seguridad de Cuenta</h2>
                    <p>Al crear una cuenta en TuLook, aceptas:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-check-circle"></i> Proporcionar datos exactos, verídicos y mantenerlos actualizados.</li>
                        <li><i class="bi bi-check-circle"></i> No crear cuentas múltiples para evadir restricciones o limitaciones.</li>
                        <li><i class="bi bi-check-circle"></i> No compartir tus credenciales de acceso con terceros.</li>
                        <li><i class="bi bi-check-circle"></i> Notificar inmediatamente cualquier uso no autorizado de tu cuenta.</li>
                        <li><i class="bi bi-check-circle"></i> Aceptar que eres el único responsable de las actividades realizadas en tu cuenta.</li>
                    </ul>
                    
                    <h3>Política de Contraseñas Seguras</h3>
                    <p>Exigimos contraseñas robustas que cumplan:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-key"></i> Mínimo 12 caracteres</li>
                        <li><i class="bi bi-key"></i> Al menos 2 números</li>
                        <li><i class="bi bi-key"></i> Al menos 1 símbolo especial (!@#$%^&*)</li>
                        <li><i class="bi bi-key"></i> Al menos 1 letra mayúscula y 1 minúscula</li>
                    </ul>
                </div>

                <!-- 4. PRIVACIDAD Y PROTECCIÓN DE DATOS -->
                <div class="terminos-section">
                    <h2><i class="bi bi-incognito"></i> 4. Privacidad y Protección de Datos</h2>
                    <p>Tu privacidad es fundamental. Nuestra <a href="#" style="color: #2f3e53; font-weight: 600;">Política de Privacidad</a> explica detalladamente:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-database"></i> Qué datos personales recopilamos (nombre, documento, correo, celular, dirección, historial de compras, preferencias).</li>
                        <li><i class="bi bi-geo-alt"></i> Cómo utilizamos tu ubicación para mejorar la experiencia de compra.</li>
                        <li><i class="bi bi-credit-card"></i> Procesamiento seguro de pagos (no almacenamos datos de tarjetas).</li>
                        <li><i class="bi bi-cookie"></i> Uso de cookies para personalización y análisis.</li>
                        <li><i class="bi bi-share"></i> Condiciones bajo las cuales compartimos datos con terceros (solo con tu consentimiento explícito).</li>
                    </ul>
                    
                    <div class="info-card">
                        <i class="bi bi-gdpr"></i>
                        <div class="info-card-content">
                            <h4>Tus Derechos ARCO</h4>
                            <p>Puedes ejercer tus derechos de Acceso, Rectificación, Cancelación y Oposición enviando un correo a privacidad@tulook.com. Respondemos en máximo 5 días hábiles.</p>
                        </div>
                    </div>
                </div>

                <!-- 5. COMPRAS Y TRANSACCIONES -->
                <div class="terminos-section">
                    <h2><i class="bi bi-cart-check"></i> 5. Compras y Transacciones</h2>
                    <p>Al realizar una compra en TuLook, aceptas las siguientes condiciones:</p>
                    
                    <h3>5.1 Proceso de Compra</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-check-circle"></i> Los precios mostrados incluyen impuestos (IVA) a menos que se indique lo contrario.</li>
                        <li><i class="bi bi-check-circle"></i> La disponibilidad de productos está sujeta a inventario en tiempo real.</li>
                        <li><i class="bi bi-check-circle"></i> Al hacer clic en "Comprar", realizas una oferta vinculante de compra.</li>
                        <li><i class="bi bi-check-circle"></i> Recibirás un correo de confirmación con los detalles de tu pedido.</li>
                        <li><i class="bi bi-check-circle"></i> TuLook se reserva el derecho de cancelar pedidos por errores de precio, falta de stock o sospecha de fraude.</li>
                    </ul>

                    <h3>5.2 Métodos de Pago</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-credit-card"></i> Tarjetas de crédito/débito (Visa, Mastercard, American Express)</li>
                        <li><i class="bi bi-bank"></i> Transferencia bancaria</li>
                        <li><i class="bi bi-wallet2"></i> Billeteras digitales (Nequi, Daviplata)</li>
                        <li><i class="bi bi-paypal"></i> PayPal</li>
                    </ul>

                    <h3>5.3 Plazos de Entrega</h3>
                    <table class="plazos-table">
                        <thead>
                            <tr>
                                <th>Tipo de Envío</th>
                                <th>Plazo Estimado</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Estándar</td>
                                <td>3-5 días hábiles</td>
                                <td>Gratis en compras > $100.000</td>
                            </tr>
                            <tr>
                                <td>Express</td>
                                <td>1-2 días hábiles</td>
                                <td>$15.000</td>
                            </tr>
                            <tr>
                                <td>Recoge en tienda</td>
                                <td>24 horas</td>
                                <td>Gratis</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 6. DEVOLUCIONES, CAMBIOS Y GARANTÍAS -->
                <div class="terminos-section">
                    <h2><i class="bi bi-arrow-return-left"></i> 6. Devoluciones, Cambios y Garantías</h2>
                    
                    <h3>6.1 Política de Devoluciones</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-clock"></i> Plazo máximo: 30 días calendario desde la recepción del producto.</li>
                        <li><i class="bi bi-box"></i> Productos deben estar sin usar, con etiquetas y empaque original.</li>
                        <li><i class="bi bi-receipt"></i> Debes presentar la factura o comprobante de compra.</li>
                        <li><i class="bi bi-cash"></i> El reembolso se procesa en 5-7 días hábiles por el mismo método de pago.</li>
                    </ul>

                    <h3>6.2 Excepciones (No aplican devoluciones)</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-exclamation-triangle"></i> Productos personalizados o hechos a medida.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Productos de higiene personal íntima.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Alimentos perecederos.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Software, música o videos descargables.</li>
                    </ul>

                    <h3>6.3 Garantía de Productos</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-shield"></i> Productos nuevos: 1 año de garantía</li>
                        <li><i class="bi bi-shield"></i> Productos electrónicos: 6 meses de garantía</li>
                        <li><i class="bi bi-shield"></i> Productos de temporada: 3 meses de garantía</li>
                    </ul>

                    <div class="example-box">
                        <p><i class="bi bi-lightbulb"></i> <strong>Ejemplo práctico:</strong> Compraste un celular, te llegó con la pantalla rota. Tienes 30 días para solicitar cambio. Envía fotos del daño a soporte@tulook.com y te enviaremos uno nuevo sin costo adicional.</p>
                    </div>
                </div>

                <!-- 7. INTERACCIÓN SOCIAL: COMENTARIOS Y ME GUSTA -->
                <div class="terminos-section">
                    <h2><i class="bi bi-chat-dots"></i> 7. Interacción Social en la Plataforma</h2>
                    <p>TuLook fomenta una comunidad respetuosa. Al utilizar funciones de interacción (comentarios, reseñas, me gusta), aceptas:</p>
                    
                    <h3>7.1 Normas de Publicación</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-check-circle"></i> Ser respetuoso con otros usuarios, vendedores y administradores.</li>
                        <li><i class="bi bi-x-circle"></i> <strong>PROHIBIDO:</strong> Contenido ofensivo, discriminatorio, violento, sexual explícito o ilegal.</li>
                        <li><i class="bi bi-x-circle"></i> <strong>PROHIBIDO:</strong> Spam, publicidad no autorizada, enlaces maliciosos.</li>
                        <li><i class="bi bi-x-circle"></i> <strong>PROHIBIDO:</strong> Suplantación de identidad o cuentas falsas.</li>
                        <li><i class="bi bi-check-circle"></i> Las reseñas deben basarse en experiencias reales de compra.</li>
                    </ul>

                    <h3>7.2 Moderación y Consecuencias</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-shield"></i> TuLook se reserva el derecho de moderar, editar o eliminar cualquier contenido que viole estas normas.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Infracciones leves: advertencia y eliminación del contenido.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Infracciones graves: suspensión temporal de la cuenta (7-30 días).</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Infracciones muy graves o reincidencia: <strong>desactivación permanente de la cuenta</strong>.</li>
                    </ul>

                    <div class="info-card">
                        <i class="bi bi-chat-quote"></i>
                        <div class="info-card-content">
                            <h4>Consejo para reseñas útiles</h4>
                            <p>Incluye fotos del producto, menciona calidad, tiempo de entrega y servicio al cliente. Las reseñas detalladas ayudan a otros compradores a tomar mejores decisiones.</p>
                        </div>
                    </div>
                </div>

                <!-- 8. PROPIEDAD INTELECTUAL -->
                <div class="terminos-section">
                    <h2><i class="bi bi-copyright"></i> 8. Propiedad Intelectual</h2>
                    <p>Todo el contenido de TuLook (logos, imágenes, textos, diseño, software) es propiedad exclusiva de TuLook o sus licenciantes y está protegido por leyes de derechos de autor.</p>
                    
                    <ul class="terminos-list">
                        <li><i class="bi bi-check-circle"></i> Los usuarios conservan los derechos de autor de sus reseñas y comentarios, pero otorgan a TuLook una licencia no exclusiva para publicarlos.</li>
                        <li><i class="bi bi-x-circle"></i> Queda prohibido copiar, modificar, distribuir o utilizar comercialmente nuestro contenido sin autorización expresa.</li>
                        <li><i class="bi bi-x-circle"></i> No puedes utilizar nuestros logos o marcas sin consentimiento por escrito.</li>
                    </ul>
                </div>

                <!-- 9. SUSPENSIÓN Y DESACTIVACIÓN DE CUENTAS -->
                <div class="terminos-section">
                    <h2><i class="bi bi-person-x"></i> 9. Suspensión y Desactivación de Cuentas</h2>
                    <p>TuLook se reserva el derecho de suspender o desactivar cuentas que:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-exclamation-triangle"></i> Viole estos Términos y Condiciones.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Realice actividades fraudulentas o sospechosas.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Proporcione información falsa o inexacta.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Acumule múltiples solicitudes de reactivación rechazadas.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Permanezca inactiva por más de 2 años.</li>
                    </ul>
                    
                    <h3>9.1 Proceso de Desactivación</h3>
                    <ul class="terminos-list">
                        <li><i class="bi bi-file-text"></i> Se registrará el motivo específico de desactivación.</li>
                        <li><i class="bi bi-file-text"></i> El usuario podrá ver el motivo al intentar iniciar sesión.</li>
                        <li><i class="bi bi-file-text"></i> Se permite un máximo de <strong>2 solicitudes de reactivación</strong> en un período de 7 días.</li>
                        <li><i class="bi bi-file-text"></i> Después de 3 desactivaciones, la cuenta será bloqueada permanentemente.</li>
                    </ul>
                </div>

                <!-- 10. LIMITACIÓN DE RESPONSABILIDAD -->
                <div class="terminos-section">
                    <h2><i class="bi bi-shield-exclamation"></i> 10. Limitación de Responsabilidad</h2>
                    <p>Hasta el máximo permitido por la ley, TuLook no será responsable por:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-x-circle"></i> Daños indirectos, incidentales o consecuentes derivados del uso de la plataforma.</li>
                        <li><i class="bi bi-x-circle"></i> Pérdida de datos, ingresos o beneficios.</li>
                        <li><i class="bi bi-x-circle"></i> Retrasos o fallas en entregas por causas de fuerza mayor.</li>
                        <li><i class="bi bi-x-circle"></i> Contenido publicado por otros usuarios.</li>
                        <li><i class="bi bi-x-circle"></i> Productos de vendedores terceros (actuamos como intermediarios).</li>
                    </ul>
                    
                    <p>Nuestra responsabilidad máxima en cualquier caso no excederá el monto pagado por el producto o servicio en cuestión.</p>
                </div>

                <!-- 11. JURISDICCIÓN Y LEY APLICABLE -->
                <div class="terminos-section">
                    <h2><i class="bi bi-bank"></i> 11. Jurisdicción y Ley Aplicable</h2>
                    <p>Estos Términos se rigen por las leyes de la República de Colombia. Cualquier disputa será sometida a los jueces de la ciudad de Bogotá D.C., renunciando expresamente a cualquier otro fuero.</p>
                    
                    <div class="info-card">
                        <i class="bi bi-gavel"></i>
                        <div class="info-card-content">
                            <h4>Resolución de Conflictos</h4>
                            <p>Antes de acudir a instancias judiciales, te invitamos a contactar nuestro centro de solución de controversias: conflictos@tulook.com. Buscamos resolver amigablemente el 95% de las disputas.</p>
                        </div>
                    </div>
                </div>

                <!-- 12. CONTACTO Y SOPORTE -->
                <div class="terminos-section">
                    <h2><i class="bi bi-headset"></i> 12. Contacto y Soporte</h2>
                    <p>Para consultas sobre estos términos, tu cuenta o cualquier inconveniente:</p>
                    <ul class="terminos-list">
                        <li><i class="bi bi-envelope"></i> <strong>Soporte general:</strong> soporte@tulook.com</li>
                        <li><i class="bi bi-shield-lock"></i> <strong>Privacidad y datos:</strong> privacidad@tulook.com</li>
                        <li><i class="bi bi-file-earmark-text"></i> <strong>Asuntos legales:</strong> legal@tulook.com</li>
                        <li><i class="bi bi-telephone"></i> <strong>Línea de atención:</strong> +57 601 123 4567 (L-V 8am-6pm)</li>
                        <li><i class="bi bi-chat"></i> <strong>Chat en vivo:</strong> Disponible 24/7 en nuestra web</li>
                    </ul>
                    
                    <div class="alert alert-primary-light" style="margin-top: 20px;">
                        <i class="bi bi-clock-history"></i>
                        <strong>Tiempo de respuesta:</strong> Máximo 24 horas hábiles para consultas generales, 48 horas para asuntos legales.
                    </div>
                </div>

                <!-- Última actualización -->
                <div style="text-align: center; margin-top: 30px; color: #6c757d; font-size: 13px;">
                    <i class="bi bi-calendar3"></i> Última actualización: 12 de febrero de 2026
                </div>
            </div>

            <!-- FOOTER CON ACEPTACIÓN - CORREGIDO -->
            <div class="terminos-footer">
                <!-- CHECKBOX DE ACEPTACIÓN MEJORADO Y CORREGIDO -->
                <div class="terminos-checkbox-container" id="acceptanceContainer">
                    <div class="custom-checkbox">
                        <input type="checkbox" id="aceptarTerminosCheckbox" autocomplete="off">
                        <span class="checkmark"></span>
                    </div>
                    <div class="checkbox-label">
                        <label for="aceptarTerminosCheckbox" style="cursor: pointer; width: 100%; display: block;">
                            <strong>He leído y acepto los Términos y Condiciones</strong> de TuLook, incluyendo la Política de Privacidad, el tratamiento de mis datos personales y las normas de interacción en la plataforma.
                        </label>
                        <br>
                        <small style="color: #6c757d; display: block; margin-top: 5px;">
                            <i class="bi bi-info-circle"></i> Al aceptar, confirmas que eres mayor de edad y que la información proporcionada es veraz.
                        </small>
                    </div>
                </div>

                <!-- REQUISITOS DE ACEPTACIÓN -->
                <div class="acceptance-requirements">
                    <span class="requirement" id="reqLectura">
                        <i class="bi bi-x-circle"></i> Leer todo el documento
                    </span>
                    <span class="requirement" id="reqCheckbox">
                        <i class="bi bi-x-circle"></i> Marcar casilla de aceptación
                    </span>
                    <span class="requirement" id="reqTiempo">
                        <i class="bi bi-check-circle"></i> Tiempo mínimo: 30 segundos
                    </span>
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="terminos-actions">
                    <button onclick="mostrarModalSalida();" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Volver al Login
                    </button>
                    <button class="btn btn-primary" id="btnAceptarTerminos" disabled>
                        <i class="bi bi-check2-circle"></i> Aceptar y Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN - TÉRMINOS ACEPTADOS -->
    <div id="confirmacionModal" class="confirm-modal">
        <div class="confirm-modal-content">
            <i class="bi bi-check-circle-fill"></i>
            <h3>¡Términos Aceptados!</h3>
            <p>Has aceptado exitosamente los Términos y Condiciones de TuLook. Ahora puedes completar tu registro y comenzar a disfrutar de todos nuestros servicios.</p>
            <div class="confirm-modal-actions">
                <button class="btn btn-primary" onclick="cerrarModalYContinuar()">
                    <i class="bi bi-arrow-right-circle"></i> Continuar con Registro
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE SALIDA - DISEÑO PEQUEÑO Y ELEGANTE -->
    <div id="modalSalida" class="modal-salida">
        <div class="modal-salida-content">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <h4>¿Quieres salir?</h4>
            <p>Es posible que los cambios no se guarden.</p>
            <div class="alert-message">
                <i class="bi bi-exclamation-circle"></i> Los cambios no se guardarán
            </div>
            <div class="modal-salida-actions">
                <button class="btn-salida btn-salida-outline" onclick="cerrarModalSalida()">
                    <i class="bi bi-arrow-left-circle"></i> Seguir Leyendo
                </button>
                <button class="btn-salida btn-salida-danger" onclick="confirmarSalida()">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </button>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // SISTEMA DE VERIFICACIÓN DE LECTURA Y ACEPTACIÓN
        // ============================================
        
        // Elementos del DOM
        const terminosContent = document.getElementById('terminosContent');
        const aceptarCheckbox = document.getElementById('aceptarTerminosCheckbox');
        const btnAceptar = document.getElementById('btnAceptarTerminos');
        const reqLectura = document.getElementById('reqLectura');
        const reqCheckbox = document.getElementById('reqCheckbox');
        const progressBar = document.getElementById('readingProgress');
        const confirmacionModal = document.getElementById('confirmacionModal');
        const modalSalida = document.getElementById('modalSalida');
        
        // Variables de estado
        let haLeidoCompleto = false;
        let tiempoInicio = Date.now();
        let scrollAlcanzado = false;
        let tiempoMinimoCumplido = false;
        let salidaConfirmada = false;
        
        // ============================================
        // 1. SISTEMA DE PROGRESO DE LECTURA
        // ============================================
        function actualizarProgresoLectura() {
            if (!terminosContent) return;
            
            const scrollTop = terminosContent.scrollTop;
            const scrollHeight = terminosContent.scrollHeight;
            const clientHeight = terminosContent.clientHeight;
            
            let porcentaje = 0;
            if (scrollHeight > clientHeight) {
                porcentaje = (scrollTop / (scrollHeight - clientHeight)) * 100;
            }
            progressBar.style.width = `${Math.min(porcentaje, 100)}%`;
            
            // Verificar si llegó al 95% (considerado "leído completo")
            if (!scrollAlcanzado && porcentaje >= 95) {
                scrollAlcanzado = true;
                haLeidoCompleto = true;
                reqLectura.innerHTML = '<i class="bi bi-check-circle"></i> Documento leído completamente';
                reqLectura.style.color = '#28a745';
                validarAceptacion();
            }
        }
        
        // ============================================
        // 2. CONTADOR DE TIEMPO MÍNIMO (30 segundos)
        // ============================================
        function verificarTiempoMinimo() {
            const tiempoTranscurrido = (Date.now() - tiempoInicio) / 1000;
            const reqTiempo = document.getElementById('reqTiempo');
            
            if (!reqTiempo) return;
            
            if (!tiempoMinimoCumplido && tiempoTranscurrido >= 30) {
                tiempoMinimoCumplido = true;
                reqTiempo.innerHTML = '<i class="bi bi-check-circle"></i> Tiempo mínimo cumplido (30s)';
                reqTiempo.style.color = '#28a745';
                validarAceptacion();
            }
            
            // Actualizar contador visual
            if (!tiempoMinimoCumplido) {
                const segundosRestantes = Math.ceil(30 - tiempoTranscurrido);
                reqTiempo.innerHTML = `<i class="bi bi-hourglass-split"></i> Tiempo mínimo: ${segundosRestantes}s restantes`;
                reqTiempo.style.color = '#ffc107';
            }
        }
        
        // ============================================
        // 3. VALIDACIÓN DE REQUISITOS PARA ACTIVAR BOTÓN
        // ============================================
        function validarAceptacion() {
            const checkboxMarcado = aceptarCheckbox ? aceptarCheckbox.checked : false;
            
            // Actualizar requisito del checkbox
            if (checkboxMarcado) {
                reqCheckbox.innerHTML = '<i class="bi bi-check-circle"></i> Casilla marcada';
                reqCheckbox.style.color = '#28a745';
            } else {
                reqCheckbox.innerHTML = '<i class="bi bi-x-circle"></i> Marcar casilla de aceptación';
                reqCheckbox.style.color = '#dc3545';
            }
            
            // Habilitar botón SOLO si se cumplen TODOS los requisitos
            if (checkboxMarcado && haLeidoCompleto && tiempoMinimoCumplido) {
                btnAceptar.disabled = false;
                btnAceptar.style.opacity = '1';
                btnAceptar.style.cursor = 'pointer';
                btnAceptar.classList.remove('disabled');
            } else {
                btnAceptar.disabled = true;
                btnAceptar.style.opacity = '0.6';
                btnAceptar.style.cursor = 'not-allowed';
                btnAceptar.classList.add('disabled');
            }
        }
        
        // ============================================
        // 4. FUNCIÓN PARA ACEPTAR TÉRMINOS
        // ============================================
        function aceptarTerminos() {
            if (btnAceptar.disabled) return;
            
            // Guardar en sessionStorage para usar en el registro
            sessionStorage.setItem('tulook_terminos_aceptados', 'true');
            sessionStorage.setItem('tulook_fecha_aceptacion', new Date().toISOString());
            
            // Mostrar modal de confirmación
            confirmacionModal.style.display = 'flex';
        }
        
        // ============================================
        // 5. CERRAR MODAL Y CONTINUAR
        // ============================================
        function cerrarModalYContinuar() {
            confirmacionModal.style.display = 'none';
            
            // Redirigir al login con parámetro para mostrar formulario de registro
            window.location.href = '<?php echo BASE_URL; ?>?c=Usuario&a=login&registro=1&terminos=aceptados';
        }
        
        // ============================================
        // 6. FUNCIONES PARA MODAL DE SALIDA
        // ============================================
        function mostrarModalSalida() {
            if (modalSalida) {
                modalSalida.style.display = 'flex';
            }
        }
        
        function cerrarModalSalida() {
            if (modalSalida) {
                modalSalida.style.display = 'none';
            }
        }
        
        function confirmarSalida() {
            salidaConfirmada = true;
            cerrarModalSalida();
            
            // Redirigir al login sin parámetros
            window.location.href = '<?php echo BASE_URL; ?>?c=Usuario&a=login';
        }
        
        // ============================================
        // 7. INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            // Evento de scroll para progreso de lectura
            if (terminosContent) {
                terminosContent.addEventListener('scroll', actualizarProgresoLectura);
                setTimeout(actualizarProgresoLectura, 100);
            }
            
            // Evento para checkbox
            if (aceptarCheckbox) {
                aceptarCheckbox.addEventListener('change', function(e) {
                    validarAceptacion();
                });
                
                // También permitir hacer clic en el label para marcar el checkbox
                const label = document.querySelector('label[for="aceptarTerminosCheckbox"]');
                if (label) {
                    label.addEventListener('click', function() {
                        setTimeout(validarAceptacion, 50);
                    });
                }
            }
            
            // Evento para botón aceptar
            if (btnAceptar) {
                btnAceptar.addEventListener('click', function(e) {
                    e.preventDefault();
                    aceptarTerminos();
                });
            }
            
            // Verificar tiempo mínimo cada segundo
            setInterval(verificarTiempoMinimo, 1000);
            
            // Validar estado inicial
            validarAceptacion();
            
            // Cerrar modal con ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    if (confirmacionModal && confirmacionModal.style.display === 'flex') {
                        confirmacionModal.style.display = 'none';
                    }
                    if (modalSalida && modalSalida.style.display === 'flex') {
                        cerrarModalSalida();
                    }
                }
            });
            
            // Cerrar modal al hacer clic fuera
            window.addEventListener('click', function(event) {
                if (event.target === confirmacionModal) {
                    confirmacionModal.style.display = 'none';
                }
                if (event.target === modalSalida) {
                    cerrarModalSalida();
                }
            });
        });

        // Asegurar que el checkbox personalizado funcione correctamente
        document.addEventListener('click', function(e) {
            const customCheckbox = e.target.closest('.custom-checkbox');
            if (customCheckbox) {
                const checkbox = customCheckbox.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    const event = new Event('change', { bubbles: true });
                    checkbox.dispatchEvent(event);
                }
            }
        });
    </script>
</body>
</html>