<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'UserModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$data = $data ?: $_POST;

$accion = $data['accion'] ?? '';

// Subir foto usa $_FILES, no JSON
if(isset($_FILES['foto'])){
    $accion = 'subir_foto';
}

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
            $_SESSION['nombre']  = $data['nombre'];
            $_SESSION['email']   = $data['email'];
            $_SESSION['foto']    = null;
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

            $_SESSION['user_id'] = $resultado['usuario']['id'];
            $_SESSION['nombre']  = $resultado['usuario']['nombre'];
            $_SESSION['email']   = $resultado['usuario']['email'];
            $_SESSION['foto']    = $resultado['usuario']['foto'] ?? null;
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
                    fecha_registro,
                    foto
                FROM usuarios
                WHERE id = $userId";

        $resultadoConsulta = $conexion->query($sql);

        if ($resultadoConsulta &&
            $resultadoConsulta->num_rows > 0) {

            $usuario = $resultadoConsulta->fetch_assoc();

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
    // SUBIR FOTO
    // ==========================================
    case 'subir_foto':

        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'No has iniciado sesión'
            ]);
            exit;
        }

        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No se recibió la foto'
            ]);
            exit;
        }

        $archivo = $_FILES['foto'];

        // Validar tipo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!in_array($archivo['type'], $tiposPermitidos)) {
            echo json_encode([
                'success' => false,
                'message' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF'
            ]);
            exit;
        }

        // Validar tamaño (max 5MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            echo json_encode([
                'success' => false,
                'message' => 'La imagen no puede pesar más de 5MB'
            ]);
            exit;
        }

        $userId = $_SESSION['user_id'];

        $extension    = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'usuario_' . $userId . '_' . time() . '.' . $extension;
        $rutaDestino  = __DIR__ . '/../assets/img/usuarios/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar la imagen'
            ]);
            exit;
        }

        // Guardar en BD
        $conexion = new mysqli("localhost", "root", "", "homeaway");
        $conexion->set_charset("utf8");

        $sql = "UPDATE usuarios SET foto = '$nombreArchivo' WHERE id = $userId";

        if ($conexion->query($sql)) {

            $_SESSION['foto'] = $nombreArchivo;

            echo json_encode([
                'success' => true,
                'message' => 'Foto actualizada',
                'foto'    => $nombreArchivo
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar en base de datos'
            ]);
        }

    break;


    // ==========================================
    // LOGOUT
    // ==========================================
    case 'logout':

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params = session_get_cookie_params();

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
                'usuario'  => [
                    'id'     => $_SESSION['user_id'],
                    'nombre' => $_SESSION['nombre'],
                    'email'  => $_SESSION['email'],
                    'foto'   => $_SESSION['foto'] ?? null
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