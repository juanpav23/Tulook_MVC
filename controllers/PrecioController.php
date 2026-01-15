<?php
require_once "models/Database.php";
require_once "models/Precio.php";

class PrecioController {
    private $db;
    private $precioModel;

    public function __construct() {
        $dbObj = new Database();
        $this->db = $dbObj->getConnection();
        $this->precioModel = new Precio($this->db);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ensureAdmin();
    }

    private function ensureAdmin() {
        if (!isset($_SESSION['rol'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $stmt = $this->db->prepare("SELECT Roles FROM rol WHERE ID_Rol = ?");
        $stmt->execute([(int)$_SESSION['rol']]);
        $rol = $stmt->fetchColumn();

        // Permitir rol 1 (Administrador) y rol 2 (Editor)
        if (!$rol || (strtolower($rol) !== 'administrador' && strtolower($rol) !== 'editor')) {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    // 📋 LISTAR PRECIOS CON FILTROS (como en colores)
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? 'todos';
        $enUso = $_GET['en_uso'] ?? 'todos';
        
        // Obtener precios según filtros
        $precios = $this->precioModel->filtrarPrecios($termino, $estado, $enUso);
        $modoBusqueda = (!empty($termino) || $estado !== 'todos' || $enUso !== 'todos');
        
        // Obtener estadísticas generales
        $estadisticas = $this->precioModel->obtenerEstadisticas();
        
        include "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO PRECIO MEJORADO
    public function crear() {
        // Obtener sugerencias de precios comunes
        $preciosExistentes = $this->precioModel->obtenerTodosOrdenados();
        
        // Calcular rangos de precios para sugerencias
        $sugerencias = $this->calcularSugerenciasPrecios($preciosExistentes);
        
        include "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR NUEVO PRECIO
    public function guardar() {
        try {
            $valor = $this->validarValor($_POST['Valor'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            $resultado = $this->precioModel->crear($valor, $activo);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Precio creado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear el precio";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=crear");
            exit;
        }
    }

    // ✏ FORMULARIO EDITAR PRECIO
    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        $precio = $this->precioModel->obtenerPorId($id);
        
        if (!$precio) {
            $_SESSION['mensaje'] = "❌ Precio no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        // Obtener productos que usan este precio
        $productosAsociados = $this->precioModel->obtenerProductosPorPrecio($id);
        
        include "views/admin/layout_admin.php";
    }

    // 💾 ACTUALIZAR PRECIO
    public function actualizar() {
        try {
            $id = (int)($_POST['ID_precio'] ?? 0);
            $valor = $this->validarValor($_POST['Valor'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de precio inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Precio&a=index");
                exit;
            }

            $resultado = $this->precioModel->actualizar($id, $valor, $activo);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Precio actualizado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar el precio";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ Error: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=editar&id=" . $id);
            exit;
        }
    }

    // 🔄 ACTIVAR/DESACTIVAR PRECIO
    public function cambiarEstado() {
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de precio inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        try {
            $resultado = $this->precioModel->cambiarEstado($id, $estado);

            if ($resultado) {
                $_SESSION['mensaje'] = $estado ? "✅ Precio activado correctamente" : "✅ Precio desactivado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al cambiar el estado del precio";
                $_SESSION['mensaje_tipo'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "warning";
        }

        header("Location: " . BASE_URL . "?c=Precio&a=index");
        exit;
    }

    // 🗑 ELIMINAR PRECIO
    public function eliminar() {
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de precio inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        try {
            $resultado = $this->precioModel->eliminar($id);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Precio eliminado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al eliminar el precio";
                $_SESSION['mensaje_tipo'] = "danger";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "warning";
        }

        header("Location: " . BASE_URL . "?c=Precio&a=index");
        exit;
    }

    // 📊 OBTENER PRODUCTOS QUE USAN UN PRECIO (API JSON)
    public function obtenerProductos() {
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $productos = $this->precioModel->obtenerProductosPorPrecio($id);
            
            echo json_encode([
                'success' => true, 
                'articulos' => $productos['articulos'] ?? [],
                'variantes' => $productos['variantes'] ?? []
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 🔄 MIGRAR PRODUCTOS DE UN PRECIO A OTRO (API JSON)
    public function migrarProductos() {
        header('Content-Type: application/json');
        
        try {
            $origen = (int)($_GET['origen'] ?? 0);
            $destino = (int)($_GET['destino'] ?? 0);
            
            if ($origen <= 0 || $destino <= 0) {
                echo json_encode(['success' => false, 'message' => 'IDs inválidos']);
                exit;
            }

            $resultado = $this->precioModel->migrarProductos($origen, $destino);
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 📋 OBTENER PRECIOS ACTIVOS PARA SELECT (API JSON)
    public function obtenerPreciosActivos() {
        header('Content-Type: application/json');
        
        try {
            $excluir = (int)($_GET['excluir'] ?? 0);
            $precios = $this->precioModel->obtenerPreciosActivos($excluir);
            echo json_encode($precios);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 📊 OBTENER ESTADÍSTICAS
    public function obtenerEstadisticas() {
        header('Content-Type: application/json');
        
        try {
            $estadisticas = $this->precioModel->obtenerEstadisticas();
            echo json_encode(['success' => true, 'estadisticas' => $estadisticas]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // 🔧 VALIDAR VALOR DEL PRECIO
    private function validarValor($valor) {
        // Limpiar y convertir - IMPORTANTE: mantener todos los dígitos
        $valor = str_replace(['$', ',', '.', ' '], '', $valor);
        
        // Validar que sea un número válido
        if (!is_numeric($valor)) {
            throw new Exception("El valor debe ser un número válido");
        }
        
        // Convertir a float (puede tener decimales)
        $valor = floatval($valor);
        
        if ($valor <= 0) {
            throw new Exception("El valor debe ser mayor a 0");
        }
        
        if ($valor > 1000000000) {
            throw new Exception("El valor es demasiado grande (máximo: 1.000.000.000)");
        }
        
        // Redondear a 2 decimales para evitar problemas de precisión
        $valor = round($valor, 2);
        
        return $valor;
    }


    // 💡 CALCULAR SUGERENCIAS DE PRECIOS
    private function calcularSugerenciasPrecios($preciosExistentes) {
        $sugerencias = [
            'bajos' => [],
            'medios' => [],
            'altos' => []
        ];
        
        if (empty($preciosExistentes)) {
            // Valores por defecto si no hay precios
            $sugerencias['bajos'] = [5000, 10000, 15000, 20000];
            $sugerencias['medios'] = [25000, 35000, 50000, 75000];
            $sugerencias['altos'] = [100000, 150000, 200000, 300000];
        } else {
            // Calcular basado en precios existentes
            $valores = array_column($preciosExistentes, 'Valor');
            $promedio = array_sum($valores) / count($valores);
            
            $sugerencias['bajos'] = [
                round($promedio * 0.25, -3),
                round($promedio * 0.33, -3),
                round($promedio * 0.5, -3),
                round($promedio * 0.66, -3)
            ];
            
            $sugerencias['medios'] = [
                round($promedio * 0.8, -3),
                round($promedio, -3),
                round($promedio * 1.2, -3),
                round($promedio * 1.5, -3)
            ];
            
            $sugerencias['altos'] = [
                round($promedio * 2, -3),
                round($promedio * 3, -3),
                round($promedio * 4, -3),
                round($promedio * 5, -3)
            ];
        }
        
        // Asegurar valores únicos y ordenados
        foreach ($sugerencias as &$categoria) {
            $categoria = array_unique($categoria);
            sort($categoria);
            $categoria = array_values(array_filter($categoria, function($v) {
                return $v > 0;
            }));
        }
        
        return $sugerencias;
    }
}
?>