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
                <a href="Login.html" class="menu-item">Iniciar sesión</a>
                <a href="Anfitrion1.html" class="menu-item">Conviértete en anfitrión</a>
            </div>

            <!-- CON SESIÓN -->
            <div class="dropdown-menu" id="menuConSesion" style="display:none;">

                <div class="user-info-menu">
                    <strong id="nombreUsuarioMenu">Usuario</strong>
                    <small id="emailUsuarioMenu">correo@correo.com</small>
                </div>

                <hr>

                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == 14): ?>
                    <a href="AdminPanel.php" class="menu-item">Panel Admin</a>
                <?php else: ?>
                    <a href="Perfil.html" class="menu-item">Mi perfil</a>
                <?php endif; ?>

                <a href="Anfitrion1.html" class="menu-item">Conviértete en anfitrión</a>
                <a href="#" class="menu-item" onclick="cerrarSesion()">Cerrar sesión</a>

            </div>

        </div>
    </div>

</header>

<!-- BUSCADOR -->
<div class="search-bar">

    <div class="search-option">
        <span class="label">Destinos</span>
        <input
            type="text"
            id="inputBusqueda"
            placeholder="Buscar lugares..."
            style="
                border: none;
                outline: none;
                font-size: 14px;
                color: #222;
                background: transparent;
                width: 100%;
                cursor: text;
            "
        >
    </div>

    <button class="search-button" onclick="buscarPropiedades()">
        
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

let todasLasPropiedades = [];

async function verificarSesion() {

    try {

        const response = await fetch('../app/AuthController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ accion: 'verificar_sesion' })
        });

        const resultado = await response.json();

        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const iconoUsuario  = document.getElementById('iconoUsuario');

        if(resultado.logueado){

            menuSinSesion.style.display = 'none';
            menuConSesion.style.display = 'block';
            iconoUsuario.style.display  = 'flex';

            if (resultado.usuario.foto) {
                iconoUsuario.innerHTML = `
                    <img
                        src="/homeaway/Airbnb/assets/img/usuarios/${resultado.usuario.foto}"
                        style="
                            width:32px;
                            height:32px;
                            border-radius:50%;
                            object-fit:cover;
                            border:2px solid #5B8A8F;
                        "
                        onerror="this.style.display='none'"
                    >
                `;
            }

            document.getElementById('nombreUsuarioMenu').textContent = resultado.usuario.nombre;
            document.getElementById('emailUsuarioMenu').textContent  = resultado.usuario.email;

        } else {

            menuSinSesion.style.display = 'block';
            menuConSesion.style.display = 'none';
            iconoUsuario.style.display  = 'none';
        }

    } catch(error){
        console.error(error);
    }
}

// ==========================
// RENDERIZAR PROPIEDADES
// ==========================
function renderizarPropiedades(propiedades) {

    const contenedor = document.getElementById('contenedorPropiedades');

    if (propiedades.length === 0) {

        // Quitar el grid para centrar el mensaje
        contenedor.style.display = 'flex';
        contenedor.style.flexDirection = 'column';
        contenedor.style.alignItems = 'center';
        contenedor.style.justifyContent = 'center';
        contenedor.style.minHeight = '60vh';

        contenedor.innerHTML = `
            <div style="text-align:center; color:#717171;">
                <div style="font-size:48px; margin-bottom:16px;">🔍</div>
                <h3 style="margin-bottom:8px; color:#222;">
                    No encontramos propiedades
                </h3>
                <p>
                    No hay alojamientos disponibles en ese lugar.<br>
                    Intenta con otra ciudad o destino.
                </p>
                <button
                    onclick="mostrarTodas()"
                    style="
                        margin-top:16px;
                        padding:10px 20px;
                        background:#5B8A8F;
                        color:white;
                        border:none;
                        border-radius:8px;
                        cursor:pointer;
                        font-size:14px;
                    "
                >
                    Ver todas las propiedades
                </button>
            </div>
        `;
        return;
    }

    // Restaurar grid cuando hay propiedades
    contenedor.style.display = 'grid';
    contenedor.style.flexDirection = '';
    contenedor.style.alignItems = '';
    contenedor.style.justifyContent = '';
    contenedor.style.minHeight = '';

    contenedor.innerHTML = '';

    propiedades.forEach(propiedad => {

        const imagen = propiedad.imagen_url
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
                            onerror="this.src='../assets/img/placeholder.png'"
                        >
                    </div>
                </a>
                <div class="info-condominio">
                    <h3 class="titulo-condominio">
                        ${propiedad.tipo_alojamiento} en ${propiedad.ciudad}
                    </h3>
                    <p class="precio">
                        $${parseFloat(propiedad.precio_noche).toLocaleString('es-MX')} MXN
                    </p>
                    <div class="rating">★ 5.0</div>
                </div>
            </div>
        `;
    });
}

// ==========================
// CARGAR PROPIEDADES
// ==========================
async function cargarPropiedades() {

    try {

        const response = await fetch('../app/AnfitrionController.php', {
            method: 'GET',
            credentials: 'include'
        });

        const resultado = await response.json();

        if(resultado.success && resultado.propiedades.length > 0){

            todasLasPropiedades = resultado.propiedades;
            renderizarPropiedades(todasLasPropiedades);

        } else {

            renderizarPropiedades([]);
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

// ==========================
// BUSCAR
// ==========================
function buscarPropiedades() {

    const termino = document.getElementById('inputBusqueda').value.trim().toLowerCase();

    if (!termino) {
        renderizarPropiedades(todasLasPropiedades);
        return;
    }

    const filtradas = todasLasPropiedades.filter(p =>
        p.ciudad.toLowerCase().includes(termino)          ||
        p.estado.toLowerCase().includes(termino)          ||
        p.region.toLowerCase().includes(termino)          ||
        p.tipo_alojamiento.toLowerCase().includes(termino)
    );

    renderizarPropiedades(filtradas);
}

// Buscar también al presionar Enter
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('inputBusqueda').addEventListener('keydown', function(e){
        if (e.key === 'Enter') buscarPropiedades();
    });
});

// ==========================
// MOSTRAR TODAS
// ==========================
function mostrarTodas() {
    document.getElementById('inputBusqueda').value = '';
    renderizarPropiedades(todasLasPropiedades);
}

// ==========================
// CERRAR SESIÓN
// ==========================
async function cerrarSesion() {

    try {

        const response = await fetch('../app/AuthController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ accion: 'logout' })
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