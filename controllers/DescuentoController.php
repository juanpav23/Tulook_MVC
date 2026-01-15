<?php
class DescuentoController {
    private $db;
    private $descuentoModel;

    public function __construct($db) {
        $this->db = $db;
        require_once "models/Descuento.php";
        $this->descuentoModel = new Descuento($db);
    }

    // 🔹 ACCIÓN 1: Listar todos los descuentos (admin)
    public function index() {
        // Verificar si es administrador
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        $descuentos = $this->descuentoModel->obtenerTodos();
        
        // Obtener estadísticas para cada descuento
        foreach ($descuentos as &$descuento) {
            $descuento['estadisticas'] = $this->descuentoModel->obtenerEstadisticas($descuento['ID_Descuento']);
        }

        include "views/admin/layout_admin.php";
    }

    // 🔹 ACCIÓN 2: Crear descuento (admin)
    public function crear() {
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        if ($_POST) {
            // Validar datos
            $errores = $this->validarDatosDescuento($_POST);
            
            if (!empty($errores)) {
                $_SESSION['form_errors'] = $errores;
                $_SESSION['form_data'] = $_POST;
                header("Location: " . BASE_URL . "?c=Descuento&a=crear");
                exit;
            }

            $tipo_aplicacion = $_POST['tipo_aplicacion'];
            $tipo_descuento = $_POST['tipo_descuento'] ?? 'manual';
            
            $datos = [
                'Tipo' => $_POST['tipo'],
                'Valor' => (float)$_POST['valor'],
                'Monto_Minimo' => (float)($_POST['monto_minimo'] ?? 0),
                'Max_Usos_Global' => (int)($_POST['max_usos_global'] ?? 0),
                'Max_Usos_Usuario' => (int)($_POST['max_usos_usuario'] ?? 0),
                'Usos_Globales' => 0,
                'FechaInicio' => $_POST['fecha_inicio'],
                'FechaFin' => $_POST['fecha_fin'],
                'Activo' => isset($_POST['activo']) ? 1 : 0
            ];

            // Generar código según tipo
            if ($tipo_descuento === 'automatico') {
                $datos['Codigo'] = $this->descuentoModel->generarCodigoAutomatico('AUTO');
                $datos['Monto_Minimo'] = max(0, (float)($_POST['monto_minimo'] ?? 0));
            } else {
                $datos['Codigo'] = strtoupper(trim($_POST['codigo']));
            }

            // Asignar según el tipo de aplicación
            switch ($tipo_aplicacion) {
                case 'articulo':
                    $datos['ID_Articulo'] = (int)$_POST['id_articulo'];
                    break;
                case 'producto':
                    $datos['ID_Producto'] = (int)$_POST['id_producto'];
                    break;
                case 'categoria':
                    $datos['ID_Categoria'] = (int)$_POST['id_categoria'];
                    break;
                case 'general':
                    // No asignar ID específico
                    break;
            }

            if ($this->descuentoModel->crear($datos)) {
                $_SESSION['mensaje'] = "🎉 Descuento creado exitosamente";
                unset($_SESSION['form_errors']);
                unset($_SESSION['form_data']);
            } else {
                $_SESSION['error'] = "❌ Error al crear el descuento";
            }
            header("Location: " . BASE_URL . "?c=Descuento&a=index");
            exit;
        }

        // Cargar datos para el formulario
        $articulos = $this->obtenerArticulos();
        $productos = $this->obtenerProductosConVariantes();
        $categorias = $this->obtenerCategorias();
        
        // Recuperar datos del formulario si hay errores
        $descuento = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        include "views/admin/layout_admin.php";
    }

