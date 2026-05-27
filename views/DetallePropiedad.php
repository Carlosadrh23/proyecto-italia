<?php
session_start();

$propiedadId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($propiedadId <= 0) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeAway - Detalle de Propiedad</title>

    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/styles2.css">
</head>

<body class="pagina-principal">

<header class="header">

    <a href="index.php">
        <img src="../assets/img/Logo_azul.png"
             alt="Logo"
             class="logo">
    </a>

    <div class="menu-container">

        <div class="icono-usuario"
             id="iconoUsuario"
             style="display:none;">

            <svg width="32"
                 height="32"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2">

                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>

            </svg>

        </div>

        <div class="menu-hamburguesa">

            <button class="boton-menu">
                <span class="linea"></span>
                <span class="linea"></span>
                <span class="linea"></span>
            </button>

            <!-- SIN SESIÓN -->
            <div class="dropdown-menu"
                 id="menuSinSesion">

                <a href="Login.html"
                   class="menu-item">
                    Iniciar sesión / Registrarse
                </a>

                <a href="Anfitrion1.html"
                   class="menu-item">
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

                <hr style="
                    margin:8px 0;
                    border:none;
                    border-top:1px solid #EBEBEB;
                ">

                <a href="Perfil.html"
                   class="menu-item">
                    Mi perfil
                </a>

                <a href="Anfitrion1.html"
                   class="menu-item">
                    Conviértete en anfitrión
                </a>

                <a href="CerrarSesion.php"
                   class="menu-item">
                    Cerrar sesión
                </a>

            </div>

        </div>

    </div>

</header>

<!-- CONTENIDO -->
<div class="contenedor-detalle"
     id="contenedorDetalle">

    <div class="loading">
        Cargando...
    </div>

</div>

<!-- BOTÓN RESERVAR -->
<button
    class="boton-reservar-fijo"
    id="btnReservar"
    onclick="abrirModalReserva()"
    style="display:none;">

    Reservar

</button>

<!-- MODAL RESERVA -->
<div class="modal-reserva"
     id="modalReserva"
     onclick="cerrarModalSiFueraClick(event)">

    <div class="modal-contenido">

        <button class="boton-cerrar"
                onclick="cerrarModalReserva()">
            ×
        </button>

        <div class="modal-header">

            <div class="precio-modal"
                 id="precioModal">
                $0 MXN
            </div>

            <div class="noches-modal"
                 id="nochesModal">
                por noche
            </div>

        </div>

        <div id="infoReserva"
             style="
                display:none;
                padding:12px;
                background:#f7f7f7;
                border-radius:8px;
                margin-bottom:16px;
             ">

            <div style="
                display:flex;
                justify-content:space-between;
                margin-bottom:8px;
            ">
                <span>Noches:</span>
                <strong id="nochesSeleccionadas">0</strong>
            </div>

            <div style="
                display:flex;
                justify-content:space-between;
                margin-bottom:8px;
            ">
                <span>Precio noche:</span>
                <strong id="precioPorNoche">$0</strong>
            </div>

            <hr style="
                margin:8px 0;
                border:none;
                border-top:1px solid #ddd;
            ">

            <div style="
                display:flex;
                justify-content:space-between;
            ">
                <strong>Total:</strong>
                <strong id="precioTotal"
                        style="color:#FF385C;">
                    $0
                </strong>
            </div>

        </div>

        <div class="fila-fechas">

            <div class="campo-fecha">

                <label class="label-fecha">
                    Llegada
                </label>

                <input type="date"
                       class="input-fecha"
                       id="fechaLlegada">

            </div>

            <div class="campo-fecha">

                <label class="label-fecha">
                    Salida
                </label>

                <input type="date"
                       class="input-fecha"
                       id="fechaSalida">

            </div>

        </div>

        <div class="campo-huespedes">

            <label class="label-huespedes">
                Huéspedes
            </label>

            <div class="contador-huespedes">

                <button
                    class="boton-contador"
                    onclick="cambiarHuespedes(-1)"
                    id="btnMenos"
                    disabled>
                    -
                </button>

                <span class="numero-huespedes"
                      id="numHuespedes">
                    0
                </span>

                <button
                    class="boton-contador"
                    onclick="cambiarHuespedes(1)">
                    +
                </button>

            </div>

        </div>

        <button class="boton-verificar"
                onclick="confirmarReservacion()"
                id="btnConfirmar">
            Continuar
        </button>

    </div>

</div>

<!-- MODAL AMENIDADES -->
<div class="modal-overlay"
     id="modalAmenidades"
     onclick="cerrarModalSiClickFuera(event)">

    <div class="modal-container">

        <div class="modal-header">

            <h2 class="modal-title">
                Lo que ofrece este lugar
            </h2>

            <button class="btn-cerrar"
                    onclick="cerrarModalAmenidades()">
                ×
            </button>

        </div>

        <div class="modal-body">

            <div class="amenidades-grid">
                                <!-- Baño -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">🚿 Baño</span>
                </div>

                <!-- Wifi -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">📶 Wifi</span>
                </div>

                <!-- TV -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">📺 TV</span>
                </div>

                <!-- Cocina -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">🍳 Cocina</span>
                </div>

                <!-- Lavadora -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">🧺 Lavadora</span>
                </div>

                <!-- Agua caliente -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">🔥 Agua caliente</span>
                </div>

                <!-- Refrigerador -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">🧊 Refrigerador</span>
                </div>

                <!-- Closet -->
                <div class="amenidad-item">
                    <span class="amenidad-texto">🧥 Closet</span>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="../assets/javascript/main.js"></script>

