<?php
require_once "models/Database.php";
require_once "models/UsuarioAdmin.php";

class UsuarioAdminController {
    private $db;
    private $usuarioModel;

    public function __construct() {
        $dbObj = new Database();
        $this->db = $dbObj->getConnection();
        $this->usuarioModel = new UsuarioAdmin($this->db);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ensureAdmin();
    }

    private function ensureAdmin() {
        // SOLO rol 1 (Administrador) puede acceder
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }
    }

    // 📋 LISTAR USUARIOS CON BÚSQUEDA
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $rol = $_GET['rol'] ?? '';
        
        // Validar y convertir estado
        if ($estado === 'activo') {
            $filtroEstado = 1;
        } elseif ($estado === 'inactivo') {
            $filtroEstado = 0;
        } else {
            $filtroEstado = '';
        }

        if (!empty($termino) || $filtroEstado !== '' || !empty($rol)) {
            $usuarios = $this->usuarioModel->buscar($termino, $filtroEstado, $rol);
            $modoBusqueda = true;
        } else {
            $usuarios = $this->usuarioModel->obtenerTodos();
            $modoBusqueda = false;
        }

        $roles = $this->usuarioModel->obtenerRoles();
        include "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO USUARIO
    public function crear() {
        $roles = $this->usuarioModel->obtenerRoles();
        $tiposDocumento = $this->usuarioModel->obtenerTiposDocumento();
        
        // Cargar datos guardados de sesión si existen
        $formData = $_SESSION['form_data'] ?? [];
        
        include "views/admin/layout_admin.php";
        
        // Limpiar datos de sesión después de usarlos (solo si venimos de un error)
        if (isset($_SESSION['form_data']) && isset($_SESSION['mensaje'])) {
            unset($_SESSION['form_data']);
        }
    }

    // 💾 GUARDAR NUEVO USUARIO (CON VALIDACIONES ESPECÍFICAS)
    public function guardar() {
        try {
            // Limpiar y validar datos
            $datos = [
                'ID_TD' => (int)($_POST['ID_TD'] ?? 0),
                'N_Documento' => (int)($_POST['N_Documento'] ?? 0),
                'Nombre' => trim($_POST['Nombre'] ?? ''),
                'Apellido' => trim($_POST['Apellido'] ?? ''),
                'Correo' => trim($_POST['Correo'] ?? ''),
                'Celular' => trim($_POST['Celular'] ?? ''),
                'Password' => $_POST['Password'] ?? '',
                'ID_Rol' => (int)($_POST['ID_Rol'] ?? 0)
            ];

            // Validaciones básicas
            $errores = [];
            
            if ($datos['ID_TD'] <= 0) {
                $errores[] = "Debes seleccionar un tipo de documento";
            }
            
            if ($datos['N_Documento'] <= 0) {
                $errores[] = "El número de documento es obligatorio";
            } elseif ($datos['N_Documento'] < 100000000 || $datos['N_Documento'] > 9999999999) {
                $errores[] = "El documento debe tener 9-10 dígitos";
            }
            
            if (empty($datos['Nombre'])) {
                $errores[] = "El nombre es obligatorio";
            } elseif (strlen($datos['Nombre']) < 2) {
                $errores[] = "El nombre debe tener al menos 2 caracteres";
            }
            
            if (empty($datos['Apellido'])) {
                $errores[] = "El apellido es obligatorio";
            } elseif (strlen($datos['Apellido']) < 2) {
                $errores[] = "El apellido debe tener al menos 2 caracteres";
            }
            
            if (empty($datos['Correo'])) {
                $errores[] = "El correo es obligatorio";
            } elseif (!filter_var($datos['Correo'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El correo electrónico no es válido";
            }
            
            if (empty($datos['Celular'])) {
                $errores[] = "El celular es obligatorio";
            } elseif (!preg_match('/^\d{10}$/', $datos['Celular'])) {
                $errores[] = "El celular debe tener 10 dígitos";
            }
            
            if (empty($datos['Password'])) {
                $errores[] = "La contraseña es obligatoria";
            } elseif (strlen($datos['Password']) < 6) {
                $errores[] = "La contraseña debe tener al menos 6 caracteres";
            }

            // CDRA4 COMENTAR ESTA SECCIÓN PARA PERMITIR ASIGNAR ROL DE ADMINISTRADOR O QUITAR COMENTARIO DE LA SECCIÓN PARA NO PERMITIR EL ROL ADMIN

            // INICIO SECCIÓN
            /*
            // Validar que el rol sea solo 2 (Editor) o 3 (Cliente) - NO SE PUEDE ASIGNAR ROL 1
            if ($datos['ID_Rol'] != 2 && $datos['ID_Rol'] != 3) {
                $errores[] = "Solo puedes asignar roles de Editor o Cliente";
            }
            */
            // FIN DE SECCIÓN

            // CDRA5 QUITAR COMENTARIO DE LA SECCIÓN PARA PERMITIR ASIGNAR ROL DE ADMINISTRADOR O COMENTAR LA SECCIÓN PARA NO PERMITIR EL ROL ADMIN

            // INICIO SECCIÓN

            // Validar que el rol sea válido (1, 2 o 3)
            if ($datos['ID_Rol'] != 1 && $datos['ID_Rol'] != 2 && $datos['ID_Rol'] != 3) {
                $errores[] = "Solo puedes asignar roles de Administrador, Editor o Cliente";
            }
            
            // FIN DE SECCIÓN

            // Si hay errores, regresar al formulario
            if (!empty($errores)) {
                $_SESSION['mensaje'] = "❌ " . implode("<br>• ", $errores);
                $_SESSION['mensaje_tipo'] = "danger";
                
                // Guardar los datos ingresados para no perderlos
                $_SESSION['form_data'] = [
                    'ID_TD' => $_POST['ID_TD'] ?? '',
                    'N_Documento' => $_POST['N_Documento'] ?? '',
                    'Nombre' => $_POST['Nombre'] ?? '',
                    'Apellido' => $_POST['Apellido'] ?? '',
                    'Correo' => $_POST['Correo'] ?? '',
                    'Celular' => $_POST['Celular'] ?? '',
                    'ID_Rol' => $_POST['ID_Rol'] ?? ''
                ];
                
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=crear");
                exit;
            }

            // VERIFICACIONES ESPECÍFICAS INDIVIDUALES
            $erroresEspecificos = [];
            
            // Verificar documento
            if ($this->usuarioModel->existeDocumento($datos['N_Documento'])) {
                $usuarioDoc = $this->usuarioModel->obtenerPorDocumento($datos['N_Documento']);
                $nombreUsuario = $usuarioDoc ? "{$usuarioDoc['Nombre']} {$usuarioDoc['Apellido']}" : "otro usuario";
                $erroresEspecificos[] = "❌ El número de documento <strong>{$datos['N_Documento']}</strong> ya está registrado por el usuario: <strong>{$nombreUsuario}</strong>";
            }
            
            // Verificar email
            if ($this->usuarioModel->existeEmail($datos['Correo'])) {
                $usuarioEmail = $this->usuarioModel->obtenerPorEmail($datos['Correo']);
                $nombreUsuario = $usuarioEmail ? "{$usuarioEmail['Nombre']} {$usuarioEmail['Apellido']}" : "otro usuario";
                $erroresEspecificos[] = "❌ El correo electrónico <strong>{$datos['Correo']}</strong> ya está registrado por el usuario: <strong>{$nombreUsuario}</strong>";
            }
            
            // Verificar celular
            if ($this->usuarioModel->existeCelular($datos['Celular'])) {
                $usuarioCel = $this->usuarioModel->obtenerPorCelular($datos['Celular']);
                $nombreUsuario = $usuarioCel ? "{$usuarioCel['Nombre']} {$usuarioCel['Apellido']}" : "otro usuario";
                $erroresEspecificos[] = "❌ El número de celular <strong>{$datos['Celular']}</strong> ya está registrado por el usuario: <strong>{$nombreUsuario}</strong>";
            }
            
            // Si hay errores específicos
            if (!empty($erroresEspecificos)) {
                $_SESSION['mensaje'] = implode("<br><br>", $erroresEspecificos);
                $_SESSION['mensaje_tipo'] = "danger";
                
                // Guardar datos del formulario
                $_SESSION['form_data'] = [
                    'ID_TD' => $_POST['ID_TD'] ?? '',
                    'N_Documento' => $_POST['N_Documento'] ?? '',
                    'Nombre' => $_POST['Nombre'] ?? '',
                    'Apellido' => $_POST['Apellido'] ?? '',
                    'Correo' => $_POST['Correo'] ?? '',
                    'Celular' => $_POST['Celular'] ?? '',
                    'ID_Rol' => $_POST['ID_Rol'] ?? ''
                ];
                
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=crear");
                exit;
            }

            // Crear el usuario
            $resultado = $this->usuarioModel->crear($datos);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Usuario creado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
                
                // Limpiar datos del formulario guardados
                unset($_SESSION['form_data']);
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear el usuario. Por favor, intenta nuevamente.";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;

        } catch (Exception $e) {
            // Manejar errores específicos del modelo
            $mensajeError = $e->getMessage();
            
            switch($mensajeError) {
                case 'documento_existente':
                    $usuarioDoc = $this->usuarioModel->obtenerPorDocumento($datos['N_Documento']);
                    $nombreUsuario = $usuarioDoc ? "{$usuarioDoc['Nombre']} {$usuarioDoc['Apellido']}" : "otro usuario";
                    $_SESSION['mensaje'] = "❌ El número de documento <strong>{$datos['N_Documento']}</strong> ya está registrado por el usuario: <strong>{$nombreUsuario}</strong>";
                    break;
                    
                case 'email_existente':
                    $usuarioEmail = $this->usuarioModel->obtenerPorEmail($datos['Correo']);
                    $nombreUsuario = $usuarioEmail ? "{$usuarioEmail['Nombre']} {$usuarioEmail['Apellido']}" : "otro usuario";
                    $_SESSION['mensaje'] = "❌ El correo electrónico <strong>{$datos['Correo']}</strong> ya está registrado por el usuario: <strong>{$nombreUsuario}</strong>";
                    break;
                    
                case 'celular_existente':
                    $usuarioCel = $this->usuarioModel->obtenerPorCelular($datos['Celular']);
                    $nombreUsuario = $usuarioCel ? "{$usuarioCel['Nombre']} {$usuarioCel['Apellido']}" : "otro usuario";
                    $_SESSION['mensaje'] = "❌ El número de celular <strong>{$datos['Celular']}</strong> ya está registrado por el usuario: <strong>{$nombreUsuario}</strong>";
                    break;
                    
                default:
                    $_SESSION['mensaje'] = "⚠ " . $mensajeError;
            }
            
            $_SESSION['mensaje_tipo'] = "danger";
            
            // Guardar datos del formulario
            $_SESSION['form_data'] = [
                'ID_TD' => $_POST['ID_TD'] ?? '',
                'N_Documento' => $_POST['N_Documento'] ?? '',
                'Nombre' => $_POST['Nombre'] ?? '',
                'Apellido' => $_POST['Apellido'] ?? '',
                'Correo' => $_POST['Correo'] ?? '',
                'Celular' => $_POST['Celular'] ?? '',
                'ID_Rol' => $_POST['ID_Rol'] ?? ''
            ];
            
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=crear");
            exit;
        }
    }

    // ACTIVAR/DESACTIVAR USUARIO CON MOTIVO
    public function cambiarEstado() {
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);
        
        // El motivo puede venir por POST (desde modal) o estar vacío (activación)
        $motivo = trim($_POST['motivo'] ?? '');

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de usuario inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // No permitir desactivarse a sí mismo
        if ($id == $_SESSION['ID_Usuario']) {
            $_SESSION['mensaje'] = "❌ No puedes desactivar tu propio usuario";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // No permitir desactivar administradores (rol 1)
        $usuario = $this->usuarioModel->obtenerPorId($id);
        if ($usuario && $usuario['ID_Rol'] == 1 && $estado == 0) {
            $_SESSION['mensaje'] = "❌ No puedes desactivar a un administrador";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // Validaciones específicas para desactivación
        if ($estado == 0) {
            // Si es desactivación, el motivo es obligatorio
            if (empty($motivo)) {
                $_SESSION['mensaje'] = "❌ El motivo de desactivación es obligatorio";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Validar longitud mínima
            if (strlen($motivo) < 20) {
                $_SESSION['mensaje'] = "❌ El motivo debe tener al menos 20 caracteres";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Validar longitud máxima
            if (strlen($motivo) > 500) {
                $_SESSION['mensaje'] = "❌ El motivo no puede exceder los 500 caracteres";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Validar que no sea solo espacios
            $motivoSinEspacios = preg_replace('/\s+/', '', $motivo);
            if (strlen($motivoSinEspacios) < 10) {
                $_SESSION['mensaje'] = "❌ El motivo es demasiado corto. Por favor, proporciona más detalles.";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Validar que tenga contenido real (no solo caracteres especiales)
            $motivoLimpio = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/', '', $motivo);
            if (strlen(trim($motivoLimpio)) < 15) {
                $_SESSION['mensaje'] = "❌ El motivo debe contener texto significativo";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Validar que no sea texto repetitivo
            if ($this->esTextoRepetitivo($motivo)) {
                $_SESSION['mensaje'] = "❌ El motivo parece contener texto repetitivo. Por favor, escribe un motivo más específico.";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Formatear el motivo antes de guardar
            $motivo = $this->formatearMotivo($motivo);
        } else {
            // Si es activación, limpiar motivo
            $motivo = '';
        }

        // Cambiar estado con motivo
        $adminId = $_SESSION['ID_Usuario'];
        $resultado = $this->usuarioModel->cambiarEstado($id, $estado, $motivo, $adminId);

        if ($resultado) {
            $_SESSION['mensaje'] = $estado ? "✅ Usuario activado correctamente" : "✅ Usuario desactivado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al cambiar el estado del usuario";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
        exit;
    }

    private function esTextoRepetitivo($texto) {
        // Limpiar texto
        $textoLimpio = preg_replace('/\s+/', ' ', trim($texto));
        
        // Dividir en palabras
        $palabras = explode(' ', $textoLimpio);
        
        // Contar frecuencia de palabras
        $frecuencias = array_count_values($palabras);
        
        // Si hay palabras que se repiten más del 50% del tiempo
        $totalPalabras = count($palabras);
        foreach ($frecuencias as $palabra => $frecuencia) {
            if ($frecuencia / $totalPalabras > 0.5 && strlen($palabra) > 3) {
                return true;
            }
        }
        
        // Verificar patrones repetitivos de caracteres
        if (preg_match('/(.)\1{10,}/', $texto)) {
            return true;
        }
        
        return false;
    }

    // Función para formatear el motivo
    private function formatearMotivo($texto) {
        // 1. Limpiar espacios extras
        $formateado = trim($texto);
        
        // 2. Reemplazar múltiples espacios por uno solo
        $formateado = preg_replace('/\s+/', ' ', $formateado);
        
        // 3. Reemplazar múltiples saltos de línea por máximo 2
        $formateado = preg_replace('/\n{3,}/', "\n\n", $formateado);
        
        // 4. Capitalizar primera letra
        $formateado = ucfirst($formateado);
        
        // 5. Asegurar que termine con punto
        if (!preg_match('/[.!?]$/', $formateado)) {
            $formateado .= '.';
        }
        
        // 6. Limitar longitud (por si acaso)
        if (strlen($formateado) > 500) {
            $formateado = substr($formateado, 0, 497) . '...';
        }
        
        return $formateado;
    }

    // 👑 CAMBIAR ROL DE USUARIO
    public function cambiarRol() {
        $id = (int)($_POST['ID_Usuario'] ?? 0);
        $rol = (int)($_POST['ID_Rol'] ?? 2);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "ID de usuario inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // No permitir cambiar el rol de sí mismo
        if ($id == $_SESSION['ID_Usuario']) {
            $_SESSION['mensaje'] = "No puedes cambiar tu propio rol";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // No permitir cambiar el rol de administradores (rol 1)
        $usuario = $this->usuarioModel->obtenerPorId($id);
        if ($usuario && $usuario['ID_Rol'] == 1) {
            $_SESSION['mensaje'] = "No puedes cambiar el rol de un administrador";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // VALIDACIÓN PARA ASCENSO A ADMINISTRADOR
        // Si está intentando ascender a Administrador (rol 1)
        if ($rol == 1) {
            // Verificar que el usuario actual sea Editor (rol 2)
            if ($usuario['ID_Rol'] != 2) {
                $_SESSION['mensaje'] = "Solo los Editores pueden ser promovidos a Administradores. El usuario debe ser Editor primero.";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
            
            // Registrar en logs o auditoría
            error_log("PROMOCIÓN A ADMIN: Usuario ID {$id} ({$usuario['Nombre']} {$usuario['Apellido']}) promovido a Administrador por usuario ID {$_SESSION['ID_Usuario']}");
        }
        
        // Si está intentando bajar de Administrador a otro rol (prevención adicional)
        if ($usuario['ID_Rol'] == 1 && $rol != 1) {
            $_SESSION['mensaje'] = "No se puede degradar a un Administrador. Esta acción es irreversible.";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // CDRA6 COMENTAR ESTA SECCIÓN PARA PERMITIR ASIGNAR ROL DE ADMINISTRADOR O QUITAR COMENTARIO DE LA SECCIÓN PARA NO PERMITIR EL ROL ADMIN
        
        // INICIO SECCIÓN
        /*
        // Validar que no se asigne el rol de Administrador (1)
        if ($rol == 1) {
            $_SESSION['mensaje'] = "No se puede asignar el rol de Administrador";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }
        */
        // FIN DE SECCIÓN

        
        //CDRA7 QUITAR COMENTARIO DE LA SECCIÓN PARA PERMITIR ASIGNAR ROL DE ADMINISTRADOR O COMENTAR LA SECCIÓN PARA NO PERMITIR EL ROL ADMIN
        
        // INICIO SECCIÓN

        // Validar que sea un rol válido (1, 2 o 3)
        if ($rol != 1 && $rol != 2 && $rol != 3) {
            $_SESSION['mensaje'] = "Rol no válido. Solo se permiten Administrador, Editor o Cliente";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }
        
        // FIN DE SECCIÓN

        // CDRA8 COMENTAR ESTA SECCIÓN PARA PERMITIR ASIGNAR ROL DE ADMINISTRADOR O QUITAR COMENTARIO DE LA SECCIÓN PARA NO PERMIR EL ROL ADMIN
        
        // INICIO SECCIÓN
        /*
        // Validar que sea un rol válido (2 o 3)
        if ($rol != 2 && $rol != 3) {
            $_SESSION['mensaje'] = "Rol no válido. Solo se permiten Editor o Cliente";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }
        */
        // FIN DE SECCIÓN

        $resultado = $this->usuarioModel->cambiarRol($id, $rol);

        if ($resultado) {
            $_SESSION['mensaje'] = "Rol de usuario actualizado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al cambiar el rol del usuario";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
        exit;
    }

    // 🔍 VERIFICAR DOCUMENTO (AJAX)
    public function verificarDocumento() {
        $documento = (int)($_GET['documento'] ?? 0);
        
        if ($documento <= 0) {
            echo json_encode(['existe' => false]);
            exit;
        }
        
        $existe = $this->usuarioModel->existeDocumento($documento);
        $usuarioInfo = '';
        
        if ($existe) {
            $usuario = $this->usuarioModel->obtenerPorDocumento($documento);
            $usuarioInfo = $usuario ? "{$usuario['Nombre']} {$usuario['Apellido']}" : "otro usuario";
        }
        
        echo json_encode([
            'existe' => $existe,
            'usuario' => $usuarioInfo
        ]);
        exit;
    }

    // 📧 VERIFICAR EMAIL (AJAX)
    public function verificarEmail() {
        $email = trim($_GET['email'] ?? '');
        
        if (empty($email)) {
            echo json_encode(['existe' => false]);
            exit;
        }
        
        $existe = $this->usuarioModel->existeEmail($email);
        $usuarioInfo = '';
        
        if ($existe) {
            $usuario = $this->usuarioModel->obtenerPorEmail($email);
            $usuarioInfo = $usuario ? "{$usuario['Nombre']} {$usuario['Apellido']}" : "otro usuario";
        }
        
        echo json_encode([
            'existe' => $existe,
            'usuario' => $usuarioInfo
        ]);
        exit;
    }

    // 📱 VERIFICAR CELULAR (AJAX)
    public function verificarCelular() {
        $celular = trim($_GET['celular'] ?? '');
        
        if (empty($celular)) {
            echo json_encode(['existe' => false]);
            exit;
        }
        
        $existe = $this->usuarioModel->existeCelular($celular);
        $usuarioInfo = '';
        
        if ($existe) {
            $usuario = $this->usuarioModel->obtenerPorCelular($celular);
            $usuarioInfo = $usuario ? "{$usuario['Nombre']} {$usuario['Apellido']}" : "otro usuario";
        }
        
        echo json_encode([
            'existe' => $existe,
            'usuario' => $usuarioInfo
        ]);
        exit;
    }
}
?>