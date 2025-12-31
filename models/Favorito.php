<?php
class Favorito {
    private $conn;
    private $table = "favorito";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function agregarFavorito($idUsuario, $idArticulo) {
        try {
            // Verificar si ya existe
            $sql = "SELECT ID_Favorito FROM favorito WHERE ID_Usuario = ? AND ID_Articulo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $idArticulo]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Ya está en favoritos'];
            }
            
            // Agregar a favoritos
            $sql = "INSERT INTO favorito (ID_Usuario, ID_Articulo) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $idArticulo]);
            
            return ['success' => true, 'message' => 'Agregado a favoritos'];
            
        } catch (PDOException $e) {
            error_log("ERROR al agregar favorito: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    public function eliminarFavorito($idUsuario, $idArticulo) {
        try {
            // Verificar si existe antes de eliminar
            $sql = "SELECT ID_Favorito FROM favorito WHERE ID_Usuario = ? AND ID_Articulo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $idArticulo]);
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'No está en favoritos'];
            }
            
            // Eliminar de favoritos
            $sql = "DELETE FROM favorito WHERE ID_Usuario = ? AND ID_Articulo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $idArticulo]);
            
            return ['success' => true, 'message' => 'Eliminado de favoritos'];
            
        } catch (PDOException $e) {
            error_log("ERROR al eliminar favorito: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    public function esFavorito($idUsuario, $idArticulo) {
        try {
            $sql = "SELECT ID_Favorito FROM favorito WHERE ID_Usuario = ? AND ID_Articulo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario, $idArticulo]);
            
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("ERROR al verificar favorito: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerFavoritos($idUsuario) {
        try {
            $sql = "SELECT 
                        f.ID_Favorito,
                        f.ID_Articulo,
                        f.Fecha,
                        a.N_Articulo,
                        a.Foto,
                        c.N_Categoria,
                        g.N_Genero,
                        pr.Valor AS Precio,
                        COALESCE(SUM(p.Cantidad), 0) AS Stock
                    FROM favorito f
                    INNER JOIN articulo a ON f.ID_Articulo = a.ID_Articulo
                    LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo AND p.Activo = 1
                    LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                    LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                    LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                    WHERE f.ID_Usuario = ? AND a.Activo = 1
                    GROUP BY f.ID_Favorito, f.ID_Articulo, f.Fecha, a.N_Articulo, a.Foto, 
                             c.N_Categoria, g.N_Genero, pr.Valor
                    ORDER BY f.Fecha DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener favoritos: " . $e->getMessage());
            return [];
        }
    }

    public function contarFavoritos($idUsuario) {
        try {
            $sql = "SELECT COUNT(*) as total FROM favorito WHERE ID_Usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (int)$result['total'] : 0;
            
        } catch (PDOException $e) {
            error_log("Error al contar favoritos: " . $e->getMessage());
            return 0;
        }
    }
}
?>