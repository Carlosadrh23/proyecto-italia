<?php

require_once 'conexionController.php';

class UserModel {

    private $conexion;

    public function __construct() {

        $db = new conexionController();
        $this->conexion = $db->getConexion();

    }

    // REGISTRAR USUARIO
    public function registrarUsuario($nombre, $email, $password) {

        try {

            // Verificar email existente
            $sql = "SELECT id FROM usuarios WHERE email = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$email]);

            if($stmt->fetch()) {

                return [
                    'success' => false,
                    'message' => 'El correo ya está registrado'
                ];
            }

            // Encriptar contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $sql = "INSERT INTO usuarios(nombre, email, password)
                    VALUES(?,?,?)";

            $stmt = $this->conexion->prepare($sql);

            if($stmt->execute([$nombre, $email, $passwordHash])) {

                return [
                    'success' => true,
                    'message' => 'Usuario registrado correctamente',
                    'user_id' => $this->conexion->lastInsertId()
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al registrar usuario'
            ];

        } catch(PDOException $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        }
    }

    // LOGIN
    public function iniciarSesion($email, $password) {

        try {

            $sql = "SELECT * FROM usuarios WHERE email = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$email]);

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$usuario) {

                return [
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ];
            }

            if(!password_verify($password, $usuario['password'])) {

                return [
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ];
            }

            return [
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email']
                ]
            ];

        } catch(PDOException $e) {

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];

        }
    }
}
?>