    // 🔹 ACCIÓN 3: Editar descuento (admin)
    public function editar() {
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        $id = (int)$_GET['id'];
        $descuento = $this->descuentoModel->obtenerPorId($id);

        if (!$descuento) {
            $_SESSION['error'] = "❌ Descuento no encontrado";
            header("Location: " . BASE_URL . "?c=Descuento&a=index");
            exit;
        }

        if ($_POST) {
            // Validar datos
            $errores = $this->validarDatosDescuento($_POST, $id);
            
            if (!empty($errores)) {
                $_SESSION['form_errors'] = $errores;
                $_SESSION['form_data'] = array_merge($descuento, $_POST);
                header("Location: " . BASE_URL . "?c=Descuento&a=editar&id=" . $id);
                exit;
            }

            $tipo_aplicacion = $_POST['tipo_aplicacion'];
            
            $datos = [
                'Codigo' => strtoupper(trim($_POST['codigo'])),
                'Tipo' => $_POST['tipo'],
                'Valor' => (float)$_POST['valor'],
                'Monto_Minimo' => (float)($_POST['monto_minimo'] ?? 0),
                'Max_Usos_Global' => (int)($_POST['max_usos_global'] ?? 0),
                'Max_Usos_Usuario' => (int)($_POST['max_usos_usuario'] ?? 0),
                'FechaInicio' => $_POST['fecha_inicio'],
                'FechaFin' => $_POST['fecha_fin'],
                'Activo' => isset($_POST['activo']) ? 1 : 0
            ];

            switch ($tipo_aplicacion) {
                case 'articulo':
                    $datos['ID_Articulo'] = (int)$_POST['id_articulo'];
                    $datos['ID_Producto'] = null;
                    $datos['ID_Categoria'] = null;
                    break;
                case 'producto':
                    $datos['ID_Producto'] = (int)$_POST['id_producto'];
                    $datos['ID_Articulo'] = null;
                    $datos['ID_Categoria'] = null;
                    break;
                case 'categoria':
                    $datos['ID_Categoria'] = (int)$_POST['id_categoria'];
                    $datos['ID_Articulo'] = null;
                    $datos['ID_Producto'] = null;
                    break;
                case 'general':
                    $datos['ID_Articulo'] = null;
                    $datos['ID_Producto'] = null;
                    $datos['ID_Categoria'] = null;
                    break;
            }

            if ($this->descuentoModel->actualizar($id, $datos)) {
                $_SESSION['mensaje'] = "✅ Descuento actualizado exitosamente";
                unset($_SESSION['form_errors']);
                unset($_SESSION['form_data']);
            } else {
                $_SESSION['error'] = "❌ Error al actualizar el descuento";
            }
            header("Location: " . BASE_URL . "?c=Descuento&a=index");
            exit;
        }

        // Cargar datos
        $articulos = $this->obtenerArticulos();
        $productos = $this->obtenerProductosConVariantes();
        $categorias = $this->obtenerCategorias();

        // Obtener estadísticas de uso
        $estadisticas = $this->descuentoModel->obtenerEstadisticas($id);
        $descuento['estadisticas'] = $estadisticas;

        include "views/admin/layout_admin.php";
    }

    // 🔹 ACCIÓN 4: Eliminar descuento (admin)
    public function eliminar() {
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        $id = (int)$_GET['id'];
        
        if ($this->descuentoModel->eliminar($id)) {
            $_SESSION['mensaje'] = "🗑️ Descuento eliminado exitosamente";
        } else {
            $_SESSION['error'] = "❌ Error al eliminar el descuento";
        }
        
        header("Location: " . BASE_URL . "?c=Descuento&a=index");
        exit;
    }

    // 🔹 ACCIÓN 5: Ver estadísticas detalladas (admin)
    public function estadisticas() {
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        $id = (int)$_GET['id'];
        $descuento = $this->descuentoModel->obtenerPorId($id);

        if (!$descuento) {
            $_SESSION['error'] = "❌ Descuento no encontrado";
            header("Location: " . BASE_URL . "?c=Descuento&a=index");
            exit;
        }

        // Obtener usuarios que han usado este descuento
        $query = "SELECT du.*, u.Nombre, u.Apellido, u.Correo,
                         COALESCE(du.Fecha_Ultimo_Uso, 'Nunca') as Fecha_Ultimo_Uso
                  FROM descuento_usuario du
                  INNER JOIN usuario u ON du.ID_Usuario = u.ID_Usuario
                  WHERE du.ID_Descuento = ?
                  ORDER BY du.Usos DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener estadísticas
        $estadisticas = $this->descuentoModel->obtenerEstadisticas($id);
        $descuento['estadisticas'] = $estadisticas;
        $descuento['usuarios'] = $usuarios;

        include "views/admin/layout_admin.php";
    }

