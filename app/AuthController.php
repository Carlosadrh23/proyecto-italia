<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'UserModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$data = $data ?: $_POST;

$accion = $data['accion'] ?? '';

$model = new UserModel();

switch ($accion) {

    // ==========================================
    // REGISTRO
    // ==========================================
    case 'registro':

        $resultado = $model->registrarUsuario(
            $data['nombre'],
            $data['email'],
            $data['password']
        );

        if ($resultado['success']) {

            $_SESSION['user_id'] = $resultado['user_id'];
            $_SESSION['nombre'] = $data['nombre'];
            $_SESSION['email'] = $data['email'];
        }

        echo json_encode($resultado);

    break;


    // ==========================================
    // LOGIN
    // ==========================================
    case 'login':

        $resultado = $model->iniciarSesion(
            $data['email'],
            $data['password']
        );

        if ($resultado['success']) {

            $_SESSION['user_id'] =
                $resultado['usuario']['id'];

            $_SESSION['nombre'] =
                $resultado['usuario']['nombre'];

            $_SESSION['email'] =
                $resultado['usuario']['email'];
        }

        echo json_encode($resultado);

    break;


    // ==========================================
    // OBTENER PERFIL
    // ==========================================
    case 'obtener_perfil':

        if (!isset($_SESSION['user_id'])) {

            echo json_encode([
                'success' => false,
                'message' => 'No has iniciado sesión'
            ]);

            exit;
        }

        $userId = $_SESSION['user_id'];

        $conexion = new mysqli(
            "localhost",
            "root",
            "",
            "homeaway"
        );

        if ($conexion->connect_error) {

            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión'
            ]);

            exit;
        }

        $conexion->set_charset("utf8");

        $sql = "SELECT
                    id,
                    nombre,
                    email,
                    fecha_registro
                FROM usuarios
                WHERE id = $userId";

        $resultadoConsulta =
            $conexion->query($sql);

        if ($resultadoConsulta &&
            $resultadoConsulta->num_rows > 0) {

            $usuario =
                $resultadoConsulta->fetch_assoc();

            echo json_encode([
                'success' => true,
                'usuario' => $usuario
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }

    break;


    // ==========================================
    // LOGOUT
    // ==========================================
    case 'logout':

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);

    break;


    // ==========================================
    // VERIFICAR SESIÓN
    // ==========================================
    case 'verificar_sesion':

        if (isset($_SESSION['user_id'])) {

            echo json_encode([
                'success' => true,
                'logueado' => true,
                'usuario' => [
                    'id' => $_SESSION['user_id'],
                    'nombre' => $_SESSION['nombre'],
                    'email' => $_SESSION['email']
                ]
            ]);

        } else {

            echo json_encode([
                'success' => true,
                'logueado' => false
            ]);
        }

    break;


    // ==========================================
    // ACCIÓN INVÁLIDA
    // ==========================================
    default:

        echo json_encode([
            'success' => false,
            'message' => 'Acción inválida'
        ]);
}
?>