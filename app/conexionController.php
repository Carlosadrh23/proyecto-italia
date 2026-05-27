<?php

class conexionController {

    private $host = 'localhost';
    private $dbname = 'homeaway';
    private $username = 'root';
    private $password = '';
    private $conexion;

    public function __construct() {

        try {

            $this->conexion = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {

            die("Error conexión BD: " . $e->getMessage());

        }
    }

    public function getConexion() {
        return $this->conexion;
    }
}
?>