<?php
// models/Descuento.php
class Descuento {
    private $conn;
    private $table_name = "descuento";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodos() {
        $query = "SELECT d.*, 
                         a.N_Articulo as ArticuloNombre,
                         p.Nombre_Producto as ProductoNombre,
                         c.N_Categoria as CategoriaNombre,
                         CASE 
                            WHEN d.Activo = 0 THEN 'inactivo'
                            WHEN d.FechaFin < NOW() THEN 'expirado' 
                            WHEN d.FechaInicio > NOW() THEN 'programado'
                            ELSE 'activo'
                         END as EstadoVigencia,
                         CASE 
                            WHEN d.Activo = 0 THEN 0
                            WHEN d.FechaFin < NOW() THEN 0 
                            WHEN d.FechaInicio > NOW() THEN 1
                            ELSE 1
                         END as EstaActivo
                  FROM " . $this->table_name . " d
                  LEFT JOIN articulo a ON d.ID_Articulo = a.ID_Articulo
                  LEFT JOIN producto p ON d.ID_Producto = p.ID_Producto
                  LEFT JOIN categoria c ON d.ID_Categoria = c.ID_Categoria
                  ORDER BY d.FechaInicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $query = "SELECT d.*, 
                         a.N_Articulo as ArticuloNombre,
                         p.Nombre_Producto as ProductoNombre,
                         c.N_Categoria as CategoriaNombre
                  FROM " . $this->table_name . " d
                  LEFT JOIN articulo a ON d.ID_Articulo = a.ID_Articulo
                  LEFT JOIN producto p ON d.ID_Producto = p.ID_Producto
                  LEFT JOIN categoria c ON d.ID_Categoria = c.ID_Categoria
                  WHERE d.ID_Descuento = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (Codigo, ID_Articulo, ID_Producto, ID_Categoria, Tipo, Valor, FechaInicio, FechaFin, Activo) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $datos['Codigo'],
            $datos['ID_Articulo'],
            $datos['ID_Producto'],
            $datos['ID_Categoria'],
            $datos['Tipo'],
            $datos['Valor'],
            $datos['FechaInicio'],
            $datos['FechaFin'],
            $datos['Activo']
        ]);
    }

    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table_name . " 
                  SET Codigo = ?, ID_Articulo = ?, ID_Producto = ?, ID_Categoria = ?, 
                      Tipo = ?, Valor = ?, FechaInicio = ?, FechaFin = ?, Activo = ?
                  WHERE ID_Descuento = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $datos['Codigo'],
            $datos['ID_Articulo'],
            $datos['ID_Producto'],
            $datos['ID_Categoria'],
            $datos['Tipo'],
            $datos['Valor'],
            $datos['FechaInicio'],
            $datos['FechaFin'],
            $datos['Activo'],
            $id
        ]);
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID_Descuento = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // Obtener descuento activo para un artículo
    public function obtenerDescuentoArticulo($id_articulo) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ID_Articulo = ? AND Activo = 1 
                  AND FechaInicio <= NOW() AND FechaFin >= NOW()
                  ORDER BY FechaInicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_articulo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener descuento activo para un producto
    public function obtenerDescuentoProducto($id_producto) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ID_Producto = ? AND Activo = 1 
                  AND FechaInicio <= NOW() AND FechaFin >= NOW()
                  ORDER BY FechaInicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener descuento activo para una categoría
    public function obtenerDescuentoCategoria($id_categoria) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ID_Categoria = ? AND Activo = 1 
                  AND FechaInicio <= NOW() AND FechaFin >= NOW()
                  ORDER BY FechaInicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_categoria]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener el mejor descuento aplicable
    public function obtenerMejorDescuento($id_articulo, $id_producto = null, $id_categoria = null) {
        // Primero buscar descuento para producto específico
        if ($id_producto) {
            $descuento = $this->obtenerDescuentoProducto($id_producto);
            if ($descuento) return $descuento;
        }

        // Luego buscar descuento para artículo
        $descuento = $this->obtenerDescuentoArticulo($id_articulo);
        if ($descuento) return $descuento;

        // Finalmente buscar descuento para categoría
        if ($id_categoria) {
            $descuento = $this->obtenerDescuentoCategoria($id_categoria);
            if ($descuento) return $descuento;
        }

        return null;
    }

    // Método para verificar si un código ya existe
    public function codigoExiste($codigo, $id_excluir = null) {
        $query = "SELECT ID_Descuento FROM " . $this->table_name . " WHERE Codigo = ?";
        $params = [$codigo];
        
        if ($id_excluir) {
            $query .= " AND ID_Descuento != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    // ✅ NUEVO MÉTODO: Obtener todos los descuentos vigentes (para el servicio)
    public function obtenerDescuentosVigentes() {
        $query = "SELECT d.*, 
                         a.N_Articulo as ArticuloNombre,
                         p.Nombre_Producto as ProductoNombre, 
                         c.N_Categoria as CategoriaNombre
                  FROM " . $this->table_name . " d
                  LEFT JOIN articulo a ON d.ID_Articulo = a.ID_Articulo
                  LEFT JOIN producto p ON d.ID_Producto = p.ID_Producto  
                  LEFT JOIN categoria c ON d.ID_Categoria = c.ID_Categoria
                  WHERE d.Activo = 1 
                    AND d.FechaInicio <= NOW() 
                    AND d.FechaFin >= NOW()
                  ORDER BY 
                    CASE 
                        WHEN d.ID_Producto IS NOT NULL THEN 1  -- Productos específicos primero
                        WHEN d.ID_Articulo IS NOT NULL THEN 2  -- Luego artículos  
                        WHEN d.ID_Categoria IS NOT NULL THEN 3 -- Finalmente categorías
                        ELSE 4
                    END";
              
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>