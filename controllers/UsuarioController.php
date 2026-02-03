<?php
// controllers/UsuarioController.php
require_once "models/Usuario.php";
require_once "models/Database.php";
require_once "controllers/BaseController.php"; 

class UsuarioController extends BaseController { 
    private $usuario;

    public function __construct($db = null) {
        parent::__construct($db); 
        $this->usuario = new Usuario($this->db);
        $this->iniciarSesion();
    }

    private function iniciarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Mostrar página de errores con detalles
    public function mostrarError($mensaje, $detalles = [], $tipoError = 'general', $mostrarRegistro = false) {
        $_SESSION['error_message'] = $mensaje;
        $_SESSION['error_type'] = $tipoError;
        $_SESSION['mostrar_registro'] = $mostrarRegistro; // Nueva variable para controlar qué formulario mostrar
        
        if (!empty($detalles)) {
            $_SESSION['error_details'] = $detalles;
        }
        header("Location: " . BASE_URL . "?c=Usuario&a=login");
        exit;
    }

    // Validaciones mejoradas
    private function validarNombre($nombre) {
        // Solo letras, espacios y acentos, entre 2 y 50 caracteres
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/', $nombre);
    }

    private function validarApellido($apellido) {
        // Solo letras, espacios y acentos, entre 2 y 50 caracteres
        return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,50}$/', $apellido);
    }

    private function validarCorreo($correo) {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validarCelular($celular) {
        // Solo números, exactamente 10 dígitos
        return preg_match('/^[0-9]{10}$/', $celular);
    }

    private function validarDocumento($documento) {
        // Solo números, entre 7 y 12 dígitos
        return preg_match('/^[0-9]{7,12}$/', $documento);
    }

    private function validarContrasena($contrasena) {
        // Mínimo 12 caracteres, al menos 2 números, 1 símbolo, 1 mayúscula y 1 minúscula
        $errores = [];
        
        if (strlen($contrasena) < 12) {
            $errores[] = "Mínimo 12 caracteres";
        }
        
        // Contar números
        if (preg_match_all('/[0-9]/', $contrasena) < 2) {
            $errores[] = "Al menos 2 números";
        }
        
        // Verificar símbolos (caracteres especiales)
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $contrasena)) {
            $errores[] = "Al menos 1 símbolo especial (!@#$% etc.)";
        }
        
        // Verificar mayúsculas
        if (!preg_match('/[A-Z]/', $contrasena)) {
            $errores[] = "Al menos 1 letra mayúscula";
        }
        
        // Verificar minúsculas
        if (!preg_match('/[a-z]/', $contrasena)) {
            $errores[] = "Al menos 1 letra minúscula";
        }
        
        return empty($errores) ? true : $errores;
    }

    private function sanitizarTexto($texto) {
        // Eliminar etiquetas HTML y espacios extras
        $texto = strip_tags($texto);
        $texto = trim($texto);
        return $texto;
    }

    // Verificar si datos ya existen
    private function verificarDatosExistentes($correo, $documento, $celular) {
        $errores = [];
        
        // Verificar correo
        if ($this->usuario->existeCorreo($correo)) {
            $errores[] = "El correo electrónico '$correo' ya está registrado";
        }
        
        // Verificar documento
        if ($this->usuario->existeDocumento($documento)) {
            $errores[] = "El número de documento '$documento' ya está registrado";
        }
        
        // Verificar celular
        if ($this->usuario->existeCelular($celular)) {
            $errores[] = "El número de celular '$celular' ya está registrado";
        }
        
        return $errores;
    }

    // Registro de usuario (actualizado con validaciones específicas)
    public function registrar() {
        // VERIFICACIÓN: Si el usuario ya está logueado, redirigir
        if (isset($_SESSION['usuario'])) {
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            header("Location: " . BASE_URL);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Sanitizar y validar datos
                $nombre = $this->sanitizarTexto($_POST['Nombre'] ?? '');
                $apellido = $this->sanitizarTexto($_POST['Apellido'] ?? '');
                $id_td = (int)($_POST['ID_TD'] ?? 0);
                $n_documento = $_POST['N_Documento'] ?? '';
                $correo = filter_var($_POST['Correo'] ?? '', FILTER_SANITIZE_EMAIL);
                $celular = $_POST['Celular'] ?? '';
                $contrasena = $_POST['Contrasena'] ?? '';
                $confirmar_contrasena = $_POST['Confirmar_Contrasena'] ?? '';

                $errores_validacion = [];

                // Validaciones individuales
                if (!$this->validarNombre($nombre)) {
                    $errores_validacion[] = "El nombre solo puede contener letras y espacios (2-50 caracteres)";
                }

                if (!$this->validarApellido($apellido)) {
                    $errores_validacion[] = "El apellido solo puede contener letras y espacios (2-50 caracteres)";
                }

                if ($id_td <= 0) {
                    $errores_validacion[] = "Seleccione un tipo de documento válido";
                }

                if (!$this->validarDocumento($n_documento)) {
                    $errores_validacion[] = "El documento debe contener solo números (7-12 dígitos)";
                }

                if (!$this->validarCorreo($correo)) {
                    $errores_validacion[] = "Ingrese un correo electrónico válido (ejemplo: usuario@dominio.com)";
                }

                if (!$this->validarCelular($celular)) {
                    $errores_validacion[] = "El celular debe contener exactamente 10 dígitos numéricos";
                }

                // Validar contraseña con detalles específicos
                $validacion_contrasena = $this->validarContrasena($contrasena);
                if ($validacion_contrasena !== true) {
                    $errores_validacion[] = "La contraseña no cumple con los requisitos:";
                    foreach ($validacion_contrasena as $error_contra) {
                        $errores_validacion[] = "• " . $error_contra;
                    }
                }

                if ($contrasena !== $confirmar_contrasena) {
                    $errores_validacion[] = "Las contraseñas no coinciden";
                }

                // Si hay errores de validación básica, mostrarlos
                if (!empty($errores_validacion)) {
                    $this->mostrarError("Errores en el formulario:", $errores_validacion, 'registro', true);
                }

                // Verificar si los datos ya existen
                $datos_existentes = $this->verificarDatosExistentes($correo, $n_documento, $celular);
                if (!empty($datos_existentes)) {
                    $this->mostrarError("Datos ya registrados en el sistema:", $datos_existentes, 'registro', true);
                }

                // Preparar datos para inserción
                $data = [
                    'Nombre' => $nombre,
                    'Apellido' => $apellido,
                    'ID_TD' => $id_td,
                    'N_Documento' => $n_documento,
                    'Correo' => $correo,
                    'Celular' => $celular,
                    'Contrasena' => password_hash($contrasena, PASSWORD_BCRYPT),
                    'ID_Rol' => 3 // cliente por defecto
                ];

                // Registrar usuario
                if ($this->usuario->registrar($data)) {
                    $_SESSION['success_registro'] = "Usuario registrado con éxito. Ahora inicia sesión.";
                    header("Location: " . BASE_URL . "?c=Usuario&a=login");
                    exit;
                } else {
                    $this->mostrarError("Error al registrar usuario en la base de datos. Intente nuevamente.", [], 'registro', true);
                }

            } catch (Exception $e) {
                $this->mostrarError("Error interno del sistema: " . $e->getMessage(), [], 'registro', true);
            }
        } else {
            $this->mostrarError("Método no permitido. Use el formulario de registro.", [], 'registro', true);
        }
    }

    // Login
    public function login() {
        // NUEVA VERIFICACIÓN: Si el usuario ya está logueado, redirigir
        if (isset($_SESSION['usuario'])) {
            // Redirección según el rol
            if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1) { // Administrador
                header("Location: " . BASE_URL . "?c=Admin&a=index");
            } else {
                header("Location: " . BASE_URL);
            }
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $correo = filter_var($_POST['Correo'] ?? '', FILTER_SANITIZE_EMAIL);
                $contrasena = $_POST['Contrasena'] ?? '';

                // Validaciones básicas
                $errores_login = [];
                
                if (empty($correo)) {
                    $errores_login[] = "El correo electrónico es obligatorio";
                }
                
                if (empty($contrasena)) {
                    $errores_login[] = "La contraseña es obligatoria";
                }

                if (!$this->validarCorreo($correo)) {
                    $errores_login[] = "Ingrese un correo electrónico válido";
                }

                if (!empty($errores_login)) {
                    $this->mostrarError("Errores en el inicio de sesión:", $errores_login);
                }

                // Verificar si el correo existe
                if (!$this->usuario->existeCorreo($correo)) {
                    $this->mostrarError("El correo electrónico '$correo' no está registrado en nuestro sistema.");
                }

                // Verificar si el usuario está activo
                $stmt = $this->db->prepare("SELECT * FROM usuario WHERE Correo = ? AND activo = 0 LIMIT 1");
                $stmt->execute([$correo]);
                $userInactivo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userInactivo) {
                    $this->mostrarError("Su cuenta está desactivada. Por favor, contacte al administrador para más información.");
                }

                // Intentar login
                $user = $this->usuario->login($correo, $contrasena);

                if ($user) {
                // Guardar datos del usuario en sesión
                $_SESSION['ID_Usuario'] = $user['ID_Usuario'];
                $_SESSION['usuario'] = $user['Correo'];
                $_SESSION['rol'] = $user['ID_Rol'];
                $_SESSION['Nombre'] = $user['Nombre'];
                $_SESSION['Apellido'] = $user['Apellido'];
                $_SESSION['Nombre_Completo'] = $user['Nombre'] . ' ' . $user['Apellido'];
                $_SESSION['login_time'] = time(); // IMPORTANTE: Guardar timestamp

                // Obtener nombre del rol
                $stmtRol = $this->db->prepare("SELECT Roles FROM rol WHERE ID_Rol = ?");
                $stmtRol->execute([(int)$user['ID_Rol']]);
                $rolName = $stmtRol->fetchColumn();
                $_SESSION['RolName'] = $rolName;

                // PREVENIR CACHE EN LA REDIRECCIÓN
                header("Cache-Control: no-cache, no-store, must-revalidate");
                header("Pragma: no-cache");
                header("Expires: 0");
                
                // Redirección con parámetro único para evitar cache
                $randomParam = 't=' . time() . '&r=' . rand(1000, 9999);
                
                // MODIFICACIÓN AQUÍ: Ambos (admin y editor) van al dashboard
                if (strtolower($rolName) === 'administrador' || strtolower($rolName) === 'editor') {
                    header("Location: " . BASE_URL . "?c=Admin&a=index&" . $randomParam);
                } else {
                    header("Location: " . BASE_URL . "?" . $randomParam);
                }
                exit;
            } else {
                    $this->mostrarError("Contraseña incorrecta. Verifique sus credenciales.");
                }

            } catch (Exception $e) {
                $this->mostrarError("Error interno del sistema: " . $e->getMessage());
            }
        } else {
        // NUEVA VERIFICACIÓN: Si el usuario ya está logueado, redirigir (para GET también)
        if (isset($_SESSION['usuario'])) {
            if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1) { // Administrador
                header("Location: " . BASE_URL . "?c=Admin&a=index");
            } else {
                header("Location: " . BASE_URL);
            }
            exit;
        }
        
        // HEADERS PARA PREVENIR CACHE
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // Mostrar formulario de login
        $tipo_docs = $this->usuario->getTipoDocumentos();
        
        // Determinar si debemos mostrar el formulario de registro automáticamente
        $mostrarRegistro = isset($_SESSION['mostrar_registro']) ? $_SESSION['mostrar_registro'] : false;
        if (isset($_SESSION['mostrar_registro'])) {
            unset($_SESSION['mostrar_registro']);
        }
        
        include "views/usuario/login.php";
    }
}

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL);
    }

    // Perfil de usuario
    public function perfil() {
            if (!isset($_SESSION['usuario'])) {
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            }

            $correo = $_SESSION['usuario'];
            $stmt = $this->db->prepare("SELECT u.*, td.Documento 
                                        FROM usuario u
                                        INNER JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                                        WHERE u.Correo = :correo LIMIT 1");
            $stmt->bindParam(":correo", $correo, PDO::PARAM_STR);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            include "views/usuario/perfil.php";
        }

        // Mostrar formulario para cambiar contraseña
        public function cambiarContrasena() {
            if (!isset($_SESSION['usuario'])) {
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            }

            // Verificar si hay mensajes de éxito o error
            $mensaje = $_SESSION['cambio_contrasena_mensaje'] ?? '';
            $tipo_mensaje = $_SESSION['cambio_contrasena_tipo'] ?? '';
            
            // Limpiar mensajes después de mostrarlos
            unset($_SESSION['cambio_contrasena_mensaje']);
            unset($_SESSION['cambio_contrasena_tipo']);

            // Asegurarse de que las variables estén disponibles en la vista
            $data = [
                'mensaje' => $mensaje,
                'tipo_mensaje' => $tipo_mensaje
            ];
            
            // Incluir la vista
            include "views/usuario/cambiar_contrasena.php";
        }
// controllers/UsuarioController.php - Agregar estos métodos después del método actualizarContrasena()

// =============================
// MÉTODOS PARA GESTIONAR DIRECCIONES
// =============================

/**
 * Guardar o actualizar una dirección
 */
public function guardarDireccion() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: " . BASE_URL . "?c=Usuario&a=login");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            require_once "models/Direccion.php";
            $dirModel = new Direccion($this->db);
            
            $data = [
                'ID_Usuario' => $_SESSION['ID_Usuario'],
                'Direccion' => trim($_POST['Direccion'] ?? ''),
                'Ciudad' => trim($_POST['Ciudad'] ?? ''),
                'Departamento' => trim($_POST['Departamento'] ?? ''),
                'CodigoPostal' => trim($_POST['CodigoPostal'] ?? '')
            ];

            // Validaciones básicas
            if (empty($data['Direccion']) || empty($data['Ciudad']) || empty($data['Departamento'])) {
                $_SESSION['error_message'] = "Todos los campos son obligatorios";
                header("Location: " . BASE_URL . "?c=Usuario&a=perfil");
                exit;
            }

            $id_direccion = $_POST['ID_Direccion'] ?? null;

            if ($id_direccion) {
                // Actualizar dirección existente
                $data['ID_Direccion'] = $id_direccion;
                if ($dirModel->actualizar($data)) {
                    $_SESSION['success_message'] = "Dirección actualizada correctamente";
                }
            } else {
                // Crear nueva dirección
                if ($dirModel->crear($data)) {
                    $_SESSION['success_message'] = "Dirección guardada correctamente";
                }
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error al guardar la dirección: " . $e->getMessage();
        }
    }

    header("Location: " . BASE_URL . "?c=Usuario&a=perfil");
    exit;
}

