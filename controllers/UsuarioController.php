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
                    $_SESSION['login_time'] = time();

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
                    
                    if (strtolower($rolName) === 'administrador') {
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

    }