<script>

const propiedadId = <?php echo $propiedadId; ?>;
let propiedadActual = null;
let numHuespedes = 0;
let usuarioLogueado = false;

// ==========================
// VERIFICAR SESIÓN
// ==========================
async function verificarSesionYActualizarMenu(){

    try{

        const response = await fetch(
            '../app/AuthController.php',
            {
                method:'POST',
                headers:{
                    'Content-Type':'application/json'
                },
                credentials:'include',
                body:JSON.stringify({
                    accion:'verificar_sesion'
                })
            }
        );

        const resultado = await response.json();

        usuarioLogueado =
            resultado.logueado || false;

        const iconoUsuario =
            document.getElementById(
                'iconoUsuario'
            );

        const menuSinSesion =
            document.getElementById(
                'menuSinSesion'
            );

        const menuConSesion =
            document.getElementById(
                'menuConSesion'
            );

        if(resultado.logueado){

            iconoUsuario.style.display='flex';
            menuSinSesion.style.display='none';
            menuConSesion.style.display='block';

            document.getElementById(
                'nombreUsuarioMenu'
            ).textContent =
                resultado.usuario.nombre;

            document.getElementById(
                'emailUsuarioMenu'
            ).textContent =
                resultado.usuario.email;

        }else{

            iconoUsuario.style.display='none';
            menuSinSesion.style.display='block';
            menuConSesion.style.display='none';
        }

        return usuarioLogueado;

    }catch(error){

        console.error(error);
        return false;
    }
}
    
// ==========================
// CARGAR DETALLE
// ==========================
async function cargarDetallePropiedad(){

    try{

        const response = await fetch(
            '../app/AnfitrionController.php?id='
            + propiedadId,
            {
                method:'GET',
                credentials:'include'
            }
        );

        const resultado =
            await response.json();

        console.log(resultado);

        if(
            resultado.success &&
            resultado.propiedad
        ){

            propiedadActual =
                resultado.propiedad;

            mostrarDetalle(
                resultado.propiedad
            );

            document
                .getElementById(
                    'btnReservar'
                )
                .style.display =
                'block';

            cargarValoraciones();

        }else{

            document
                .getElementById(
                    'contenedorDetalle'
                )
                .innerHTML =
                `
                <div class="error">
                    Propiedad no encontrada
                </div>
                `;
        }

    }catch(error){

        console.error(error);

        document
            .getElementById(
                'contenedorDetalle'
            )
            .innerHTML =
            `
            <div class="error">
                Error al cargar
            </div>
            `;
    }
}

// ==========================
// MOSTRAR DETALLE
// ==========================
function mostrarDetalle(propiedad){

    const imagenUrl =
        propiedad.imagen_url
        ? '../assets/img/propiedades/'
            + propiedad.imagen_url
        : '../assets/img/placeholder.png';

    const precioFormateado =
        parseFloat(
            propiedad.precio_noche
        ).toLocaleString(
            'es-MX'
        );

    const html = `

        <h1>
            ${propiedad.tipo_alojamiento}
            en
            ${propiedad.ciudad}
        </h1>

        <div class="propiedades">

            <div class="condominio">

                <div class="contenedor-img">

                    <img
                        src="${imagenUrl}"
                        class="img-condominio"
                        onerror="
                        this.src='../assets/img/placeholder.png'
                        "
                    >

                </div>

                <div class="info-condominio">

                    <h3>
                        ${propiedad.tipo_alojamiento}
                    </h3>

                    <p>
                        ${propiedad.ciudad},
                        ${propiedad.estado}
                    </p>

                    <p>
                        ${propiedad.descripcion}
                    </p>

                    <p class="precio">
                        $${precioFormateado}
                        MXN
                    </p>

                    <div class="rating">
                        ★ 5.0
                    </div>

                    <hr style="margin:20px 0;">

                    <!-- VALORACIONES -->
                    <div
                        id="valoracionesContainer"
                    >

                        <h3>
                            Valoraciones
                        </h3>

                        <div
                            id="listaValoraciones"
                        >
                            Cargando...
                        </div>

                        <div
                            id="formValoracion"
                            style="
                                margin-top:20px;
                            "
                        >

                            <textarea
                                id="comentarioValoracion"
                                placeholder="Escribe tu opinión..."
                                style="
                                    width:100%;
                                    min-height:80px;
                                    padding:10px;
                                    border:1px solid #ddd;
                                    border-radius:8px;
                                "
                            ></textarea>

                            <div
                                style="
                                    margin-top:10px;
                                "
                            >

                                <select
                                    id="ratingValoracion"
                                >

                                    <option value="5">
                                        ⭐⭐⭐⭐⭐
                                    </option>

                                    <option value="4">
                                        ⭐⭐⭐⭐
                                    </option>

                                    <option value="3">
                                        ⭐⭐⭐
                                    </option>

                                    <option value="2">
                                        ⭐⭐
                                    </option>

                                    <option value="1">
                                        ⭐
                                    </option>

                                </select>

                                <button
                                    onclick="enviarValoracion()"
                                    style="
                                        padding:10px 20px;
                                        background:#5B8A8F;
                                        color:white;
                                        border:none;
                                        border-radius:8px;
                                        margin-left:10px;
                                        cursor:pointer;
                                    "
                                >
                                    Enviar
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    `;

    document
        .getElementById(
            'contenedorDetalle'
        )
        .innerHTML = html;
}