/**
 * Eliminar una dirección
 */
public function eliminarDireccion() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: " . BASE_URL . "?c=Usuario&a=login");
        exit;
    }

    $id_direccion = $_GET['id'] ?? null;

    if ($id_direccion) {
        try {
            require_once "models/Direccion.php";
            $dirModel = new Direccion($this->db);
            
            // Verificar que la dirección pertenece al usuario
            if ($dirModel->perteneceAUsuario($id_direccion, $_SESSION['ID_Usuario'])) {
                if ($dirModel->eliminar($id_direccion)) {
                    $_SESSION['success_message'] = "Dirección eliminada correctamente";
                } else {
                    $_SESSION['error_message'] = "Error al eliminar la dirección";
                }
            } else {
                $_SESSION['error_message'] = "No tienes permisos para eliminar esta dirección";
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error al eliminar la dirección: " . $e->getMessage();
        }
    }

    header("Location: " . BASE_URL . "?c=Usuario&a=perfil");
    exit;
}

/**
 * Establecer dirección como predeterminada
 */
public function predeterminada() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: " . BASE_URL . "?c=Usuario&a=login");
        exit;
    }

    $id_direccion = $_GET['id'] ?? null;

    if ($id_direccion) {
        try {
            require_once "models/Direccion.php";
            $dirModel = new Direccion($this->db);
            
            // Verificar que la dirección pertenece al usuario
            if ($dirModel->perteneceAUsuario($id_direccion, $_SESSION['ID_Usuario'])) {
                if ($dirModel->establecerPredeterminada($id_direccion, $_SESSION['ID_Usuario'])) {
                    $_SESSION['success_message'] = "Dirección establecida como predeterminada";
                } else {
                    $_SESSION['error_message'] = "Error al establecer dirección predeterminada";
                }
            } else {
                $_SESSION['error_message'] = "No tienes permisos para modificar esta dirección";
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error al establecer dirección predeterminada: " . $e->getMessage();
        }
    }

    header("Location: " . BASE_URL . "?c=Usuario&a=perfil");
    exit;
}

