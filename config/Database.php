<?php

class Database {
    // Parámetros de la base de datos
    private $host = "localhost";
    private $db_name = "zooki_db";
    private $username = "root"; // Usuario por defecto en XAMPP
    private $password = "";     // Contraseña por defecto en XAMPP
    public $conn;

    // Método para obtener la conexión
    public function getConnection() {
        $this->conn = null;

        try {
            // Usamos PDO que es el estándar más seguro y profesional hoy en día
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            
            // Configuramos PDO para que lance excepciones si hay errores (muy útil para depurar)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Hacemos que los resultados se devuelvan como un array asociativo por defecto
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            echo "Error de conexión a la base de datos: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
