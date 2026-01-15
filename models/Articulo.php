<?php
class Articulo {
    private $conn;
    private $table = "articulo";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $sql = "SELECT * FROM articulo";
        return $this->conn->query($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM articulo WHERE ID_Articulo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO articulo (N_Articulo, Foto, ID_Categoria, ID_SubCategoria, ID_Color, ID_Talla, ID_Genero, ID_Precio, Cantidad, Activo, Destacado, Fecha_Creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['N_Articulo'], $data['Foto'], $data['ID_Categoria'], $data['ID_SubCategoria'],
            $data['ID_Color'], $data['ID_Talla'], $data['ID_Genero'], $data['ID_Precio'],
            $data['Cantidad'], $data['Activo'], $data['Destacado']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE articulo SET 
                N_Articulo=?, Foto=?, ID_Categoria=?, ID_SubCategoria=?, ID_Color=?, ID_Talla=?, 
                ID_Genero=?, ID_Precio=?, Cantidad=?, Activo=?, Destacado=? 
                WHERE ID_Articulo=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['N_Articulo'], $data['Foto'], $data['ID_Categoria'], $data['ID_SubCategoria'],
            $data['ID_Color'], $data['ID_Talla'], $data['ID_Genero'], $data['ID_Precio'],
            $data['Cantidad'], $data['Activo'], $data['Destacado'], $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM articulo WHERE ID_Articulo = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>
