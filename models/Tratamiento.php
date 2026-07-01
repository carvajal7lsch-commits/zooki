<?php
class Tratamiento {
    private $conn;
    private $table_name = "tratamientos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function insert($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_consulta, medicamento, dosis, via_administracion, duracion, observaciones) 
                  VALUES (:id_consulta, :medicamento, :dosis, :via, :duracion, :obs)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_consulta', $data['id_consulta']);
        $stmt->bindParam(':medicamento', $data['medicamento']);
        $stmt->bindParam(':dosis', $data['dosis']);
        $stmt->bindParam(':via', $data['via_administracion']);
        $stmt->bindParam(':duracion', $data['duracion']);
        $stmt->bindParam(':obs', $data['observaciones']);
        
        return $stmt->execute();
    }

    public function findByConsulta($id_consulta) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_consulta = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_consulta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
