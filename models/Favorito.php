<?php
class Favorito {
    private $conn;
    private $table = "favorito";

    public $ID_Favorito;
    public $ID_Usuario;
    public $ID_Producto;
    public $ID_Articulo;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Existe favorito para este usuario + (producto o articulo)
    public function existsFor($idUsuario, $idProducto = null, $idArticulo = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE ID_Usuario = :usuario
                  AND ((ID_Producto IS NOT NULL AND ID_Producto = :producto) OR (ID_Articulo IS NOT NULL AND ID_Articulo = :articulo))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':producto', $idProducto, PDO::PARAM_INT);
        $stmt->bindValue(':articulo', $idArticulo, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    // Añadir favorito (acepta nulos)
    public function add($idUsuario, $idProducto = null, $idArticulo = null) {
        $sql = "INSERT INTO {$this->table} (ID_Usuario, ID_Producto, ID_Articulo) VALUES (:usuario, :producto, :articulo)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
        
        if ($idProducto === null) $stmt->bindValue(':producto', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':producto', $idProducto, PDO::PARAM_INT);

        if ($idArticulo === null) $stmt->bindValue(':articulo', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':articulo', $idArticulo, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Remove favorito (por usuario + (producto o articulo))
    public function remove($idUsuario, $idProducto = null, $idArticulo = null) {
        $sql = "DELETE FROM {$this->table}
                WHERE ID_Usuario = :usuario
                  AND ((ID_Producto IS NOT NULL AND ID_Producto = :producto) OR (ID_Articulo IS NOT NULL AND ID_Articulo = :articulo))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
        if ($idProducto === null) $stmt->bindValue(':producto', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':producto', $idProducto, PDO::PARAM_INT);

        if ($idArticulo === null) $stmt->bindValue(':articulo', null, PDO::PARAM_NULL);
        else $stmt->bindValue(':articulo', $idArticulo, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Obtener favoritos de un usuario (CORREGIDO)
    public function getByUser($idUsuario) {
        $sql = "SELECT 
                    f.ID_Favorito,
                    f.ID_Producto,
                    f.ID_Articulo,
                    -- Campos del artículo
                    a.N_Articulo,
                    a.Foto as FotoArticulo,
                    -- Campos del producto
                    p.Nombre_Producto,
                    p.Foto as FotoProducto,
                    -- Campos calculados
                    COALESCE(p.Nombre_Producto, a.N_Articulo) AS Nombre,
                    COALESCE(p.Foto, a.Foto) AS Foto,
                    COALESCE(pr.Valor, 0) AS Precio_Base,
                    COALESCE(p.Porcentaje, 0) AS Porcentaje,
                    (COALESCE(pr.Valor,0) + (COALESCE(pr.Valor,0) * (COALESCE(p.Porcentaje,0) / 100))) AS Precio_Final,
                    g.N_Genero
                FROM favorito f
                LEFT JOIN producto p ON f.ID_Producto = p.ID_Producto
                LEFT JOIN articulo a ON f.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON COALESCE(a.ID_Precio, 0) = pr.ID_Precio
                LEFT JOIN genero g ON COALESCE(a.ID_Genero, 0) = g.ID_Genero
                WHERE f.ID_Usuario = :usuario
                ORDER BY f.ID_Favorito DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':usuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>