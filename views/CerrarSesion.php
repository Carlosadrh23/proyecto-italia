<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar sesión</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>

    <h2>¿Desea cerrar la sesión?</h2>

    <button type="button" onclick="cerrarSesion()">
        Cerrar sesión
    </button>

    <button type="button" onclick="window.location.href='Index.php'">
        Cancelar
    </button>

    <script>
    async function cerrarSesion() {

        console.log("Botón presionado");

        try {

            const response = await fetch('../app/AuthController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    accion: 'logout'
                })
            });

            const text = await response.text();

            console.log("Respuesta:", text);

            const resultado = JSON.parse(text);

            if (resultado.success) {

                alert("Sesión cerrada");
                window.location.href = 'Index.php';

            } else {

                alert("Error: " + resultado.message);
            }

        } catch (error) {

            console.error(error);
            alert("Error en fetch o ruta");
        }
    }
    </script>

</body>
</html>