<?php
session_start();

header('Content-Type: application/json');

$conexion = new mysqli("localhost", "root", "", "homeaway");

if ($conexion->connect_error) {

    echo json_encode([
        "success" => false,
        "message" => "Error de conexión"
    ]);

    exit;
}

$conexion->set_charset("utf8");

// ======================================================
// GET
// ======================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // OBTENER UNA PROPIEDAD
    if (isset($_GET['id'])) {

        $id = intval($_GET['id']);

        $sql = "SELECT p.*, u.nombre AS nombre_anfitrion
                FROM propiedades p
                INNER JOIN usuarios u ON p.anfitrion_id = u.id
                WHERE p.id = $id";

        $resultado = $conexion->query($sql);

        if ($resultado && $resultado->num_rows > 0) {

            $propiedad = $resultado->fetch_assoc();

            echo json_encode([
                "success" => true,
                "propiedad" => $propiedad
            ]);

        } else {

            echo json_encode([
                "success" => false,
                "message" => "Propiedad no encontrada"
            ]);
        }

        exit;
    }

    // OBTENER TODAS LAS PROPIEDADES
    $sql = "SELECT p.*, u.nombre AS nombre_anfitrion
            FROM propiedades p
            INNER JOIN usuarios u ON p.anfitrion_id = u.id
            ORDER BY p.id DESC";

    $resultado = $conexion->query($sql);

    $propiedades = [];

    if ($resultado) {

        while ($fila = $resultado->fetch_assoc()) {

            $propiedades[] = $fila;
        }
    }

    echo json_encode([
        "success" => true,
        "propiedades" => $propiedades
    ]);

    exit;
}

// ======================================================
// POST
// ======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user_id'])) {

        echo json_encode([
            "success" => false,
            "message" => "Debes iniciar sesión"
        ]);

        exit;
    }

    $usuarioId = $_SESSION['user_id'];

    // ======================================================
    // ELIMINAR PROPIEDAD
    // ======================================================
    $input = file_get_contents("php://input");
    $data  = json_decode($input, true);

    if (isset($data['accion']) && $data['accion'] === 'eliminar') {

        $propiedadId = intval($data['id']);

        // Obtener reservaciones activas de esta propiedad
        $sqlReservas = "SELECT id, usuario_id FROM reservaciones 
                        WHERE propiedad_id = $propiedadId";
        $resReservas = $conexion->query($sqlReservas);

        if ($resReservas && $resReservas->num_rows > 0) {

            while ($reserva = $resReservas->fetch_assoc()) {

                $reservacionId = $reserva['id'];
                $huespedId     = $reserva['usuario_id'];
                $mensaje       = $conexion->real_escape_string(
                    '⚠️ El anfitrión ha eliminado esta propiedad. Tu reservación ha sido cancelada y recibirás un reembolso completo. Disculpa los inconvenientes.'
                );

                // Enviar mensaje automático al huésped
                $conexion->query("INSERT INTO mensajes 
                    (reservacion_id, emisor_id, receptor_id, mensaje)
                    VALUES ($reservacionId, $usuarioId, $huespedId, '$mensaje')");
            }
        }

        // Eliminar reservaciones de la propiedad
        $conexion->query("DELETE FROM reservaciones WHERE propiedad_id = $propiedadId");

        // Limpiar mensajes huérfanos
        $conexion->query("DELETE FROM mensajes WHERE reservacion_id NOT IN (SELECT id FROM reservaciones)");

        // Eliminar la propiedad
        $sqlEliminar = "DELETE FROM propiedades
                        WHERE id = $propiedadId
                        AND anfitrion_id = $usuarioId";

        if ($conexion->query($sqlEliminar)) {

            echo json_encode([
                "success" => true,
                "message" => "Propiedad eliminada"
            ]);

        } else {

            echo json_encode([
                "success" => false,
                "message" => "Error al eliminar"
            ]);
        }

        exit;
    }

    // ======================================================
    // CREAR PROPIEDAD
    // ======================================================

    $tipoAlojamiento = $_POST['tipoAlojamiento'] ?? '';
    $region          = $_POST['region']          ?? '';
    $direccion       = $_POST['direccion']       ?? '';
    $departamento    = $_POST['departamento']    ?? '';
    $zona            = $_POST['zona']            ?? '';
    $codigoPostal    = $_POST['codigoPostal']    ?? '';
    $ciudad          = $_POST['ciudad']          ?? '';
    $estado          = $_POST['estado']          ?? '';
    $precioNoche     = $_POST['precioNoche']     ?? 0;
    $numeroNoches    = $_POST['numeroNoches']    ?? 1;
    $descripcion     = $_POST['descripcion']     ?? '';

    $nombreImagen = "";

    if (isset($_FILES['imagenes'])) {

        $archivo = $_FILES['imagenes'];

        if ($archivo['error'][0] === 0) {

            $nombreOriginal = $archivo['name'][0];
            $extension      = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreImagen   = uniqid() . "." . $extension;
            $rutaDestino    = "../assets/img/propiedades/" . $nombreImagen;

            move_uploaded_file($archivo['tmp_name'][0], $rutaDestino);
        }
    }

    $sql = "INSERT INTO propiedades (
        anfitrion_id,
        tipo_alojamiento,
        region,
        direccion,
        departamento_habitacion,
        zona,
        codigo_postal,
        ciudad,
        estado,
        precio_noche,
        numero_noches,
        descripcion,
        imagen_url
    ) VALUES (
        '$usuarioId',
        '$tipoAlojamiento',
        '$region',
        '$direccion',
        '$departamento',
        '$zona',
        '$codigoPostal',
        '$ciudad',
        '$estado',
        '$precioNoche',
        '$numeroNoches',
        '$descripcion',
        '$nombreImagen'
    )";

    if ($conexion->query($sql)) {

        echo json_encode([
            "success" => true,
            "datos"   => [
                "tipo"      => $tipoAlojamiento,
                "direccion" => $direccion,
                "ciudad"    => $ciudad,
                "estado"    => $estado,
                "precio"    => $precioNoche,
                "noches"    => $numeroNoches,
                "imagenes"  => $nombreImagen ? 1 : 0
            ]
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" => $conexion->error
        ]);
    }

    exit;
}
?>