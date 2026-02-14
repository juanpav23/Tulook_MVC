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

        $ok = $this->resenaModel->reportar($idResena, (int)$_SESSION['ID_Usuario'], $motivo, $descripcion);
        if($isAjax){ 
            header('Content-Type: application/json'); 
            echo json_encode(['success'=> (bool)$ok]); 
            exit; 
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
                header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL);
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

            if(!$this->resenaModel->usuarioCompro($idUsuario, $idArticulo)){
                if($isAjax){ 
                    header('Content-Type: application/json'); 
                    http_response_code(403); 
                    echo json_encode(['success' => false, 'error' => 'primero debes comprar el producto para dejar una reseña']); 
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
                    echo json_encode(['success' => false, 'error' => 'exists']); 
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
                    echo json_encode(['success' => false, 'error' => 'no_product_available']); 
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
                    echo json_encode(['success' => false, 'error' => 'exists']); 
                    exit; 
                }
                $_SESSION['info_message'] = 'Ya dejaste una reseña en este producto. Puedes editarla o eliminarla.';
                header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
                exit;
            }

            $idResena = $this->resenaModel->crearResena($idUsuario, $idArticulo, $idProducto, $calificacion, $titulo, $comentario);
            
            if ($idResena && isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $uploadDir = 'uploads/resenas/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $files = $_FILES['fotos'];
                $maxFotos = 5;

                for ($i = 0; $i < count($files['name']) && $i < $maxFotos; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK && !empty($files['name'][$i])) {
                        $tmpFile = $files['tmp_name'][$i];
                        $fileName = $files['name'][$i];
                        
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            continue;
                        }

                        $newFileName = uniqid('resena_' . $idResena . '_') . '.' . $ext;
                        $uploadPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($tmpFile, $uploadPath)) {
                            $this->resenaModel->agregarFoto($idResena, $uploadPath);
                        }
                    }
                }
            }
            
            if($isAjax){ 
                header('Content-Type: application/json'); 
                echo json_encode(['success' => true, 'id_resena' => $idResena]); 
                exit; 
            }
            header('Location: ' . BASE_URL . '?c=Producto&a=ver&id=' . $idArticulo);
            
        } catch (Throwable $e) {
            $isAjax = (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1') || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'unique_resena_usuario_producto') !== false || 
                (strpos($errorMsg, 'Integrity constraint violation') !== false && strpos($errorMsg, 'Duplicate entry') !== false)) {
                $errorMsg = 'Ya dejaste una reseña en este producto. Puedes editarla o eliminarla.';
                $httpCode = 409;
            } else {
                $errorMsg = 'Error al crear la reseña. Intenta nuevamente.';
                $httpCode = 500;
            }
            
            if($isAjax){ 
                header('Content-Type: application/json'); 
                http_response_code($httpCode); 
                echo json_encode(['success' => false, 'error' => $errorMsg]); 
                exit; 
            }
            error_log('Error en ResenaController::crear: ' . $e->getMessage());
            throw $e;
        }
    }

    // POST: responder reseña
    public function responder(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            header('Location: ' . BASE_URL);
            exit;
        }
        if(!isset($_SESSION['ID_Usuario']) || ($_SESSION['ID_Rol'] ?? 3) == 3){
            die('No autorizado');
        }
        $idResena = (int)($_POST['id_resena'] ?? 0);
        $respuesta = trim($_POST['respuesta'] ?? '');
        if($idResena && $respuesta !== ''){
            $this->resenaModel->responderResena($idResena, (int)$_SESSION['ID_Usuario'], $respuesta);
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    }

    // POST: votar - VERSIÓN CORREGIDA
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

        $this->resenaModel->editarResena($idResena, $titulo, $comentario, $calificacion);
        
        if($isAjax){ 
            header('Content-Type: application/json'); 
            echo json_encode(['success' => true]); 
            exit; 
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
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

        $this->resenaModel->eliminarResena($idResena);
        
        if($isAjax){ 
            header('Content-Type: application/json'); 
            echo json_encode(['success' => true]); 
            exit; 
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
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

        $dir = __DIR__ . '/../uploads/resenas/';
        if(!is_dir($dir)) mkdir($dir, 0755, true);

        $f = $_FILES['foto'];
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $nombre = uniqid('resena_') . '.' . $ext;
        $dest = $dir . $nombre;
        
        if(move_uploaded_file($f['tmp_name'], $dest)){
            $rutaRel = 'uploads/resenas/' . $nombre;
            $this->resenaModel->agregarFoto($idResena, $rutaRel);
            
            if($isAjax){
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'foto_url' => $rutaRel]);
                exit;
            }
        } else {
            if($isAjax){
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'move_failed']);
                exit;
            }
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
        
        $this->resenaModel->eliminarFoto($idFoto);
        
        if($isAjax){ 
            header('Content-Type: application/json'); 
            echo json_encode(['success' => true]); 
            exit; 
        }
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
    }
}