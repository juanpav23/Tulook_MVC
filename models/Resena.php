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

    public function verificarPalabrasBloqueadas($texto){
        if (empty($texto)) return ['tiene_bloqueadas' => false, 'palabras' => [], 'gravedad_maxima' => null];
        
        $sql = "SELECT Palabra, Gravedad FROM palabras_bloqueadas WHERE Activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $palabras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $encontradas = [];
        $gravedad_maxima = null;
        $orden_gravedad = ['Leve' => 1, 'Media' => 2, 'Grave' => 3];
        
        $texto_lower = mb_strtolower($texto, 'UTF-8');
        
        foreach($palabras as $row){
            $palabra = $row['Palabra'];
            $gravedad = $row['Gravedad'] ?: 'Leve';
            
            $patron = '/\b' . preg_quote(mb_strtolower($palabra, 'UTF-8'), '/') . '\b/u';
            if (preg_match($patron, $texto_lower)) {
                $encontradas[] = [
                    'palabra' => $palabra,
                    'gravedad' => $gravedad
                ];
                
                if ($gravedad_maxima === null || $orden_gravedad[$gravedad] > $orden_gravedad[$gravedad_maxima]) {
                    $gravedad_maxima = $gravedad;
                }
            }
        }
        
        return [
            'tiene_bloqueadas' => !empty($encontradas),
            'palabras' => $encontradas,
            'gravedad_maxima' => $gravedad_maxima
        ];
    }

    public function filtrarTexto($texto){
        if (empty($texto)) return $texto;
        
        $sql = "SELECT Palabra FROM palabras_bloqueadas WHERE Activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $palabras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $texto_filtrado = $texto;
        foreach($palabras as $row){
            $palabra = preg_quote($row['Palabra'], '/');
            $texto_filtrado = preg_replace('/\b' . $palabra . '\b/iu', str_repeat('*', mb_strlen($row['Palabra'])), $texto_filtrado);
        }
        return $texto_filtrado;
    }

    public function crearAdvertencia($idUsuario, $idAdmin = null, $motivo = null, $descripcion = null, $tipo = 'Leve'){
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO advertencias_usuario (ID_Usuario, ID_Admin, Motivo, Descripcion, Tipo, Fecha_Advertencia, Estado)
                    VALUES (?, ?, ?, ?, ?, NOW(), 'Activa')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario, $idAdmin, $motivo, $descripcion, $tipo]);
            
            $sql = "UPDATE usuario SET Num_Advertencias = IFNULL(Num_Advertencias, 0) + 1 WHERE ID_Usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario]);
            
            $sql = "SELECT Num_Advertencias FROM usuario WHERE ID_Usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idUsuario]);
            $numAdvertencias = $stmt->fetchColumn();
            
            if ($numAdvertencias >= 3) {
                $sql = "UPDATE usuario SET Activo = 0, Motivo_Desactivacion = 'Acumuló 3 advertencias', 
                        Fecha_Desactivacion = NOW(), ID_Admin_Desactiva = ? 
                        WHERE ID_Usuario = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$idAdmin, $idUsuario]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error al crear advertencia: " . $e->getMessage());
            return false;
        }
    }

    public function crearResena($idUsuario, $idArticulo, $idProducto = null, $calificacion = 5, $titulo = null, $comentario = ''){
        try {
            $this->db->beginTransaction();
            
            $texto_completo = ($titulo ? $titulo . ' ' : '') . $comentario;
            $verificacion = $this->verificarPalabrasBloqueadas($texto_completo);
            
            if ($verificacion['tiene_bloqueadas'] && $verificacion['gravedad_maxima'] === 'Grave') {
                $palabras_str = implode(', ', array_column($verificacion['palabras'], 'palabra'));
                $this->crearAdvertencia(
                    $idUsuario,
                    null,
                    'Intento de publicar lenguaje inapropiado',
                    "Intento de crear reseña con palabras graves: $palabras_str",
                    'Grave'
                );
                
                throw new Exception('Tu reseña contiene lenguaje inapropiado y ha sido rechazada. Has recibido una advertencia.');
            }
            
            $titulo_filtrado = $titulo ? $this->filtrarTexto($titulo) : null;
            $comentario_filtrado = $this->filtrarTexto($comentario);
            
            $sql = "INSERT INTO resena (ID_Usuario, ID_Articulo, ID_Producto, Calificacion, Titulo, Comentario, Fecha_Resena, Activo, Util_Positivo, Util_Negativo)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), 1, 0, 0)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$idUsuario, $idArticulo, $idProducto, $calificacion, $titulo_filtrado, $comentario_filtrado]);
            $idResena = $this->db->lastInsertId();
            
            if ($verificacion['tiene_bloqueadas']) {
                $palabras_str = implode(', ', array_column($verificacion['palabras'], 'palabra'));
                $this->crearAdvertencia(
                    $idUsuario,
                    null,
                    'Lenguaje inapropiado en reseña',
                    "La reseña contenía palabras inapropiadas: $palabras_str. El texto fue filtrado automáticamente.",
                    $verificacion['gravedad_maxima']
                );
            }
            
            $this->db->commit();
            return $idResena;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en crearResena: " . $e->getMessage());
            throw $e;
        }
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

    public function votar($idResena, $idUsuario, $esPositivo){
        try {
            $this->db->beginTransaction();
            
            $val = $esPositivo ? 1 : 0;
            
            $sql = "SELECT ID_Voto, Es_Positivo FROM resena_voto WHERE ID_Resena = ? AND ID_Usuario = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idResena, $idUsuario]);
            $ex = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ex) {
                $current = (int)$ex['Es_Positivo'];
                if ($current === $val) {
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
                    $upd = $this->db->prepare("UPDATE resena_voto SET Es_Positivo = ?, Fecha_Voto = NOW() WHERE ID_Voto = ?");
                    $upd->execute([$val, $ex['ID_Voto']]);
                    
                    if ($val === 1) {
                        $this->db->prepare("UPDATE resena SET 
                                            Util_Positivo = IFNULL(Util_Positivo, 0) + 1, 
                                            Util_Negativo = GREATEST(IFNULL(Util_Negativo, 0) - 1, 0) 
                                            WHERE ID_Resena = ?")
                                 ->execute([$idResena]);
                    } else {
                        $this->db->prepare("UPDATE resena SET 
                                            Util_Negativo = IFNULL(Util_Negativo, 0) + 1, 
                                            Util_Positivo = GREATEST(IFNULL(Util_Positivo, 0) - 1, 0) 
                                            WHERE ID_Resena = ?")
                                 ->execute([$idResena]);
                    }
                    $user_vote = $val === 1 ? 'positivo' : 'negativo';
                }
            } else {
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
        $sql = "SELECT ID_Reporte FROM resena_reporte WHERE ID_Resena = ? AND ID_Usuario = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idResena, $idUsuario]);
        if ($stmt->fetch()) {
            throw new Exception('Ya has reportado esta reseña anteriormente.');
        }
        
        $sql = "INSERT INTO resena_reporte (ID_Resena, ID_Usuario, Motivo, Descripcion, Fecha_Reporte, Estado)
                VALUES (?, ?, ?, ?, NOW(), 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idResena, $idUsuario, $motivo, $descripcion]);
    }

    public function editarResena($idResena, $titulo, $comentario, $calificacion){
        try {
            $this->db->beginTransaction();
            
            $resena = $this->obtenerPorIdResena($idResena);
            if (!$resena) {
                throw new Exception("Reseña no encontrada");
            }
            
            $texto_completo = ($titulo ? $titulo . ' ' : '') . $comentario;
            $verificacion = $this->verificarPalabrasBloqueadas($texto_completo);
            
            if ($verificacion['tiene_bloqueadas'] && $verificacion['gravedad_maxima'] === 'Grave') {
                $palabras_str = implode(', ', array_column($verificacion['palabras'], 'palabra'));
                $this->crearAdvertencia(
                    $resena['ID_Usuario'],
                    null,
                    'Intento de edición con lenguaje inapropiado',
                    "Intento de editar reseña #$idResena con palabras graves: $palabras_str",
                    'Grave'
                );
                
                throw new Exception('La edición contiene lenguaje inapropiado y ha sido rechazada. Has recibido una advertencia.');
            }
            
            $titulo_filtrado = $titulo ? $this->filtrarTexto($titulo) : null;
            $comentario_filtrado = $this->filtrarTexto($comentario);
            
            $sql = "UPDATE resena SET Titulo = ?, Comentario = ?, Calificacion = ?, Fecha_Actualizacion = NOW(), Modificado = 1 WHERE ID_Resena = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$titulo_filtrado, $comentario_filtrado, $calificacion, $idResena]);
            
            if ($verificacion['tiene_bloqueadas']) {
                $palabras_str = implode(', ', array_column($verificacion['palabras'], 'palabra'));
                $this->crearAdvertencia(
                    $resena['ID_Usuario'],
                    null,
                    'Lenguaje inapropiado en edición de reseña',
                    "La edición de la reseña contenía palabras inapropiadas: $palabras_str. El texto fue filtrado automáticamente.",
                    $verificacion['gravedad_maxima']
                );
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en editarResena: " . $e->getMessage());
            throw $e;
        }
    }

    public function responderResena($idResena, $idUsuario, $respuesta){
        try {
            $this->db->beginTransaction();
            
            $verificacion = $this->verificarPalabrasBloqueadas($respuesta);
            
            if ($verificacion['tiene_bloqueadas'] && $verificacion['gravedad_maxima'] === 'Grave') {
                throw new Exception('La respuesta contiene lenguaje inapropiado y no puede ser publicada.');
            }
            
            $respuesta_filtrada = $this->filtrarTexto($respuesta);
            
            $sql = "UPDATE resena_respuesta SET Activo = 0 WHERE ID_Resena = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idResena]);
            
            $sql = "INSERT INTO resena_respuesta (ID_Resena, ID_Usuario, Respuesta, Fecha_Respuesta, Activo)
                    VALUES (?, ?, ?, NOW(), 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idResena, $idUsuario, $respuesta_filtrada]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
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
            $this->db->prepare("DELETE FROM resena_reporte WHERE ID_Resena = ?")->execute([$idResena]);
            
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

    public function obtenerReportesPendientes(){
        $sql = "SELECT rp.*, r.ID_Usuario as ID_AutorResena, u.Nombre, u.Apellido, 
                u2.Nombre as NombreReportante, u2.Apellido as ApellidoReportante
                FROM resena_reporte rp
                INNER JOIN resena r ON rp.ID_Resena = r.ID_Resena
                INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                INNER JOIN usuario u2 ON rp.ID_Usuario = u2.ID_Usuario
                WHERE rp.Estado = 'Pendiente'
                ORDER BY rp.Fecha_Reporte ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function procesarReporte($idReporte, $estado, $idAdmin){
        $sql = "UPDATE resena_reporte SET Estado = ? WHERE ID_Reporte = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $idReporte]);
    }

    public function obtenerTodasResenas($filtros = [], $limite = null, $offset = null){
        $sql = "SELECT r.*, u.Nombre, u.Apellido, u.Correo, a.N_Articulo,
                IFNULL(r.Util_Positivo, 0) as Util_Positivo,
                IFNULL(r.Util_Negativo, 0) as Util_Negativo,
                (SELECT COUNT(*) FROM resena_reporte WHERE ID_Resena = r.ID_Resena) AS reportes_count
                FROM resena r
                LEFT JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                LEFT JOIN articulo a ON r.ID_Articulo = a.ID_Articulo";
        
        $where = [];
        $params = [];
        
        if (!empty($filtros['usuario'])) {
            $where[] = " (u.Nombre LIKE ? OR u.Apellido LIKE ? OR u.Correo LIKE ?) ";
            $term = '%' . $filtros['usuario'] . '%';
            $params[] = $term; $params[] = $term; $params[] = $term;
        }
        
        if (!empty($filtros['articulo'])) {
            $where[] = " a.N_Articulo LIKE ? ";
            $params[] = '%' . $filtros['articulo'] . '%';
        }
        
        if (!empty($filtros['calificacion'])) {
            $where[] = " r.Calificacion = ? ";
            $params[] = $filtros['calificacion'];
        }
        
        if (!empty($filtros['fecha_from'])) {
            $where[] = " r.Fecha_Resena >= ? ";
            $params[] = $filtros['fecha_from'];
        }
        
        if (!empty($filtros['fecha_to'])) {
            $where[] = " r.Fecha_Resena <= ? ";
            $params[] = $filtros['fecha_to'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY r.Fecha_Resena DESC";
        
        if ($limite !== null) {
            $sql .= " LIMIT " . (int)$limite;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarTodasResenas($filtros = []){
        $sql = "SELECT COUNT(*) FROM resena r
                LEFT JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                LEFT JOIN articulo a ON r.ID_Articulo = a.ID_Articulo";
        
        $where = [];
        $params = [];
        
        if (!empty($filtros['usuario'])) {
            $where[] = " (u.Nombre LIKE ? OR u.Apellido LIKE ? OR u.Correo LIKE ?) ";
            $term = '%' . $filtros['usuario'] . '%';
            $params[] = $term; $params[] = $term; $params[] = $term;
        }
        
        if (!empty($filtros['articulo'])) {
            $where[] = " a.N_Articulo LIKE ? ";
            $params[] = '%' . $filtros['articulo'] . '%';
        }
        
        if (!empty($filtros['calificacion'])) {
            $where[] = " r.Calificacion = ? ";
            $params[] = $filtros['calificacion'];
        }
        
        if (!empty($filtros['fecha_from'])) {
            $where[] = " r.Fecha_Resena >= ? ";
            $params[] = $filtros['fecha_from'];
        }
        
        if (!empty($filtros['fecha_to'])) {
            $where[] = " r.Fecha_Resena <= ? ";
            $params[] = $filtros['fecha_to'];
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}