// ==========================
// CARGAR VALORACIONES
// ==========================
async function cargarValoraciones(){

    try{

        const response =
            await fetch(
                '../app/ValoracionController.php?propiedad_id='
                + propiedadId
            );

        const resultado =
            await response.json();

        const lista =
            document.getElementById(
                'listaValoraciones'
            );

        if(!lista) return;

        if(
            resultado.success &&
            resultado.valoraciones.length > 0
        ){

            lista.innerHTML='';

            resultado.valoraciones.forEach(v=>{

                lista.innerHTML += `
                    <div style="
                        border-bottom:1px solid #eee;
                        padding:12px 0;
                    ">

                        <strong>
                            ${v.usuario}
                        </strong>

                        <div>
                            ${'⭐'.repeat(v.rating)}
                        </div>

                        <p style="
                            margin-top:5px;
                        ">
                            ${v.comentario}
                        </p>

                    </div>
                `;
            });

        }else{

            lista.innerHTML=
                '<p>No hay valoraciones aún.</p>';
        }

    }catch(error){

        console.error(error);
    }
}

// ==========================
// ENVIAR VALORACIÓN
// ==========================
async function enviarValoracion(){

    const comentario =
        document.getElementById(
            'comentarioValoracion'
        ).value;

    const rating =
        document.getElementById(
            'ratingValoracion'
        ).value;

    if(comentario.trim()===''){

        alert(
            'Escribe un comentario'
        );

        return;
    }

    try{

        const response =
            await fetch(
                '../app/ValoracionController.php',
                {
                    method:'POST',
                    headers:{
                        'Content-Type':
                        'application/json'
                    },
                    credentials:'include',
                    body:JSON.stringify({
                        propiedad_id:propiedadId,
                        comentario,
                        rating
                    })
                }
            );

        const resultado =
            await response.json();

        if(resultado.success){

            alert(
                'Valoración enviada'
            );

            document.getElementById(
                'comentarioValoracion'
            ).value='';

            cargarValoraciones();

        }else{

            alert(
                resultado.message
            );
        }

    }catch(error){

        console.error(error);
    }
}

// ==========================
// MODAL RESERVA
// ==========================
function abrirModalReserva(){

    document
        .getElementById(
            'modalReserva'
        )
        .classList.add(
            'active'
        );

    document.body.style.overflow=
        'hidden';
}

function cerrarModalReserva(){

    document
        .getElementById(
            'modalReserva'
        )
        .classList.remove(
            'active'
        );

    document.body.style.overflow=
        'auto';
}

function cerrarModalSiFueraClick(
    event
){

    if(
        event.target.id
        ===
        'modalReserva'
    ){

        cerrarModalReserva();
    }
}

// ==========================
// AMENIDADES
// ==========================
function abrirModalAmenidades(){

    document
        .getElementById(
            'modalAmenidades'
        )
        .classList.add(
            'active'
        );

    document.body.style.overflow=
        'hidden';
}

function cerrarModalAmenidades(){

    document
        .getElementById(
            'modalAmenidades'
        )
        .classList.remove(
            'active'
        );

    document.body.style.overflow=
        'auto';
}

function cerrarModalSiClickFuera(
    event
){

    if(
        event.target.id
        ===
        'modalAmenidades'
    ){

        cerrarModalAmenidades();
    }
}

// ==========================
// HUÉSPEDES
// ==========================
function cambiarHuespedes(
    cambio
){

    numHuespedes += cambio;

    if(
        numHuespedes < 0
    ){
        numHuespedes = 0;
    }

    document
        .getElementById(
            'numHuespedes'
        )
        .textContent =
        numHuespedes;

    document
        .getElementById(
            'btnMenos'
        )
        .disabled =
        numHuespedes === 0;
}

// ==========================
// CONFIRMAR
// ==========================
function confirmarReservacion(){

    alert(
        'Continuar con reservación'
    );
}

// ==========================
// INIT
// ==========================
document.addEventListener(
    'DOMContentLoaded',
    async function(){

        await verificarSesionYActualizarMenu();

        await cargarDetallePropiedad();
    }
);

</script>

</body>
</html>