// =============================
// MÉTODO INDEX FALTANTE
// =============================

/**
 * Método index - Redirige al perfil del usuario
 */
public function index() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: " . BASE_URL . "?c=Usuario&a=login");
        exit;
    }
    
    // Redirigir al perfil del usuario
    header("Location: " . BASE_URL . "?c=Usuario&a=perfil");
    exit;
}

// =============================
// MÉTODO PARA MOSTRAR MENSAJES EN EL PERFIL
// =============================

/**
 * Obtener mensajes de éxito/error para mostrar en el perfil
 */
private function obtenerMensajes() {
    $mensajes = [];
    
    if (isset($_SESSION['success_message'])) {
        $mensajes['success'] = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        $mensajes['error'] = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
    }
    
    return $mensajes;
}
        // Procesar cambio de contraseña
        public function actualizarContrasena() {
            if (!isset($_SESSION['usuario'])) {
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $id_usuario = $_SESSION['ID_Usuario'];
                    $contrasena_actual = $_POST['contrasena_actual'] ?? '';
                    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
                    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

                    $errores = [];

                    // Validaciones
                    if (empty($contrasena_actual)) {
                        $errores[] = "La contraseña actual es obligatoria";
                    }

                    if (empty($nueva_contrasena)) {
                        $errores[] = "La nueva contraseña es obligatoria";
                    }

                    if (empty($confirmar_contrasena)) {
                        $errores[] = "Confirmar contraseña es obligatorio";
                    }

                    if ($nueva_contrasena !== $confirmar_contrasena) {
                        $errores[] = "Las nuevas contraseñas no coinciden";
                    }

                    // Validar fortaleza de la nueva contraseña
                    $validacion_contrasena = $this->validarContrasena($nueva_contrasena);
                    if ($validacion_contrasena !== true) {
                        $errores[] = "La nueva contraseña no cumple con los requisitos de seguridad:";
                        foreach ($validacion_contrasena as $error_contra) {
                            $errores[] = "• " . $error_contra;
                        }
                    }

                    // Si hay errores, mostrar formulario con errores
                    if (!empty($errores)) {
                        $_SESSION['cambio_contrasena_mensaje'] = implode("<br>", $errores);
                        $_SESSION['cambio_contrasena_tipo'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=cambiarContrasena");
                        exit;
                    }

                    // Verificar contraseña actual
                    $stmt = $this->db->prepare("SELECT Contrasena FROM usuario WHERE ID_Usuario = ?");
                    $stmt->execute([$id_usuario]);
                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$usuario || !password_verify($contrasena_actual, $usuario['Contrasena'])) {
                        $_SESSION['cambio_contrasena_mensaje'] = "La contraseña actual es incorrecta";
                        $_SESSION['cambio_contrasena_tipo'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=cambiarContrasena");
                        exit;
                    }

                    // Verificar que la nueva contraseña no sea igual a la actual
                    if (password_verify($nueva_contrasena, $usuario['Contrasena'])) {
                        $_SESSION['cambio_contrasena_mensaje'] = "La nueva contraseña debe ser diferente a la actual";
                        $_SESSION['cambio_contrasena_tipo'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=cambiarContrasena");
                        exit;
                    }

                    // Actualizar contraseña
                    $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

                    if ($this->usuario->actualizarContrasena($id_usuario, $nueva_contrasena_hash)) {
                        $_SESSION['cambio_contrasena_mensaje'] = "Contraseña actualizada correctamente";
                        $_SESSION['cambio_contrasena_tipo'] = 'success';
                        
                        //Redirigir al formulario de cambio de contraseña, no al perfil
                        header("Location: " . BASE_URL . "?c=Usuario&a=cambiarContrasena");
                        exit;
                    } else {
                        throw new Exception("Error al actualizar la contraseña en la base de datos");
                    }

                } catch (Exception $e) {
                    $_SESSION['cambio_contrasena_mensaje'] = "Error interno del sistema: " . $e->getMessage();
                    $_SESSION['cambio_contrasena_tipo'] = 'error';
                    header("Location: " . BASE_URL . "?c=Usuario&a=cambiarContrasena");
                    exit;
                }
            } else {
                header("Location: " . BASE_URL . "?c=Usuario&a=cambiarContrasena");
                exit;
            }
        }

        public function checkAuthStatus() {
            // Headers para prevenir cache
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            header('Content-Type: application/json');
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $loggedIn = isset($_SESSION['ID_Usuario']) && !empty($_SESSION['ID_Usuario']);
            
            echo json_encode([
                'loggedIn' => $loggedIn,
                'timestamp' => time()
            ]);
            exit;
        }

        // Mostrar formulario de olvido de contraseña
        public function olvidoContrasena() {
            if (isset($_SESSION['usuario'])) {
                header("Location: " . BASE_URL);
                exit;
            }
            
            // HEADERS PARA PREVENIR CACHE
            header("Cache-Control: no-cache, no-store, must-revalidate");
            header("Pragma: no-cache");
            header("Expires: 0");
            
            include "views/usuario/olvido_contrasena.php";
        }

        // Solicitar reset de contraseña
        public function requestPasswordReset() {
            if (isset($_SESSION['usuario'])) {
                header("Location: " . BASE_URL);
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                    
                    if (empty($email) || !$this->validarCorreo($email)) {
                        $_SESSION['recovery_message'] = "Por favor ingresa un correo electrónico válido";
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                        exit;
                    }
                    
                    // Verificar si el usuario existe
                    if (!$this->usuario->existeCorreo($email)) {
                        // Por seguridad, mostramos mensaje genérico aunque el correo no exista
                        $_SESSION['recovery_message'] = "Si el correo está registrado, recibirás un enlace para restablecer tu contraseña";
                        $_SESSION['recovery_type'] = 'success';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                        exit;
                    }
                    
                    // Generar token único
                    $token = bin2hex(random_bytes(50));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Eliminar tokens anteriores para este email
                    $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()");
                    $stmt->execute([$email]);
                    
                    // Guardar nuevo token
                    $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$email, password_hash($token, PASSWORD_BCRYPT), $expires]);
                    
                    // Obtener nombre del usuario
                    $stmt = $this->db->prepare("SELECT Nombre FROM usuario WHERE Correo = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $userName = $user['Nombre'] ?? 'Usuario';
                    
                    // Crear enlace de recuperación
                    $resetLink = BASE_URL . "?c=Usuario&a=olvidoContrasena&token=" . urlencode($token) . "&email=" . urlencode($email);
                    
                    // Enviar correo (implementación básica - puedes usar tu clase Mailer)
                    $this->enviarEmailRecuperacion($email, $userName, $resetLink);
                    
                    $_SESSION['recovery_message'] = "Se ha enviado un enlace de recuperación a tu correo. Revisa también la carpeta de spam.";
                    $_SESSION['recovery_type'] = 'success';
                    header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                    exit;
                    
                } catch (Exception $e) {
                    error_log("Error en recuperación: " . $e->getMessage());
                    $_SESSION['recovery_message'] = "Error al procesar la solicitud. Intenta nuevamente.";
                    $_SESSION['recovery_type'] = 'error';
                    header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                    exit;
                }
            } else {
                header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                exit;
            }
        }

        // Resetear contraseña
        public function resetPassword() {
            if (isset($_SESSION['usuario'])) {
                header("Location: " . BASE_URL);
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $token = $_POST['token'] ?? '';
                    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
                    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
                    
                    // Validaciones básicas
                    if (empty($token) || empty($email) || empty($nueva_contrasena)) {
                        $_SESSION['recovery_message'] = "Datos incompletos";
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                        exit;
                    }
                    
                    if (!$this->validarCorreo($email)) {
                        $_SESSION['recovery_message'] = "Correo electrónico inválido";
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                        exit;
                    }
                    
                    // Verificar que las contraseñas coincidan
                    if ($nueva_contrasena !== $confirmar_contrasena) {
                        $_SESSION['recovery_message'] = "Las contraseñas no coinciden";
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena&token=" . urlencode($token) . "&email=" . urlencode($email));
                        exit;
                    }
                    
                    // Validar seguridad de la contraseña
                    $validacion_contrasena = $this->validarContrasena($nueva_contrasena);
                    if ($validacion_contrasena !== true) {
                        $errores = implode("\n• ", $validacion_contrasena);
                        $_SESSION['recovery_message'] = "La contraseña no cumple los requisitos:\n• " . $errores;
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena&token=" . urlencode($token) . "&email=" . urlencode($email));
                        exit;
                    }
                    
                    // Buscar token válido
                    $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE email = ? AND expires_at > NOW() AND used = 0");
                    $stmt->execute([$email]);
                    $resetTokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $tokenValido = false;
                    foreach ($resetTokens as $resetToken) {
                        if (password_verify($token, $resetToken['token'])) {
                            $tokenValido = true;
                            $tokenId = $resetToken['id'];
                            break;
                        }
                    }
                    
                    if (!$tokenValido) {
                        $_SESSION['recovery_message'] = "El enlace ha expirado o no es válido. Solicita uno nuevo.";
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                        exit;
                    }
                    
                    // Verificar que el usuario existe
                    $stmt = $this->db->prepare("SELECT ID_Usuario FROM usuario WHERE Correo = ? AND activo = 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user) {
                        $_SESSION['recovery_message'] = "Usuario no encontrado o cuenta desactivada";
                        $_SESSION['recovery_type'] = 'error';
                        header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                        exit;
                    }
                    
                    // Actualizar contraseña
                    $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
                    $stmt = $this->db->prepare("UPDATE usuario SET Contrasena = ? WHERE Correo = ?");
                    $success = $stmt->execute([$nueva_contrasena_hash, $email]);
                    
                    if ($success) {
                        // Marcar token como usado
                        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                        $stmt->execute([$tokenId]);
                        
                        // Eliminar todos los tokens de este email
                        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE email = ?");
                        $stmt->execute([$email]);
                        
                        $_SESSION['recovery_message'] = "¡Contraseña cambiada exitosamente! Ahora puedes iniciar sesión con tu nueva contraseña.";
                        $_SESSION['recovery_type'] = 'success';
                        header("Location: " . BASE_URL . "?c=Usuario&a=login");
                        exit;
                    } else {
                        throw new Exception("Error al actualizar la contraseña");
                    }
                    
                } catch (Exception $e) {
                    error_log("Error en reset password: " . $e->getMessage());
                    $_SESSION['recovery_message'] = "Error al restablecer la contraseña. Intenta nuevamente.";
                    $_SESSION['recovery_type'] = 'error';
                    header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                    exit;
                }
            } else {
                header("Location: " . BASE_URL . "?c=Usuario&a=olvidoContrasena");
                exit;
            }
        }

        // Método para enviar email (usando PHPMailer)
        private function enviarEmailRecuperacion($email, $nombre, $resetLink) {
            try {
                // Incluir PHPMailer y usar tu clase Mailer
                require_once __DIR__ . "/../vendor/autoload.php"; // Ajusta según tu estructura
                
                // Inicializar PHPMailer directamente
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // Obtener configuración de mail.php
                $configPath = __DIR__ . '/../config/mail.php';
                if (file_exists($configPath)) {
                    $mailConfig = require $configPath;
                } else {
                    // Configuración por defecto
                    $mailConfig = [
                        'host' => 'smtp.gmail.com',
                        'port' => 465,
                        'smtp_secure' => 'ssl',
                        'username' => 'looktu541@gmail.com', // Tu correo
                        'password' => 'tu_contraseña', // Tu contraseña
                        'from_email' => 'looktu541@gmail.com',
                        'from_name' => 'TuLook'
                    ];
                }
                
                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host = $mailConfig['host'] ?? 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $mailConfig['username'];
                $mail->Password = $mailConfig['password'];
                $mail->SMTPSecure = $mailConfig['smtp_secure'] ?? 'ssl';
                $mail->Port = $mailConfig['port'] ?? 465;
                
                // Configuración para XAMPP
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
                
                $mail->Timeout = 30;
                $mail->CharSet = 'UTF-8';
                
                // Remitente y destinatario
                $fromEmail = $mailConfig['from_email'] ?? 'looktu541@gmail.com';
                $fromName = $mailConfig['from_name'] ?? 'TuLook';
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($email, $nombre);
                
                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = "Recuperación de Contraseña - TuLook";
                
                // Cuerpo HTML del correo
                $mail->Body = "
                    <!DOCTYPE html>
                    <html lang='es'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.6;
                                color: #333;
                                margin: 0;
                                padding: 0;
                            }
                            .container {
                                max-width: 600px;
                                margin: 0 auto;
                                padding: 20px;
                            }
                            .header {
                                background: #2f3e53;
                                color: white;
                                padding: 20px;
                                text-align: center;
                                border-radius: 5px 5px 0 0;
                            }
                            .content {
                                padding: 30px;
                                background: #f8f9fa;
                                border: 1px solid #ddd;
                                border-top: none;
                                border-radius: 0 0 5px 5px;
                            }
                            .button {
                                display: inline-block;
                                background: #2f3e53;
                                color: white !important;
                                padding: 12px 24px;
                                text-decoration: none;
                                border-radius: 5px;
                                margin: 20px 0;
                                font-weight: bold;
                            }
                            .footer {
                                text-align: center;
                                padding: 20px;
                                color: #666;
                                font-size: 12px;
                                margin-top: 20px;
                            }
                            .warning {
                                background: #fff3cd;
                                border: 1px solid #ffc107;
                                padding: 15px;
                                border-radius: 5px;
                                margin: 20px 0;
                            }
                            .link-text {
                                word-break: break-all;
                                background: #eee;
                                padding: 10px;
                                border-radius: 5px;
                                margin: 10px 0;
                                font-size: 12px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>TuLook</h1>
                            </div>
                            <div class='content'>
                                <h2>Hola $nombre,</h2>
                                <p>Hemos recibido una solicitud para restablecer tu contraseña en TuLook.</p>
                                <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                                
                                <p style='text-align: center;'>
                                    <a href='$resetLink' class='button'>Restablecer Contraseña</a>
                                </p>
                                
                                <p>O copia y pega este enlace en tu navegador:</p>
                                <p class='link-text'>$resetLink</p>
                                
                                <div class='warning'>
                                    <p><strong>⚠️ Información importante:</strong></p>
                                    <ul>
                                        <li>Este enlace expirará en <strong>1 hora</strong></li>
                                        <li>Si no solicitaste este cambio, puedes ignorar este correo</li>
                                        <li>Tu contraseña actual permanecerá activa hasta que completes el proceso</li>
                                    </ul>
                                </div>
                                
                                <p>Si tienes problemas para restablecer tu contraseña o no solicitaste este cambio, 
                                contacta con nuestro equipo de soporte.</p>
                            </div>
                            <div class='footer'>
                                <p>© " . date('Y') . " TuLook. Todos los derechos reservados.</p>
                                <p>Este es un correo automático, por favor no responder.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                // Versión en texto plano
                $mail->AltBody = "Hola $nombre,\n\n" .
                                "Para restablecer tu contraseña en TuLook, visita el siguiente enlace:\n\n" .
                                "$resetLink\n\n" .
                                "Este enlace expirará en 1 hora.\n\n" .
                                "Si no solicitaste este cambio, ignora este correo.\n\n" .
                                "Saludos,\n" .
                                "El equipo de TuLook";
                
                // Intentar enviar el correo
                $mail->send();
                
                // Registrar envío exitoso
                error_log("Email de recuperación enviado a: $email - " . date('Y-m-d H:i:s'));
                
                return true;
                
            } catch (Exception $e) {
                // Registrar error pero no mostrarlo al usuario (por seguridad)
                error_log("Error enviando email de recuperación a $email: " . $e->getMessage());
                
                // No lanzamos excepción para no revelar información
                // El sistema mostrará mensaje de éxito aunque falle el envío
                return true;
            }
        }
    }