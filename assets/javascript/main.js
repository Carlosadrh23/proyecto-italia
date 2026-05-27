const API_URL = '/homeaway/Airbnb/app/AuthController.php';

// =======================================================================
//                           REGISTRO
// =======================================================================
function inicializarRegistro() {

    const formRegistro = document.getElementById('formRegistro');

    if (!formRegistro) return;

    formRegistro.addEventListener('submit', async function(e) {

        e.preventDefault();

        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!nombre || !email || !password) {
            alert('Completa todos los campos');
            return;
        }

        try {

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    accion: 'registro',
                    nombre,
                    email,
                    password
                })
            });

            const resultado = await response.json();

            console.log(resultado);

            if (resultado.success) {

                alert('Registro exitoso');

                window.location.href = '/homeaway/Airbnb/views/index.php';

            } else {

                alert(resultado.message);
            }

        } catch (error) {

            console.error(error);
            alert('Error de conexión');
        }

    });

}

// =======================================================================
//                               LOGIN
// =======================================================================
function inicializarLogin() {

    const formLogin = document.getElementById('formLogin');

    if (!formLogin) return;

    formLogin.addEventListener('submit', async function(e) {

        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();

        if (!email || !password) {
            alert('Completa todos los campos');
            return;
        }

        try {

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include',
                body: JSON.stringify({
                    accion: 'login',
                    email,
                    password
                })
            });

            const resultado = await response.json();

            console.log(resultado);

            if (resultado.success) {

                alert('Bienvenido ' + resultado.usuario.nombre);

                window.location.href = '/homeaway/Airbnb/views/index.php';

            } else {

                alert(resultado.message);
            }

        } catch (error) {

            console.error(error);
            alert('Error al iniciar sesión');
        }

    });

}

// =======================================================================
//                           VERIFICAR SESIÓN
// =======================================================================
async function verificarSesion() {

    try {

        const response = await fetch(API_URL, {
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

        console.log(resultado);

        const menuSinSesion = document.getElementById('menuSinSesion');
        const menuConSesion = document.getElementById('menuConSesion');
        const iconoUsuario = document.querySelector('.icono-usuario');

        if (resultado.logueado) {

            if (menuSinSesion) {
                menuSinSesion.style.display = 'none';
            }

            if (menuConSesion) {
                menuConSesion.style.display = 'block';
            }

            if (iconoUsuario) {
                iconoUsuario.style.display = 'flex';
            }

        } else {

            if (menuSinSesion) {
                menuSinSesion.style.display = 'block';
            }

            if (menuConSesion) {
                menuConSesion.style.display = 'none';
            }

            if (iconoUsuario) {
                iconoUsuario.style.display = 'none';
            }

        }

    } catch (error) {

        console.error(error);

    }

}

// =======================================================================
//                           MENÚ HAMBURGUESA
// =======================================================================
function inicializarMenu() {

    const botonMenu = document.querySelector('.boton-menu');

    const menuSinSesion = document.getElementById('menuSinSesion');
    const menuConSesion = document.getElementById('menuConSesion');

    if (!botonMenu) return;

    botonMenu.addEventListener('click', function(e) {

        e.stopPropagation();

        if (menuSinSesion && menuSinSesion.style.display !== 'none') {
            menuSinSesion.classList.toggle('active');
        }

        if (menuConSesion && menuConSesion.style.display !== 'none') {
            menuConSesion.classList.toggle('active');
        }

    });

    document.addEventListener('click', function(e) {

        if (!e.target.closest('.menu-hamburguesa')) {

            if (menuSinSesion) {
                menuSinSesion.classList.remove('active');
            }

            if (menuConSesion) {
                menuConSesion.classList.remove('active');
            }

        }

    });

}

// =======================================================================
//                           CERRAR SESIÓN
// =======================================================================
async function cerrarSesion() {

    try {

        const response = await fetch(API_URL, {
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

        if (resultado.success) {

            alert('Sesión cerrada');

            window.location.href = '/homeaway/Airbnb/views/index.php';

        }

    } catch (error) {

        console.error(error);

    }

}

window.cerrarSesion = cerrarSesion;

// =======================================================================
//                              INICIAR
// =======================================================================
document.addEventListener('DOMContentLoaded', function() {

    inicializarRegistro();

    inicializarLogin();

    inicializarMenu();

    verificarSesion();

});