    // 🔹 ACCIÓN 6: Validar código AJAX (para frontend) - ✅ TOTALMENTE CORREGIDO
    public function validarCodigoAjax() {
        // Siempre devolver JSON
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['ID_Usuario'])) {
            echo json_encode([
                'success' => false, 
                'mensaje' => 'Debes iniciar sesión'
            ]);
            exit;
        }

        if (!isset($_POST['codigo']) || empty($_POST['codigo'])) {
            echo json_encode([
                'success' => false, 
                'mensaje' => 'Código no proporcionado'
            ]);
            exit;
        }

        $codigo = strtoupper(trim($_POST['codigo']));
        $id_usuario = $_SESSION['ID_Usuario'];
        
        $resultado = $this->descuentoModel->validarCodigo($codigo, $id_usuario);
        
        if ($resultado['valido']) {
            echo json_encode([
                'success' => true,
                'mensaje' => $resultado['mensaje'],
                'descuento' => $resultado['descuento'],
                'tieneLimites' => $resultado['tieneLimites'] ?? false
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => $resultado['mensaje']
            ]);
        }
        exit;
    }

    // 🔹 ACCIÓN 13: Validar código (para uso general) - ✅ TOTALMENTE CORREGIDO
    public function validar() {
        // Siempre devolver JSON
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['ID_Usuario'])) {
            echo json_encode([
                'valido' => false, 
                'mensaje' => 'Debes iniciar sesión'
            ]);
            exit;
        }

        // Obtener código desde POST o GET
        $codigo = '';
        if (isset($_POST['codigo']) && !empty($_POST['codigo'])) {
            $codigo = strtoupper(trim($_POST['codigo']));
        } elseif (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
            $codigo = strtoupper(trim($_GET['codigo']));
        }
        
        if (empty($codigo)) {
            echo json_encode([
                'valido' => false, 
                'mensaje' => 'Código no proporcionado'
            ]);
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        
        try {
            $resultado = $this->descuentoModel->validarCodigo($codigo, $id_usuario);
            
            if ($resultado['valido']) {
                echo json_encode([
                    'valido' => true,
                    'mensaje' => $resultado['mensaje'],
                    'descuento' => $resultado['descuento'],
                    'tieneLimites' => $resultado['tieneLimites'] ?? false
                ]);
            } else {
                echo json_encode([
                    'valido' => false,
                    'mensaje' => $resultado['mensaje']
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error en validar(): " . $e->getMessage());
            echo json_encode([
                'valido' => false,
                'mensaje' => 'Error interno del servidor al validar el código'
            ]);
        }
        exit;
    }

    // 🔹 ACCIÓN 7: Obtener descuentos del usuario (para frontend)
    public function obtenerDescuentosUsuario() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['ID_Usuario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
            exit;
        }

        $descuentos = $this->descuentoModel->obtenerDescuentosVigentesUsuario($_SESSION['ID_Usuario']);
        
        echo json_encode([
            'success' => true,
            'descuentos' => $descuentos
        ]);
        exit;
    }

    // 🔹 ACCIÓN 8: Aplicar descuento en carrito
    public function aplicarDescuentoCarrito() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['ID_Usuario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
            exit;
        }

        if (!isset($_POST['codigo']) || empty($_POST['codigo'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Código no proporcionado']);
            exit;
        }

        $codigo = strtoupper(trim($_POST['codigo']));
        $id_usuario = $_SESSION['ID_Usuario'];
        
        $resultado = $this->descuentoModel->validarCodigo($codigo, $id_usuario);
        
        if ($resultado['valido']) {
            // Guardar en sesión el descuento aplicado
            $_SESSION['descuento_carrito'] = $resultado['descuento'];
            
            echo json_encode([
                'success' => true,
                'mensaje' => 'Descuento aplicado correctamente',
                'descuento' => $resultado['descuento']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => $resultado['mensaje']
            ]);
        }
        exit;
    }

    // 🔹 ACCIÓN 9: Remover descuento del carrito
    public function removerDescuentoCarrito() {
        header('Content-Type: application/json');
        
        unset($_SESSION['descuento_carrito']);
        
        echo json_encode([
            'success' => true,
            'mensaje' => 'Descuento removido'
        ]);
        exit;
    }

    // 🔹 ACCIÓN 10: Procesar descuentos ganados en compra
    public function procesarDescuentosGanados() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['ID_Usuario']) || !isset($_POST['monto_total'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos insuficientes']);
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        $monto_total = (float)$_POST['monto_total'];
        
        // Obtener descuentos ganados
        $descuentos_ganados = $this->descuentoModel->obtenerDescuentosGanados($id_usuario, $monto_total);
        
        if (empty($descuentos_ganados)) {
            echo json_encode(['success' => true, 'ganados' => []]);
            exit;
        }

        // Registrar que el usuario ganó estos descuentos
        $codigos_generados = [];
        foreach ($descuentos_ganados as $descuento) {
            $this->descuentoModel->registrarDescuentoGanado($descuento['ID_Descuento'], $id_usuario);
            $codigos_generados[] = $descuento['Codigo'];
        }

        // Guardar en sesión para mostrar en la página de éxito
        $_SESSION['descuentos_ganados_compra'] = $descuentos_ganados;
        $_SESSION['codigos_descuento_generados'] = $codigos_generados;

        echo json_encode([
            'success' => true,
            'ganados' => $descuentos_ganados,
            'codigos' => $codigos_generados
        ]);
        exit;
    }

    // 🔹 ACCIÓN 11: Registrar uso de descuento en factura
    public function registrarUsoDescuento() {
        header('Content-Type: application/json');
        
        if (!isset($_POST['id_descuento']) || !isset($_SESSION['ID_Usuario'])) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos insuficientes']);
            exit;
        }

        $id_descuento = (int)$_POST['id_descuento'];
        $id_usuario = $_SESSION['ID_Usuario'];
        
        // Verificar que el usuario puede usar el descuento
        if (!$this->descuentoModel->puedeUsarDescuento($id_descuento, $id_usuario)) {
            echo json_encode(['success' => false, 'mensaje' => 'Descuento no disponible']);
            exit;
        }

        // Registrar uso COMPLETO
        $resultado = $this->descuentoModel->registrarUsoCompleto($id_descuento, $id_usuario);

        if ($resultado) {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Uso de descuento registrado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al registrar el uso'
            ]);
        }
        exit;
    }

    // 🔹 ACCIÓN 12: Registrar uso de descuento en checkout
    public function registrarUsoEnCheckout() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['ID_Usuario']) || !isset($_SESSION['descuento_carrito'])) {
            echo json_encode(['success' => false, 'mensaje' => 'No hay descuento para registrar']);
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        $descuento = $_SESSION['descuento_carrito'];
        $id_descuento = $descuento['ID_Descuento'];

        // Registrar uso COMPLETO
        $resultado = $this->descuentoModel->registrarUsoCompleto($id_descuento, $id_usuario);

        if ($resultado) {
            // Limpiar descuento de la sesión después de registrar
            unset($_SESSION['descuento_carrito']);
            
            echo json_encode([
                'success' => true,
                'mensaje' => 'Descuento aplicado y registrado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al registrar el descuento'
            ]);
        }
        exit;
    }

    // 🔹 MÉTODO PRIVADO: Validar datos del descuento
    private function validarDatosDescuento($datos, $id_descuento = null) {
        $errores = [];

        // Validar tipo de descuento
        $tipo_descuento = $datos['tipo_descuento'] ?? 'manual';
        
        if ($tipo_descuento === 'manual') {
            if (empty($datos['codigo'])) {
                $errores['codigo'] = 'El código es obligatorio para descuentos manuales';
            } else {
                $codigo = strtoupper(trim($datos['codigo']));
                if (!preg_match('/^[A-Z0-9_-]+$/', $codigo)) {
                    $errores['codigo'] = 'Solo mayúsculas, números, guiones y guiones bajos';
                } else if ($this->descuentoModel->codigoExiste($codigo, $id_descuento)) {
                    $errores['codigo'] = 'El código ya existe';
                }
            }
        }

        // Validar tipo de descuento
        if (empty($datos['tipo'])) {
            $errores['tipo'] = 'El tipo de descuento es obligatorio';
        }

        // Validar valor
        if (empty($datos['valor'])) {
            $errores['valor'] = 'El valor es obligatorio';
        } else {
            $valor = (float)$datos['valor'];
            $tipo = $datos['tipo'];
            
            if ($tipo === 'Porcentaje' && ($valor <= 0 || $valor > 100)) {
                $errores['valor'] = 'El porcentaje debe estar entre 0.01 y 100';
            }

            if ($tipo === 'ValorFijo' && $valor <= 0) {
                $errores['valor'] = 'El valor fijo debe ser mayor a 0';
            }
        }

        // Validar monto mínimo
        if (isset($datos['monto_minimo']) && $datos['monto_minimo'] !== '') {
            $monto_minimo = (float)$datos['monto_minimo'];
            if ($monto_minimo < 0) {
                $errores['monto_minimo'] = 'El monto mínimo no puede ser negativo';
            }
        }

        // Validar límites de uso
        if (isset($datos['max_usos_global']) && $datos['max_usos_global'] !== '') {
            $max_global = (int)$datos['max_usos_global'];
            if ($max_global < 0) {
                $errores['max_usos_global'] = 'El límite global no puede ser negativo';
            }
        }

        if (isset($datos['max_usos_usuario']) && $datos['max_usos_usuario'] !== '') {
            $max_usuario = (int)$datos['max_usos_usuario'];
            if ($max_usuario < 0) {
                $errores['max_usos_usuario'] = 'El límite por usuario no puede ser negativo';
            }
        }

        // Validar fechas
        if (empty($datos['fecha_inicio'])) {
            $errores['fechas'] = 'La fecha de inicio es obligatoria';
        } else if (empty($datos['fecha_fin'])) {
            $errores['fechas'] = 'La fecha de fin es obligatoria';
        } else if ($datos['fecha_inicio'] >= $datos['fecha_fin']) {
            $errores['fechas'] = 'La fecha de fin debe ser posterior a la fecha de inicio';
        }

        // Validar tipo de aplicación
        $tipo_aplicacion = $datos['tipo_aplicacion'] ?? '';
        
        switch ($tipo_aplicacion) {
            case 'articulo':
                if (empty($datos['id_articulo'])) {
                    $errores['aplicacion'] = 'Debes seleccionar un artículo';
                }
                break;
            case 'producto':
                if (empty($datos['id_producto'])) {
                    $errores['aplicacion'] = 'Debes seleccionar un producto';
                }
                break;
            case 'categoria':
                if (empty($datos['id_categoria'])) {
                    $errores['aplicacion'] = 'Debes seleccionar una categoría';
                }
                break;
            case 'general':
                // No requiere selección específica
                break;
            default:
                $errores['aplicacion'] = 'Debes seleccionar un tipo de aplicación';
                break;
        }

        return $errores;
    }

    // 🔹 MÉTODOS AUXILIARES: Obtener datos para formularios
    private function obtenerArticulos() {
        $query = "SELECT ID_Articulo, N_Articulo 
                  FROM articulo 
                  WHERE Activo = 1 
                  ORDER BY N_Articulo";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerProductosConVariantes() {
        $query = "SELECT 
                    p.ID_Producto,
                    p.Nombre_Producto,
                    a.N_Articulo,
                    av1.Valor as Talla,
                    av2.Valor as Color,
                    CONCAT(a.N_Articulo, ' - ', 
                        COALESCE(av2.Valor, ''), ' ', 
                        COALESCE(av1.Valor, '')) as Nombre_Completo
                FROM producto p
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN atributo_valor av1 ON p.ValorAtributo1 = av1.Valor AND av1.ID_TipoAtributo = 1
                LEFT JOIN atributo_valor av2 ON p.ValorAtributo2 = av2.Valor AND av2.ID_TipoAtributo = 2
                WHERE a.Activo = 1
                ORDER BY a.N_Articulo, av2.Valor, av1.Valor";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerCategorias() {
        $query = "SELECT ID_Categoria, N_Categoria 
                  FROM categoria 
                  ORDER BY N_Categoria";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>