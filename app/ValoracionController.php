<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

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

// ==========================================
// POST -> CREAR VALORACIÓN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if(!isset($_SESSION['user_id'])) {

        echo json_encode([
            'success' => false,
            'message' => 'Debes iniciar sesión'
        ]);

        exit;
    }

    $usuarioId = $_SESSION['user_id'];

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    $propiedadId = intval($data['propiedad_id']);
    $puntuacion = intval($data['puntuacion']);
    $comentario = trim($data['comentario']);

    if($puntuacion < 1 || $puntuacion > 5){

        echo json_encode([
            'success' => false,
            'message' => 'Puntuación inválida'
        ]);

        exit;
    }

    $sql = "INSERT INTO valoraciones(
                usuario_id,
                propiedad_id,
                puntuacion,
                comentario
            )
            VALUES(
                '$usuarioId',
                '$propiedadId',
                '$puntuacion',
                '$comentario'
            )";

    if($conexion->query($sql)){

        echo json_encode([
            'success' => true,
            'message' => 'Valoración enviada'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => $conexion->error
        ]);
    }

    exit;
}

// ==========================================
// GET -> OBTENER VALORACIONES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $propiedadId = intval($_GET['propiedad_id']);

    $sql = "SELECT
                v.*,
                u.nombre
            FROM valoraciones v
            INNER JOIN usuarios u
            ON v.usuario_id = u.id
            WHERE propiedad_id = $propiedadId
            ORDER BY v.id DESC";

    $resultado = $conexion->query($sql);

    $valoraciones = [];

    $promedio = 0;
    $total = 0;
    $suma = 0;

    while($fila = $resultado->fetch_assoc()) {

        $valoraciones[] = [
            'usuario' => $fila['nombre'],
            'puntuacion' => $fila['puntuacion'],
            'comentario' => $fila['comentario'],
            'fecha' => $fila['fecha']
        ];

        $suma += $fila['puntuacion'];
        $total++;
    }

    if($total > 0){
        $promedio = round($suma / $total,1);
    }

    echo json_encode([
        'success' => true,
        'promedio' => $promedio,
        'total' => $total,
        'valoraciones' => $valoraciones
    ]);

    exit;
}
?>