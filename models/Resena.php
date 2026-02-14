<?php
class Resena {

    private $db;

    public function __construct($conexion){
        $this->db = $conexion;
    }

    public function usuarioCompro($idUsuario, $idArticulo){
        $sql = "SELECT fp.ID_Factura
                FROM factura_producto fp
                INNER JOIN factura f ON fp.ID_Factura = f.ID_Factura
                INNER JOIN producto p ON fp.ID_Producto = p.ID_Producto
                WHERE f.ID_Usuario = ? AND p.ID_Articulo = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario, $idArticulo]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function filtrarTexto($texto){
        $sql = "SELECT Palabra FROM palabras_bloqueadas WHERE Activo=1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $palabras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($palabras as $row){
            $word = preg_quote($row['Palabra'], '/');
            $texto = preg_replace('/\b' . $word . '\b/i', '****', $texto);
        }
        return $texto;
    }

    public function crearResena($idUsuario, $idArticulo, $idProducto = null, $calificacion = 5, $titulo = null, $comentario = ''){
        $comentario = $this->filtrarTexto($comentario);
        $sql = "INSERT INTO resena (ID_Usuario, ID_Articulo, ID_Producto, Calificacion, Titulo, Comentario, Fecha_Resena, Activo, Util_Positivo, Util_Negativo)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 1, 0, 0)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$idUsuario, $idArticulo, $idProducto, $calificacion, $titulo, $comentario]);
        return $result ? $this->db->lastInsertId() : null;
    }

    public function obtenerResenas($idArticulo){
        $sql = "SELECT r.*, u.Nombre, u.Apellido, 
                IFNULL(r.Util_Positivo, 0) as Util_Positivo, 
                IFNULL(r.Util_Negativo, 0) as Util_Negativo
                FROM resena r
                LEFT JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                WHERE r.ID_Articulo = ? AND r.Activo = 1
                ORDER BY r.Fecha_Resena DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idArticulo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstadisticas($idArticulo){
        $sql = "SELECT COUNT(*) AS total, COALESCE(AVG(Calificacion),0) AS promedio
                FROM resena
                WHERE ID_Articulo = ? AND Activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idArticulo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function responderResena($idResena, $idAdmin, $respuesta){
        $sql = "INSERT INTO resena_respuesta (ID_Resena, ID_Usuario, Respuesta, Fecha_Respuesta, Activo)
                VALUES (?, ?, ?, NOW(), 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idResena, $idAdmin, $respuesta]);
    }

    /**
     * VOTAR - VERSIÓN CORREGIDA Y OPTIMIZADA
     * AHORA SIEMPRE DEVUELVE LOS VALORES ACTUALIZADOS CORRECTAMENTE
     */
    public function votar($idResena, $idUsuario, $esPositivo){
        try {
            $this->db->beginTransaction();
            
            $val = $esPositivo ? 1 : 0;
            
            // Verificar si ya existe un voto
            $sql = "SELECT ID_Voto, Es_Positivo FROM resena_voto WHERE ID_Resena = ? AND ID_Usuario = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idResena, $idUsuario]);
            $ex = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ex) {
                $current = (int)$ex['Es_Positivo'];
                if ($current === $val) {
                    // ELIMINAR VOTO
                    $del = $this->db->prepare("DELETE FROM resena_voto WHERE ID_Voto = ?");
                    $del->execute([$ex['ID_Voto']]);
                    
                    if ($val === 1) {
                        $this->db->prepare("UPDATE resena SET Util_Positivo = GREATEST(IFNULL(Util_Positivo, 0) - 1, 0) WHERE ID_Resena = ?")
                                 ->execute([$idResena]);
                    } else {
                        $this->db->prepare("UPDATE resena SET Util_Negativo = GREATEST(IFNULL(Util_Negativo, 0) - 1, 0) WHERE ID_Resena = ?")
                                 ->execute([$idResena]);
                    }
                    $user_vote = 'none';
                } else {
                    // CAMBIAR VOTO
                    $upd = $this->db->prepare("UPDATE resena_voto SET Es_Positivo = ?, Fecha_Voto = NOW() WHERE ID_Voto = ?");
                    $upd->execute([$val, $ex['ID_Voto']]);
                    
                    if ($val === 1) {
                        // Cambiar de negativo a positivo
                        $this->db->prepare("UPDATE resena SET 
                                            Util_Positivo = IFNULL(Util_Positivo, 0) + 1, 
                                            Util_Negativo = GREATEST(IFNULL(Util_Negativo, 0) - 1, 0) 
                                            WHERE ID_Resena = ?")
                                 ->execute([$idResena]);
                    } else {
                        // Cambiar de positivo a negativo
                        $this->db->prepare("UPDATE resena SET 
                                            Util_Negativo = IFNULL(Util_Negativo, 0) + 1, 
                                            Util_Positivo = GREATEST(IFNULL(Util_Positivo, 0) - 1, 0) 
                                            WHERE ID_Resena = ?")
                                 ->execute([$idResena]);
                    }
                    $user_vote = $val === 1 ? 'positivo' : 'negativo';
                }
            } else {
                // NUEVO VOTO
                $ins = $this->db->prepare("INSERT INTO resena_voto (ID_Resena, ID_Usuario, Es_Positivo, Fecha_Voto) VALUES (?, ?, ?, NOW())");
                $ins->execute([$idResena, $idUsuario, $val]);
                
                if ($val === 1) {
                    $this->db->prepare("UPDATE resena SET Util_Positivo = IFNULL(Util_Positivo, 0) + 1 WHERE ID_Resena = ?")
                             ->execute([$idResena]);
                } else {
                    $this->db->prepare("UPDATE resena SET Util_Negativo = IFNULL(Util_Negativo, 0) + 1 WHERE ID_Resena = ?")
                             ->execute([$idResena]);
                }
                $user_vote = $val === 1 ? 'positivo' : 'negativo';
            }

            // 🔴🔴🔴 CORREGIDO: OBTENER VALORES ACTUALIZADOS DIRECTAMENTE DE LA BD 🔴🔴🔴
            $sql = "SELECT IFNULL(Util_Positivo, 0) as Util_Positivo, IFNULL(Util_Negativo, 0) as Util_Negativo 
                    FROM resena WHERE ID_Resena = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idResena]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'util_positivo' => (int)($res['Util_Positivo'] ?? 0),
                'util_negativo' => (int)($res['Util_Negativo'] ?? 0),
                'user_vote' => $user_vote
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en votar: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function reportar($idResena, $idUsuario, $motivo, $descripcion = null){
        $sql = "INSERT INTO resena_reporte (ID_Resena, ID_Usuario, Motivo, Descripcion, Fecha_Reporte, Estado)
                VALUES (?, ?, ?, ?, NOW(), 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idResena, $idUsuario, $motivo, $descripcion]);
    }

    public function crearAdvertencia($idUsuario, $idAdmin = null, $motivo = null, $descripcion = null, $tipo = 'Leve'){
        $sql = "INSERT INTO advertencias_usuario (ID_Usuario, ID_Admin, Motivo, Descripcion, Tipo, Fecha_Advertencia, Estado)
                VALUES (?, ?, ?, ?, ?, NOW(), 'Activa')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idUsuario, $idAdmin, $motivo, $descripcion, $tipo]);
    }

    public function editarResena($idResena, $titulo, $comentario, $calificacion){
        $comentario = $this->filtrarTexto($comentario);
        $sql = "UPDATE resena SET Titulo = ?, Comentario = ?, Calificacion = ?, Fecha_Actualizacion = NOW(), Modificado = 1 WHERE ID_Resena = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$titulo, $comentario, $calificacion, $idResena]);
    }

    public function eliminarResena($idResena){
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("SELECT ID_Resena FROM resena WHERE ID_Resena = ? LIMIT 1");
            $stmt->execute([$idResena]);
            $resena = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resena) {
                throw new Exception("Reseña no encontrada");
            }
            
            $stmtFotos = $this->db->prepare("SELECT Foto FROM resena_foto WHERE ID_Resena = ?");
            $stmtFotos->execute([$idResena]);
            $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($fotos as $foto) {
                if (file_exists($foto['Foto'])) {
                    @unlink($foto['Foto']);
                }
            }
            
            $this->db->prepare("DELETE FROM resena_voto WHERE ID_Resena = ?")->execute([$idResena]);
            $this->db->prepare("DELETE FROM resena_foto WHERE ID_Resena = ?")->execute([$idResena]);
            $this->db->prepare("DELETE FROM resena_respuesta WHERE ID_Resena = ?")->execute([$idResena]);
            
            $sql = "DELETE FROM resena WHERE ID_Resena = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$idResena]);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function agregarFoto($idResena, $ruta){
        $sql = "INSERT INTO resena_foto (ID_Resena, Foto, Activo, Fecha_Subida) VALUES (?, ?, 1, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idResena, $ruta]);
    }

    public function obtenerFotos($idResena){
        $sql = "SELECT * FROM resena_foto WHERE ID_Resena = ? AND (Activo = 1 OR Activo IS NULL) ORDER BY Orden ASC, ID_Foto ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idResena]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarFoto($idFoto){
        $sql = "UPDATE resena_foto SET Activo = 0 WHERE ID_Foto = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idFoto]);
    }

    public function obtenerPorIdResena($idResena){
        $sql = "SELECT *, IFNULL(Util_Positivo, 0) as Util_Positivo, IFNULL(Util_Negativo, 0) as Util_Negativo 
                FROM resena 
                WHERE ID_Resena = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idResena]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerFotoPorId($idFoto){
        $sql = "SELECT * FROM resena_foto WHERE ID_Foto = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idFoto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorUsuarioArticulo($idUsuario, $idArticulo){
        $sql = "SELECT * FROM resena WHERE ID_Usuario = ? AND ID_Articulo = ? AND Activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario, $idArticulo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorUsuarioProducto($idUsuario, $idProducto){
        $sql = "SELECT * FROM resena WHERE ID_Usuario = ? AND ID_Producto = ? AND Activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idUsuario, $idProducto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}