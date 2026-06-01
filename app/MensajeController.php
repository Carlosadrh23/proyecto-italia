<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

$conexion = new mysqli("localhost", "root", "", "homeaway");

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

$conexion->set_charset("utf8");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

$usuarioId = intval($_SESSION['user_id']);

// ============================================
// POST — Enviar mensaje
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input      = file_get_contents('php://input');
    $data       = json_decode($input, true);
    $receptorId = intval($data['receptor_id']);
    $mensaje    = $conexion->real_escape_string(trim($data['mensaje']));

    if ($receptorId <= 0 || $mensaje === '') {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }

    if ($receptorId === $usuarioId) {
        echo json_encode(['success' => false, 'message' => 'No puedes enviarte mensajes a ti mismo']);
        exit;
    }

    $sql = "INSERT INTO mensajes (emisor_id, receptor_id, mensaje, fecha, leido)
            VALUES ($usuarioId, $receptorId, '$mensaje', NOW(), 0)";

    if ($conexion->query($sql)) {
        echo json_encode([
            'success' => true,
            'mensaje' => [
                'id'          => $conexion->insert_id,
                'emisor_id'   => $usuarioId,
                'receptor_id' => $receptorId,
                'mensaje'     => $data['mensaje'],
                'fecha'       => date('Y-m-d H:i:s'),
                'leido'       => 0
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $conexion->error]);
    }

    exit;
}

// ============================================
// GET
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $accion = $_GET['accion'] ?? '';

    // CONVERSACIÓN CON OTRO USUARIO
    if ($accion === 'conversacion') {

        $conId = intval($_GET['con']);

        if ($conId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Usuario inválido']);
            exit;
        }

        // Marcar como leídos
        $conexion->query(
            "UPDATE mensajes SET leido = 1
             WHERE receptor_id = $usuarioId AND emisor_id = $conId AND leido = 0"
        );

        // Obtener mensajes
        $sql = "SELECT m.*, 
                       u.nombre AS emisor_nombre,
                       u.foto   AS emisor_foto
                FROM mensajes m
                INNER JOIN usuarios u ON m.emisor_id = u.id
                WHERE (m.emisor_id = $usuarioId AND m.receptor_id = $conId)
                   OR (m.emisor_id = $conId    AND m.receptor_id = $usuarioId)
                ORDER BY m.fecha ASC";

        $resultado = $conexion->query($sql);
        $mensajes  = [];

        while ($fila = $resultado->fetch_assoc()) {
            $mensajes[] = [
                'id'            => $fila['id'],
                'emisor_id'     => $fila['emisor_id'],
                'receptor_id'   => $fila['receptor_id'],
                'mensaje'       => $fila['mensaje'],
                'fecha'         => $fila['fecha'],
                'leido'         => $fila['leido'],
                'emisor_nombre' => $fila['emisor_nombre'],
                'emisor_foto'   => $fila['emisor_foto']
            ];
        }

        // Datos del otro usuario
        $resOtro     = $conexion->query("SELECT id, nombre, foto FROM usuarios WHERE id = $conId");
        $otroUsuario = $resOtro ? $resOtro->fetch_assoc() : null;

        // Verificar rol basado en reservaciones entre los dos
            $sqlRol = "SELECT COUNT(*) as total 
                    FROM reservaciones r
                    INNER JOIN propiedades p ON r.propiedad_id = p.id
                    WHERE r.usuario_id = $usuarioId 
                    AND p.anfitrion_id = $conId";

            $resRol            = $conexion->query($sqlRol);
            $filaRol           = $resRol->fetch_assoc();
            $esAnfitrionElOtro = $filaRol['total'] > 0;

        echo json_encode([
            'success'              => true,
            'mensajes'             => $mensajes,
            'otro_usuario'         => $otroUsuario,
            'usuario_id'           => $usuarioId,
            'es_anfitrion_el_otro' => $esAnfitrionElOtro
        ]);

        exit;
    }

    // LISTA DE CONVERSACIONES
    if ($accion === 'lista') {

        $sql = "SELECT 
                    CASE 
                        WHEN m.emisor_id = $usuarioId THEN m.receptor_id
                        ELSE m.emisor_id
                    END as otro_id,
                    u.nombre as otro_nombre,
                    u.foto   as otro_foto,
                    MAX(m.fecha) as ultima_fecha,
                    (SELECT m2.mensaje FROM mensajes m2
                     WHERE ((m2.emisor_id = $usuarioId AND m2.receptor_id = otro_id)
                         OR (m2.emisor_id = otro_id    AND m2.receptor_id = $usuarioId))
                     ORDER BY m2.fecha DESC LIMIT 1) as ultimo_mensaje,
                    SUM(CASE WHEN m.receptor_id = $usuarioId AND m.leido = 0 THEN 1 ELSE 0 END) as no_leidos
                FROM mensajes m
                INNER JOIN usuarios u ON u.id = CASE 
                    WHEN m.emisor_id = $usuarioId THEN m.receptor_id
                    ELSE m.emisor_id
                END
                WHERE m.emisor_id = $usuarioId OR m.receptor_id = $usuarioId
                GROUP BY otro_id, u.nombre, u.foto
                ORDER BY ultima_fecha DESC";

        $resultado      = $conexion->query($sql);
        $conversaciones = [];

        while ($fila = $resultado->fetch_assoc()) {
            $conversaciones[] = $fila;
        }

        echo json_encode(['success' => true, 'conversaciones' => $conversaciones]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Método no permitido']);
?>