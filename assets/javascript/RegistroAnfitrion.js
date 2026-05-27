const API_ANFITRION = '../app/AnfitrionController.php';

// ================= GUARDAR =================
const Datos = {
    guardar: (k, v) => sessionStorage.setItem(k, v),
    obtener: k => sessionStorage.getItem(k) || '',
    limpiar: () => sessionStorage.clear()
};

// ================= PASO 1 =================
function inicializarPaso1() {

    document.querySelectorAll('.option-button').forEach(btn => {

        btn.onclick = () => {

            Datos.guardar('tipoAlojamiento', btn.dataset.tipo);

            window.location = 'Anfitrion2.html';
        };
    });
}

// ================= PASO 2 =================
function inicializarPaso2() {

    const form = document.getElementById('formDireccion');

    if (!form) return;

    form.onsubmit = e => {

        e.preventDefault();

        [
            'region',
            'direccion',
            'departamento',
            'zona',
            'codigoPostal',
            'ciudad',
            'estado'
        ].forEach(id => {

            Datos.guardar(id, document.getElementById(id).value);

        });

        window.location = 'PrecioXNoche.html';
    };
}

// ================= PASO 3 =================
function inicializarPaso3() {

    const form = document.getElementById('formPrecio');

    if (!form) return;

    form.onsubmit = async e => {

        e.preventDefault();

        const formData = new FormData();

        formData.append('tipoAlojamiento', Datos.obtener('tipoAlojamiento'));
        formData.append('region', Datos.obtener('region'));
        formData.append('direccion', Datos.obtener('direccion'));
        formData.append('departamento', Datos.obtener('departamento'));
        formData.append('zona', Datos.obtener('zona'));
        formData.append('codigoPostal', Datos.obtener('codigoPostal'));
        formData.append('ciudad', Datos.obtener('ciudad'));
        formData.append('estado', Datos.obtener('estado'));

        formData.append(
            'precioNoche',
            document.getElementById('precioNoche').value
        );

        formData.append(
            'numeroNoches',
            document.getElementById('numeroNoches').value
        );

        formData.append(
            'descripcion',
            document.getElementById('descripcion').value
        );

        // ================= IMAGEN =================
        const fileInput = document.getElementById('fileInput');

        if (fileInput && fileInput.files.length > 0) {

            formData.append('imagenes[]', fileInput.files[0]);
        }

        try {

            const res = await fetch(API_ANFITRION, {
                method: 'POST',
                credentials: 'include',
                body: formData
            });

            const data = await res.json();

            console.log(data);

            if (data.success) {

                alert('Propiedad registrada');

                Datos.limpiar();

                window.location = 'Anfitrion3.html';

            } else {

                alert(data.message || 'Error al registrar');
            }

        } catch (error) {

            console.error(error);

            alert('Error de conexión');
        }
    };
}

// ================= INIT =================
document.addEventListener('DOMContentLoaded', () => {

    const path = location.pathname;

    if (path.includes('Anfitrion1')) {
        inicializarPaso1();
    }

    if (path.includes('Anfitrion2')) {
        inicializarPaso2();
    }

    if (path.includes('PrecioXNoche')) {
        inicializarPaso3();
    }
});