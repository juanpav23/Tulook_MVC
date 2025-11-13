<?php
// models/Favorito.php
class Favorito {
    private $conn;
    private $table_name = "favorito";

    public $ID_Favorito;
    public $ID_Usuario;
    public $ID_Producto;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Verificar si un producto ya está en favoritos
    public function exists() {
        $sql = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Usuario = :usuario AND ID_Producto = :producto";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario", $this->ID_Usuario);
        $stmt->bindParam(":producto", $this->ID_Producto);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Agregar a favoritos
    public function add() {
        if ($this->exists()) return false;
        $sql = "INSERT INTO " . $this->table_name . " (ID_Usuario, ID_Producto) VALUES (:usuario, :producto)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario", $this->ID_Usuario);
        $stmt->bindParam(":producto", $this->ID_Producto);
        return $stmt->execute();
    }

    // Quitar de favoritos
    public function remove() {
        $sql = "DELETE FROM " . $this->table_name . " WHERE ID_Usuario = :usuario AND ID_Producto = :producto";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario", $this->ID_Usuario);
        $stmt->bindParam(":producto", $this->ID_Producto);
        return $stmt->execute();
    }

    // Obtener todos los favoritos de un usuario
    public function getByUser($id_usuario) {
        $sql = "SELECT p.ID_Producto, a.N_Articulo, a.Foto, g.N_Genero,
                       MIN(pr.Valor + COALESCE(s.Sobrecosto,0)) AS Precio_Final
                FROM favorito f
                INNER JOIN producto p ON f.ID_Producto = p.ID_Producto
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                INNER JOIN genero g ON a.ID_Genero = g.ID_Genero
                INNER JOIN precio pr ON p.Porcentaje = Porcentaje
                LEFT JOIN sobrecosto_talla s ON p.ID_Talla = s.ID_Talla
                WHERE f.ID_Usuario = :usuario
                GROUP BY p.ID_Producto, a.N_Articulo, a.Foto, g.N_Genero
                ORDER BY f.ID_Favorito DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":usuario", $id_usuario);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

