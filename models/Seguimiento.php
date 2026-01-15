<?php
class Seguimiento {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Obtener pedido por código de acceso
    public function obtenerPedidoPorCodigo($codigo_acceso) {
        $query = "SELECT f.*, 
                         d.Direccion, d.Ciudad, d.Departamento, d.CodigoPostal,
                         mp.T_Pago as Metodo_Pago,
                         CONCAT(u.Nombre, ' ', u.Apellido) as Nombre_Cliente,
                         u.Correo as Email_Cliente
                  FROM factura f
                  LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  WHERE f.Codigo_Acceso = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $codigo_acceso);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    // Obtener todos los seguimientos de una factura
    public function obtenerSeguimientos($id_factura) {
        $query = "SELECT ps.*, CONCAT(u.Nombre, ' ', u.Apellido) as Nombre
                  FROM pedido_seguimiento ps
                  LEFT JOIN usuario u ON ps.ID_Usuario = u.ID_Usuario
                  WHERE ps.ID_Factura = ?
                  ORDER BY ps.Fecha DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $seguimientos = [];
        while ($row = $result->fetch_assoc()) {
            $seguimientos[] = $row;
        }
        
        return $seguimientos;
    }
    
    // Agregar nuevo seguimiento (para admin)
    public function agregarSeguimiento($id_factura, $estado, $descripcion, $id_usuario = null) {
        $query = "INSERT INTO pedido_seguimiento 
                  (ID_Factura, Estado, Descripcion, ID_Usuario, Fecha) 
                  VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issi", $id_factura, $estado, $descripcion, $id_usuario);
        
        if ($stmt->execute()) {
            // Actualizar estado en factura
            $this->actualizarEstadoFactura($id_factura, $estado);
            
            // Obtener email del cliente para notificación
            $cliente_email = $this->obtenerEmailCliente($id_factura);
            
            return [
                'success' => true,
                'id_seguimiento' => $stmt->insert_id,
                'cliente_email' => $cliente_email
            ];
        }
        
        return ['success' => false];
    }
    
    // Actualizar estado en factura principal
    private function actualizarEstadoFactura($id_factura, $estado) {
        $query = "UPDATE factura SET Estado = ? WHERE ID_Factura = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $estado, $id_factura);
        $stmt->execute();
    }
    
    // Obtener email del cliente para notificación
    private function obtenerEmailCliente($id_factura) {
        $query = "SELECT u.Correo 
                  FROM factura f
                  JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  WHERE f.ID_Factura = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id_factura);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['Correo'] ?? null;
    }
    
    // Obtener todos los pedidos para panel admin
    public function obtenerTodosPedidos($filtro_estado = null, $limit = 50) {
        $where = "";
        $params = [];
        $types = "";
        
        if ($filtro_estado) {
            $where = " WHERE f.Estado = ?";
            $params[] = $filtro_estado;
            $types .= "s";
        }
        
        $query = "SELECT f.ID_Factura, f.Fecha_Factura, f.Monto_Total, f.Estado, 
                         f.Codigo_Acceso,
                         CONCAT(u.Nombre, ' ', u.Apellido) as Cliente,
                         (SELECT COUNT(*) FROM pedido_seguimiento ps 
                          WHERE ps.ID_Factura = f.ID_Factura) as Total_Actualizaciones,
                         (SELECT MAX(Fecha) FROM pedido_seguimiento ps 
                          WHERE ps.ID_Factura = f.ID_Factura) as Ultima_Actualizacion
                  FROM factura f
                  JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  $where
                  ORDER BY f.Fecha_Factura DESC
                  LIMIT ?";
        
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($query);
        
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        
        return $pedidos;
    }
}
?>