<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeAway - Inicio</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/styles.css?v=1">
    <link rel="stylesheet" href="../assets/styles2.css?v=1">
</head>

<body class="pagina-principal">

<header class="header">

    <a href="Index.php">
        <img src="../assets/img/Logo_azul.png"
             alt="Logo"
             class="logo">
    </a>

    <div class="menu-container">

       <div class="icono-usuario" id="iconoUsuario" style="display:none;">

    <svg 
        xmlns="http://www.w3.org/2000/svg" 
        width="22" 
        height="22" 
        fill="none" 
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="2"
    >

        <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75a17.933 17.933 0 0 1-7.5-1.632Z"
        />

    </svg>

</div>

        <!-- MENÚ -->
        <div class="menu-hamburguesa">

            <button class="boton-menu" id="botonMenu">
                <span class="linea"></span>
                <span class="linea"></span>
                <span class="linea"></span>
            </button>

            <!-- SIN SESIÓN -->
            <div class="dropdown-menu" id="menuSinSesion">

                <a href="Login.html" class="menu-item">
                    Iniciar sesión
                </a>

                <a href="Anfitrion1.html" class="menu-item">
                    Conviértete en anfitrión
                </a>

            </div>

            <!-- CON SESIÓN -->
            <div class="dropdown-menu"
                 id="menuConSesion"
                 style="display:none;">

                <div class="user-info-menu">
                    <strong id="nombreUsuarioMenu">
                        Usuario
                    </strong>

                    <small id="emailUsuarioMenu">
                        correo@correo.com
                    </small>
                </div>

                <hr>

                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == 14): ?>

                    <a href="AdminPanel.php"
                       class="menu-item">
                        Panel Admin
                    </a>

                <?php else: ?>

                    <a href="Perfil.html"
                       class="menu-item">
                        Mi perfil
                    </a>

                <?php endif; ?>

                <a href="Anfitrion1.html"
                   class="menu-item">
                    Conviértete en anfitrión
                </a>

                <a href="#"
                   class="menu-item"
                   onclick="cerrarSesion()">
                    Cerrar sesión
                </a>

            </div>

        </div>
    </div>

</header>

<!-- BUSCADOR -->
<div class="search-bar">

    <div class="search-option">

        <span class="label">
            Destinos
        </span>

        <span class="value">
            Buscar lugares
        </span>

    </div>

    <button class="search-button">

        

    </button>

</div>

<!-- PROPIEDADES -->
<div class="propiedades" id="contenedorPropiedades">

    <div style="text-align:center; padding:40px;">
        <p>Cargando propiedades...</p>
    </div>

</div>

<!-- JS -->
<script src="../assets/javascript/main.js?v=1"></script>

<script>

async function verificarSesion() {

    try {

        const response = await fetch('../app/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                accion: 'verificar_sesion'
            })
        });

        const resultado = await response.json();

        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const iconoUsuario = document.getElementById('iconoUsuario');

        if(resultado.logueado){

            menuSinSesion.style.display = 'none';
            menuConSesion.style.display = 'block';
            iconoUsuario.style.display = 'flex';

            document.getElementById('nombreUsuarioMenu').textContent =
                resultado.usuario.nombre;

            document.getElementById('emailUsuarioMenu').textContent =
                resultado.usuario.email;

        } else {

            menuSinSesion.style.display = 'block';
            menuConSesion.style.display = 'none';
            iconoUsuario.style.display = 'none';
        }

    } catch(error){

        console.error(error);
    }
}

async function cargarPropiedades() {

    try {

        const response = await fetch('../app/AnfitrionController.php', {
            method: 'GET',
            credentials: 'include'
        });

        const resultado = await response.json();

        const contenedor =
            document.getElementById('contenedorPropiedades');

        if(resultado.success &&
           resultado.propiedades.length > 0){

            contenedor.innerHTML = '';

            resultado.propiedades.forEach(propiedad => {

                const imagen =
                    propiedad.imagen_url
                    ? '../assets/img/propiedades/' + propiedad.imagen_url
                    : '../assets/img/placeholder.png';

                contenedor.innerHTML += `

                    <div class="condominio">

                        <a href="DetallePropiedad.php?id=${propiedad.id}">

                            <div class="contenedor-img">

                                <img
                                    src="${imagen}"
                                    class="img-condominio"
                                    alt="propiedad"

                                    onerror="
                                        this.src='../assets/img/placeholder.png'
                                    "
                                >

                            </div>

                        </a>

                        <div class="info-condominio">

                            <h3 class="titulo-condominio">

                                ${propiedad.tipo_alojamiento}
                                en
                                ${propiedad.ciudad}

                            </h3>

                            <p class="precio">

                                $${parseFloat(propiedad.precio_noche)
                                    .toLocaleString('es-MX')} MXN

                            </p>

                            <div class="rating">
                                ★ 5.0
                            </div>

                        </div>

                    </div>

                `;
            });

        } else {

            contenedor.innerHTML = `

                <div style="padding:40px; text-align:center;">

                    No hay propiedades disponibles

                </div>

            `;
        }

    } catch(error){

        console.error(error);

        document.getElementById('contenedorPropiedades').innerHTML = `

            <div style="padding:40px; text-align:center;">

                Error al cargar propiedades

            </div>

        `;
    }
}

/* ========= CERRAR SESION CORREGIDO ========= */

async function cerrarSesion() {

    try {

        const response = await fetch('../app/AuthController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                accion: 'logout'
            })
        });

        const resultado = await response.json();

        if(resultado.success){

            alert('Sesión cerrada correctamente');

            location.reload();

        } else {

            alert('Error al cerrar sesión');
        }

    } catch(error){

        console.error(error);
        alert('Error de conexión');
    }
}

document.addEventListener('DOMContentLoaded', async () => {

    await verificarSesion();

    await cargarPropiedades();

});

</script>

</body>
</html>