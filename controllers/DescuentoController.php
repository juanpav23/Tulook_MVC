<?php
// controllers/DescuentoController.php
class DescuentoController {
    private $db;
    private $descuentoModel;

    public function __construct($db) {
        $this->db = $db;
        require_once "models/Descuento.php";
        $this->descuentoModel = new Descuento($db);
    }

    public function index() {
        // Verificar si es administrador
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        $descuentos = $this->descuentoModel->obtenerTodos();
        include "views/admin/layout_admin.php"; // ✅ CAMBIADO
    }

    public function ver() {
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

        include "views/admin/layout_admin.php"; // ✅ CAMBIADO
    }

    public function crear() {
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        if ($_POST) {
            // Primero validar
            $errores = $this->validarDatosDescuento($_POST);
            
            if (!empty($errores)) {
                $_SESSION['form_errors'] = $errores;
                $_SESSION['form_data'] = $_POST;
                header("Location: " . BASE_URL . "?c=Descuento&a=crear");
                exit;
            }

            $tipo_aplicacion = $_POST['tipo_aplicacion'];
            
            $datos = [
                'Codigo' => strtoupper(trim($_POST['codigo'])),
                'ID_Articulo' => null,
                'ID_Producto' => null,
                'ID_Categoria' => null,
                'Tipo' => $_POST['tipo'],
                'Valor' => (float)$_POST['valor'],
                'FechaInicio' => $_POST['fecha_inicio'],
                'FechaFin' => $_POST['fecha_fin'],
                'Activo' => isset($_POST['activo']) ? 1 : 0
            ];

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

        include "views/admin/layout_admin.php"; // ✅ CAMBIADO
    }

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
                $_SESSION['form_data'] = $_POST;
                header("Location: " . BASE_URL . "?c=Descuento&a=editar&id=" . $id);
                exit;
            }

            $tipo_aplicacion = $_POST['tipo_aplicacion'];
            
            $datos = [
                'Codigo' => strtoupper(trim($_POST['codigo'])),
                'ID_Articulo' => null,
                'ID_Producto' => null,
                'ID_Categoria' => null,
                'Tipo' => $_POST['tipo'],
                'Valor' => (float)$_POST['valor'],
                'FechaInicio' => $_POST['fecha_inicio'],
                'FechaFin' => $_POST['fecha_fin'],
                'Activo' => isset($_POST['activo']) ? 1 : 0
            ];

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

        // Cargar datos usando métodos internos
        $articulos = $this->obtenerArticulos();
        $productos = $this->obtenerProductosConVariantes();
        $categorias = $this->obtenerCategorias();

        include "views/admin/layout_admin.php"; // ✅ CAMBIADO
    }

    public function eliminar() {
        if ($_SESSION['RolName'] !== 'Administrador') {
            header("Location: " . BASE_URL);
            exit;
        }

        $id = (int)$_GET['id'];
        
        // Verificar si el descuento está activo actualmente
        $descuento = $this->descuentoModel->obtenerPorId($id);
        if ($descuento) {
            $now = date('Y-m-d H:i:s');
            $isActive = $descuento['Activo'];
            $isCurrent = $isActive && $descuento['FechaInicio'] <= $now && $descuento['FechaFin'] >= $now;
            
            if ($isCurrent) {
                $_SESSION['error'] = "❌ No se puede eliminar un descuento que está actualmente activo";
                header("Location: " . BASE_URL . "?c=Descuento&a=index");
                exit;
            }
        }

        if ($this->descuentoModel->eliminar($id)) {
            $_SESSION['mensaje'] = "✅ Descuento eliminado exitosamente";
        } else {
            $_SESSION['error'] = "❌ Error al eliminar el descuento";
        }
        header("Location: " . BASE_URL . "?c=Descuento&a=index");
        exit;
    }

    // =======================================================
    // 🔧 MÉTODOS PRIVADOS PARA OBTENER DATOS
    // =======================================================

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

    // Método para validaciones
    private function validarDatosDescuento($datos, $id_descuento = null) {
        $errores = [];

        // Validar código único
        if (empty($datos['codigo'])) {
            $errores['codigo'] = 'El código es obligatorio';
        } else {
            $codigo = strtoupper(trim($datos['codigo']));
            if (!preg_match('/^[A-Z0-9_]+$/', $codigo)) {
                $errores['codigo'] = 'El código solo puede contener mayúsculas, números y guiones bajos';
            } else if ($this->descuentoModel->codigoExiste($codigo, $id_descuento)) {
                $errores['codigo'] = 'El código ya existe';
            }
        }

        // Validar tipo de descuento
        if (empty($datos['tipo'])) {
            $errores['tipo'] = 'El tipo de descuento es obligatorio';
        }

        // Validar valor según tipo
        if (empty($datos['valor'])) {
            $errores['valor'] = 'El valor es obligatorio';
        } else {
            $valor = (float)$datos['valor'];
            $tipo = $datos['tipo'];
            
            if ($tipo === 'Porcentaje' && ($valor < 0 || $valor > 100)) {
                $errores['valor'] = 'El porcentaje debe estar entre 0 y 100';
            }

            if ($tipo === 'ValorFijo' && $valor < 0) {
                $errores['valor'] = 'El valor fijo no puede ser negativo';
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

        // Validar que se haya seleccionado un tipo de aplicación
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
            default:
                $errores['aplicacion'] = 'Debes seleccionar un tipo de aplicación';
                break;
        }

        return $errores;
    }
}
?>