<?php
require_once "models/Resena.php";
require_once "models/Database.php";

class ResenaController {
    private $db;
    private $resenaModel;

    public function __construct($db = null){
        $this->db = $db ?: (new Database())->getConnection();
        $this->resenaModel = new Resena($this->db);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // POST: reportar
    public function reportar(){
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            if($isAjax){
                http_response_code(405);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
                exit;
            }
            header('Location: ' . BASE_URL);
            exit;
        }
        if(!isset($_SESSION['ID_Usuario'])){
            if($isAjax){
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'login_required']);
                exit;
            }
            header('Location: ' . BASE_URL . '?c=Usuario&a=login');
            exit;
        }
        $idResena = (int)($_POST['id_resena'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? null);

        if(!$idResena || $motivo === ''){
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode(['success'=>false,'error'=>'invalid_parameters']); 
                exit; 
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL)); 
            exit;
        }

        $r = $this->resenaModel->obtenerPorIdResena($idResena);
        if($r && (int)$r['ID_Usuario'] === (int)$_SESSION['ID_Usuario']){
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode(['success'=>false,'error'=>'cannot_report_own']); 
                exit; 
            }
            $_SESSION['error_message'] = 'No puedes reportar tu propia reseña.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL)); 
            exit;
        }

        try {
            $ok = $this->resenaModel->reportar($idResena, (int)$_SESSION['ID_Usuario'], $motivo, $descripcion);
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode(['success'=> (bool)$ok]); 
                exit; 
            }
        } catch (Exception $e) {
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400);
                echo json_encode(['success'=> false, 'error' => $e->getMessage()]); 
                exit; 
            }
            $_SESSION['error_message'] = $e->getMessage();
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    }

    // POST: crear reseña
    public function crear(){
        try {
            if($_SERVER['REQUEST_METHOD'] !== 'POST'){
                header('Location: ' . BASE_URL);
                exit;
            }
            
            $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

            if(!isset($_SESSION['ID_Usuario'])){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(401); 
                    echo json_encode(['success' => false, 'error' => 'login_required']); 
                    exit; 
                }
                $_SESSION['error_message'] = 'Debes iniciar sesión para dejar una reseña.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
                exit;
            }

            $idUsuario = (int) $_SESSION['ID_Usuario'];
            $idArticulo = isset($_POST['id_articulo']) ? (int)$_POST['id_articulo'] : null;
            $idProducto = isset($_POST['id_producto']) && $_POST['id_producto'] !== '' ? (int)$_POST['id_producto'] : null;
            $calificacion = isset($_POST['calificacion']) ? (int)$_POST['calificacion'] : 5;
            $titulo = trim($_POST['titulo'] ?? null);
            $comentario = trim($_POST['comentario'] ?? '');

            if(!$idArticulo){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(400); 
                    echo json_encode(['success' => false, 'error' => 'missing_id']); 
                    exit; 
                }
                header('Location: ' . BASE_URL);
                exit;
            }

            if(empty($comentario)){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(400); 
                    echo json_encode(['success' => false, 'error' => 'El comentario es obligatorio']); 
                    exit; 
                }
                $_SESSION['error_message'] = 'El comentario es obligatorio.';
                header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
                exit;
            }

            if(!$this->resenaModel->usuarioCompro($idUsuario, $idArticulo)){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'error' => 'Debes comprar el producto para dejar una reseña']); 
                    exit; 
                }
                $_SESSION['error_message'] = 'Solo compradores pueden dejar reseñas.';
                header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
                exit;
            }

            $ex = $this->resenaModel->obtenerPorUsuarioArticulo($idUsuario, $idArticulo);
            if($ex){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(409); 
                    echo json_encode(['success' => false, 'error' => 'Ya dejaste una reseña en este producto']); 
                    exit; 
                }
                $_SESSION['info_message'] = 'Ya dejaste una reseña en este producto. Puedes editarla o eliminarla.';
                header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
                exit;
            }

            if ($idProducto === null || $idProducto == 0) {
                $stmt = $this->db->prepare("SELECT ID_Producto FROM producto WHERE ID_Articulo = ? AND Activo = 1 LIMIT 1");
                $stmt->execute([$idArticulo]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $idProducto = $row ? (int)$row['ID_Producto'] : null;
            }
            
            if (!$idProducto) {
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(400); 
                    echo json_encode(['success' => false, 'error' => 'No hay productos disponibles']); 
                    exit; 
                }
                $_SESSION['error_message'] = 'No hay productos disponibles para este artículo.';
                header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
                exit;
            }
            
            $exProducto = $this->resenaModel->obtenerPorUsuarioProducto($idUsuario, $idProducto);
            if($exProducto){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(409); 
                    echo json_encode(['success' => false, 'error' => 'Ya dejaste una reseña en este producto']); 
                    exit; 
                }
                $_SESSION['info_message'] = 'Ya dejaste una reseña en este producto. Puedes editarla o eliminarla.';
                header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
                exit;
            }

            $idResena = $this->resenaModel->crearResena($idUsuario, $idArticulo, $idProducto, $calificacion, $titulo, $comentario);
            
            if ($idResena && isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $this->procesarSubidaFotos($idResena, $_FILES['fotos']);
            }
            
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode([
                    'success' => true, 
                    'id_resena' => $idResena,
                    'message' => 'Reseña creada exitosamente'
                ]); 
                exit; 
            }
            
            $_SESSION['success_message'] = 'Reseña creada exitosamente.';
            header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo . '#resenas');
            
        } catch (Exception $e) {
            $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            
            $errorMsg = $e->getMessage();
            
            if (strpos($errorMsg, 'Duplicate entry') !== false || 
                strpos($errorMsg, 'unique_resena_usuario_producto') !== false) {
                $errorMsg = 'Ya dejaste una reseña en este producto. Puedes editarla o eliminarla.';
            }
            
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400); 
                echo json_encode(['success' => false, 'error' => $errorMsg]); 
                exit; 
            }
            $_SESSION['error_message'] = $errorMsg;
            header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . ($idArticulo ?? ''));
            exit;
        }
    }

    // POST: responder reseña
    public function responder(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        if(!isset($_SESSION['ID_Usuario']) || ($_SESSION['ID_Rol'] ?? 3) == 3){
            if($isAjax){
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'No autorizado']);
                exit;
            }
            die('No autorizado');
        }
        
        $idResena = (int)($_POST['id_resena'] ?? 0);
        $respuesta = trim($_POST['respuesta'] ?? '');
        
        if(!$idResena || empty($respuesta)){
            if($isAjax){
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
                exit;
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            exit;
        }
        
        try {
            $this->resenaModel->responderResena($idResena, (int)$_SESSION['ID_Usuario'], $respuesta);
            
            if($isAjax){
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Respuesta publicada exitosamente']);
                exit;
            }
            
            $_SESSION['success_message'] = 'Respuesta publicada exitosamente.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            
        } catch (Exception $e) {
            if($isAjax){
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        }
    }

    // POST: votar
    public function votar(){
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            if($isAjax){
                http_response_code(405);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
                exit;
            }
            header('Location: ' . BASE_URL);
            exit;
        }

        if(!isset($_SESSION['ID_Usuario'])){
            if($isAjax){
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'login_required']);
                exit;
            }
            header('Location: ' . BASE_URL . '?c=Usuario&a=login');
            exit;
        }

        try{
            $idResena = (int)($_POST['id_resena'] ?? 0);
            $tipo = $_POST['tipo'] ?? 'positivo';
            $esPositivo = in_array(strtolower($tipo), ['positivo','1','true','yes','like']);

            if(!$idResena){
                if($isAjax){
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'invalid_parameters']);
                    exit;
                }
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
                exit;
            }

            $result = $this->resenaModel->votar($idResena, (int)$_SESSION['ID_Usuario'], $esPositivo);

            if($isAjax){
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(200);
                echo json_encode($result);
                exit;
            }

            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            
        } catch(Throwable $e){
            error_log("Error en votar: " . $e->getMessage());
            if($isAjax){
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'Error al procesar el voto']);
                exit;
            }
            throw $e;
        }
    }

    // GET: listar reseñas
    public function listar(){
        $idArticulo = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if(!$idArticulo){
            echo json_encode(['success' => false]);
            return;
        }
        $resenas = $this->resenaModel->obtenerResenas($idArticulo);
        $stats = $this->resenaModel->obtenerEstadisticas($idArticulo);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $resenas, 'stats' => $stats]);
    }

    // POST: editar reseña
    public function editar(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASE_URL);
            exit;
        }
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        if(!isset($_SESSION['ID_Usuario'])){
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(401); 
                echo json_encode(['success' => false, 'error' => 'login_required']); 
                exit; 
            }
            header('Location: ' . BASE_URL . '?c=Usuario&a=login');
            exit;
        }
        
        $idResena = (int)($_POST['id_resena'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? null);
        $comentario = trim($_POST['comentario'] ?? '');
        $calificacion = isset($_POST['calificacion']) ? (int)$_POST['calificacion'] : 5;
        
        if(!$idResena){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400); 
                echo json_encode(['success' => false, 'error' => 'missing_id']); 
                exit; 
            } 
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL)); 
            exit; 
        }

        if(empty($comentario)){
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400); 
                echo json_encode(['success' => false, 'error' => 'El comentario es obligatorio']); 
                exit; 
            }
            $_SESSION['error_message'] = 'El comentario es obligatorio.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            exit;
        }

        $r = $this->resenaModel->obtenerPorIdResena($idResena);
        if(!$r){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(404); 
                echo json_encode(['success' => false, 'error' => 'not_found']); 
                exit; 
            } 
            die('Reseña no encontrada'); 
        }

        $esAutor = ((int)$r['ID_Usuario'] === (int)$_SESSION['ID_Usuario']);
        $esAdmin = (($_SESSION['ID_Rol'] ?? 3) != 3);
        
        if(!$esAutor && !$esAdmin){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(403); 
                echo json_encode(['success' => false, 'error' => 'unauthorized']); 
                exit; 
            } 
            die('No autorizado'); 
        }

        try {
            $this->resenaModel->editarResena($idResena, $titulo, $comentario, $calificacion);
            
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode(['success' => true, 'message' => 'Reseña actualizada exitosamente']); 
                exit; 
            }
            
            $_SESSION['success_message'] = 'Reseña actualizada exitosamente.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            
        } catch (Exception $e) {
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400); 
                echo json_encode(['success' => false, 'error' => $e->getMessage()]); 
                exit; 
            }
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        }
    }

    // POST: eliminar reseña
    public function eliminar(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASE_URL);
            exit;
        }
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        if(!isset($_SESSION['ID_Usuario'])){
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(401); 
                echo json_encode(['success' => false, 'error' => 'login_required']); 
                exit; 
            }
            header('Location: ' . BASE_URL . '?c=Usuario&a=login');
            exit;
        }
        
        $idResena = (int)($_POST['id_resena'] ?? 0);
        if(!$idResena){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400); 
                echo json_encode(['success' => false, 'error' => 'missing_id']); 
                exit; 
            } 
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL)); 
            exit; 
        }

        $r = $this->resenaModel->obtenerPorIdResena($idResena);
        if(!$r){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(404); 
                echo json_encode(['success' => false, 'error' => 'not_found']); 
                exit; 
            } 
            die('Reseña no encontrada'); 
        }

        $esAutor = ((int)$r['ID_Usuario'] === (int)$_SESSION['ID_Usuario']);
        $esAdmin = (($_SESSION['ID_Rol'] ?? 3) != 3);
        
        if(!$esAutor && !$esAdmin){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(403); 
                echo json_encode(['success' => false, 'error' => 'unauthorized']); 
                exit; 
            } 
            die('No autorizado'); 
        }

        try {
            $this->resenaModel->eliminarResena($idResena);
            
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode(['success' => true, 'message' => 'Reseña eliminada exitosamente']); 
                exit; 
            }
            
            $_SESSION['success_message'] = 'Reseña eliminada exitosamente.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            
        } catch (Exception $e) {
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(500); 
                echo json_encode(['success' => false, 'error' => $e->getMessage()]); 
                exit; 
            }
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        }
    }

    // POST: subir foto
    public function subirFoto(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        if(!isset($_SESSION['ID_Usuario'])){
            if($isAjax){
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'login_required']);
                exit;
            }
            header('Location: ' . BASE_URL . '?c=Usuario&a=login');
            exit;
        }
        
        if(empty($_POST['id_resena'])) { 
            if($isAjax){
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'missing_resena_id']);
                exit;
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL)); 
            exit; 
        }
        
        $idResena = (int)$_POST['id_resena'];

        $r = $this->resenaModel->obtenerPorIdResena($idResena);
        if(!$r) {
            if($isAjax){
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'resena_not_found']);
                exit;
            }
            die('Reseña no encontrada');
        }

        $esAutor = ((int)$r['ID_Usuario'] === (int)$_SESSION['ID_Usuario']);
        $esAdmin = (($_SESSION['ID_Rol'] ?? 3) != 3);
        
        if(!$esAutor && !$esAdmin) {
            if($isAjax){
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'unauthorized']);
                exit;
            }
            die('No autorizado');
        }

        if(!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK){
            if($isAjax){
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'upload_error']);
                exit;
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            exit;
        }

        $resultado = $this->procesarUnaFoto($idResena, $_FILES['foto']);
        
        if($isAjax){
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;
        }
        
        if($resultado['success']){
            $_SESSION['success_message'] = 'Foto subida exitosamente.';
        } else {
            $_SESSION['error_message'] = $resultado['error'];
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    }

    // POST: eliminar foto
    public function eliminarFoto(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        if(!isset($_SESSION['ID_Usuario'])){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(401); 
                echo json_encode(['success' => false, 'error' => 'login_required']); 
                exit; 
            }
            die('Login requerido'); 
        }
        
        $idFoto = (int)($_POST['id_foto'] ?? 0);
        if(!$idFoto){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(400); 
                echo json_encode(['success' => false, 'error' => 'missing_id']); 
                exit; 
            }
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL)); 
            exit; 
        }
        
        $foto = $this->resenaModel->obtenerFotoPorId($idFoto);
        if(!$foto){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(404); 
                echo json_encode(['success' => false, 'error' => 'not_found']); 
                exit; 
            }
            die('Foto no encontrada'); 
        }
        
        $resena = $this->resenaModel->obtenerPorIdResena($foto['ID_Resena']);
        if(!$resena){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(404); 
                echo json_encode(['success' => false, 'error' => 'resena_not_found']); 
                exit; 
            }
            die('Reseña no encontrada'); 
        }
        
        $esAutor = ((int)$resena['ID_Usuario'] === (int)$_SESSION['ID_Usuario']);
        $esAdmin = (($_SESSION['ID_Rol'] ?? 3) != 3);
        
        if(!$esAutor && !$esAdmin){ 
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code(403); 
                echo json_encode(['success' => false, 'error' => 'unauthorized']); 
                exit; 
            }
            die('No autorizado'); 
        }
        
        if(file_exists($foto['Foto'])){
            @unlink($foto['Foto']);
        }
        
        $this->resenaModel->eliminarFoto($idFoto);
        
        if($isAjax){ 
            header('Content-Type: application/json'); 
            echo json_encode(['success' => true, 'message' => 'Foto eliminada exitosamente']); 
            exit; 
        }
        
        $_SESSION['success_message'] = 'Foto eliminada exitosamente.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    }

    // =============== MÉTODOS AUXILIARES ===============

    private function procesarSubidaFotos($idResena, $files){
        $uploadDir = 'uploads/resenas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $maxFotos = 5;
        $contador = 0;

        for ($i = 0; $i < count($files['name']) && $contador < $maxFotos; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK && !empty($files['name'][$i])) {
                $this->procesarArchivoFoto($idResena, $files['tmp_name'][$i], $files['name'][$i]);
                $contador++;
            }
        }
    }

    private function procesarUnaFoto($idResena, $file){
        $uploadDir = 'uploads/resenas/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir el archivo'];
        }

        $resultado = $this->procesarArchivoFoto($idResena, $file['tmp_name'], $file['name']);
        
        if($resultado){
            return ['success' => true, 'foto_url' => $resultado];
        } else {
            return ['success' => false, 'error' => 'Error al procesar la imagen'];
        }
    }

    private function procesarArchivoFoto($idResena, $tmpFile, $originalName){
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $extPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($ext, $extPermitidas)) {
            return false;
        }

        if (filesize($tmpFile) > 5 * 1024 * 1024) {
            return false;
        }

        $newFileName = uniqid('resena_' . $idResena . '_') . '.' . $ext;
        $uploadPath = 'uploads/resenas/' . $newFileName;

        if (move_uploaded_file($tmpFile, $uploadPath)) {
            $this->resenaModel->agregarFoto($idResena, $uploadPath);
            return $uploadPath;
        }

        return false;
    }

    public function reportesPendientes(){
        if(!isset($_SESSION['ID_Usuario']) || ($_SESSION['ID_Rol'] ?? 3) == 3){
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $reportes = $this->resenaModel->obtenerReportesPendientes();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $reportes]);
    }

    public function procesarReporte(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['ID_Usuario']) || 
           ($_SESSION['ID_Rol'] ?? 3) == 3){
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        
        $idReporte = (int)($_POST['id_reporte'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        
        if(!$idReporte || !in_array($estado, ['Revisado', 'Descartado'])){
            if($isAjax){
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
                exit;
            }
            $_SESSION['error_message'] = 'Parámetros inválidos';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
            exit;
        }
        
        $this->resenaModel->procesarReporte($idReporte, $estado, $_SESSION['ID_Usuario']);
        
        if($isAjax){
            echo json_encode(['success' => true, 'message' => 'Reporte procesado exitosamente']);
            exit;
        }
        
        $_SESSION['success_message'] = 'Reporte procesado exitosamente.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    }
}