<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// CONEXIÓN LOCAL XAMPP
$conexion = new mysqli("localhost", "root", "", "homeaway");

if ($conexion->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

$conexion->set_charset("utf8");

// ============================================
// POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Debes iniciar sesión'
        ]);
        exit;
    }

    $usuarioId = $_SESSION['user_id'];

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // CANCELAR
    if(isset($data['accion']) && $data['accion'] === 'cancelar') {

        $reservacionId = intval($data['id']);

        $sql = "DELETE FROM reservaciones 
                WHERE id = $reservacionId 
                AND usuario_id = $usuarioId";

        if($conexion->query($sql)) {

            echo json_encode([
                'success' => true,
                'message' => 'Reservación cancelada'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Error al cancelar'
            ]);
        }

        exit;
    }

    // CREAR RESERVACIÓN
    $propiedadId = intval($data['propiedad_id']);
    $fechaInicio = $data['fecha_inicio'];
    $fechaFin = $data['fecha_fin'];
    $numHuespedes = intval($data['num_huespedes']);

    // VALIDACIONES
    if($propiedadId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Propiedad inválida'
        ]);
        exit;
    }

    // OBTENER PROPIEDAD
    $sqlPropiedad = "SELECT * FROM propiedades WHERE id = $propiedadId";

    $resultado = $conexion->query($sqlPropiedad);

    if(!$resultado || $resultado->num_rows === 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Propiedad no encontrada'
        ]);

        exit;
    }

    $propiedad = $resultado->fetch_assoc();

    // NO RESERVAR TU PROPIA CASA
    if($propiedad['anfitrion_id'] == $usuarioId) {

        echo json_encode([
            'success' => false,
            'message' => 'No puedes reservar tu propia propiedad'
        ]);

        exit;
    }

    // CALCULAR NOCHES
    $fecha1 = new DateTime($fechaInicio);
    $fecha2 = new DateTime($fechaFin);

    $numeroNoches = $fecha1->diff($fecha2)->days;

    // PRECIO TOTAL
    $precioTotal = $numeroNoches * $propiedad['precio_noche'];

    // INSERTAR RESERVACIÓN
    $sql = "INSERT INTO reservaciones (
        usuario_id,
        propiedad_id,
        fecha_inicio,
        fecha_fin,
        numero_huespedes,
        precio_total,
        estado_reservacion
    ) VALUES (
        '$usuarioId',
        '$propiedadId',
        '$fechaInicio',
        '$fechaFin',
        '$numHuespedes',
        '$precioTotal',
        'confirmada'
    )";

    if($conexion->query($sql)) {

        echo json_encode([
            'success' => true,
            'message' => 'Reservación realizada',
            'reservacion' => [
                'id' => $conexion->insert_id
            ]
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => $conexion->error
        ]);
    }

    exit;
}

// ============================================
// GET
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if(!isset($_SESSION['user_id'])) {

        echo json_encode([
            'success' => false,
            'message' => 'Debes iniciar sesión'
        ]);

        exit;
    }

    $usuarioId = $_SESSION['user_id'];

    // ← CAMBIO 1: se agrega p.anfitrion_id al SELECT
    $sql = "SELECT 
                r.*,
                p.tipo_alojamiento,
                p.ciudad,
                p.estado,
                p.imagen_url,
                p.anfitrion_id
            FROM reservaciones r
            INNER JOIN propiedades p 
            ON r.propiedad_id = p.id
            WHERE r.usuario_id = $usuarioId
            ORDER BY r.id DESC";

    $resultado = $conexion->query($sql);

    $reservaciones = [];

    while($fila = $resultado->fetch_assoc()) {

        $reservaciones[] = [
            'id'               => $fila['id'],
            'fecha_inicio'     => $fila['fecha_inicio'],
            'fecha_fin'        => $fila['fecha_fin'],
            'numero_huespedes' => $fila['numero_huespedes'],
            'precio_total'     => $fila['precio_total'],
            'estado'           => $fila['estado_reservacion'],
            'propiedad' => [
                'tipo'         => $fila['tipo_alojamiento'],
                'ciudad'       => $fila['ciudad'],
                'estado'       => $fila['estado'],
                'imagen'       => $fila['imagen_url'],
                'anfitrion_id' => $fila['anfitrion_id']  // ← CAMBIO 2
            ]
        ];
    }

    echo json_encode([
        'success'       => true,
        'reservaciones' => $reservaciones
    ]);

    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Método no permitido'
]);
?>