<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-tr from-blue-100 via-white to-pink-100 min-h-screen flex items-center justify-center">

    <main class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8">
        <h2 class="text-2xl font-bold text-center text-blue-800 mb-6">Regístrate</h2>

        <form action="php/registro_bd.php" method="POST" class="space-y-4">

            <input type="text" placeholder="Nombre completo" name="nombre_completo" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">

            <div id="campoCorreo">
                <input type="email" placeholder="Correo Electrónico" name="correo" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
            </div>

            <input type="text" placeholder="Usuario" name="usuario" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">

            <input type="password" placeholder="Contraseña" name="contrasena" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">

            <div>
                <label for="rol" class="block mb-1 text-sm font-medium text-gray-700">Rol:</label>
                <select id="rol" name="rol" onchange="mostrarCamposOperador()" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="">Seleccionar</option>
                    <option value="administrador">Administrador</option>
                    <option value="operador">Operador</option>
                    <option value="servicio">Servicio</option>
                </select>
            </div>

            <div id="camposOperador" style="display: none;">
                <label for="tipoOperador" class="block mt-4 mb-1 text-sm font-medium text-gray-700">Tipo de Operador:</label>
                <select id="tipoOperador" name="tipoOperador" onchange="cambiarTipoCorreo()"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                    <option value="">Seleccionar</option>
                    <option value="madre">Madre</option>
                    <option value="padre">Padre</option>
                    <option value="hijo">Hijo</option>
                </select>

                <div id="campoEdad" style="display: none;">
                    <label for="edad" class="block mt-4 mb-1 text-sm font-medium text-gray-700">Edad:</label>
                    <input type="number" id="edad" name="edad" min="0" max="120" placeholder="Ingrese su edad"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-300">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-300 mt-6">
                Registrarse
            </button>
        </form>
        <a href="CRUD.php"
           class="block text-center mt-4 text-blue-600 hover:underline hover:text-blue-800">
           ← Atrás
        </a>
    </main>

    <script>
        function mostrarCamposOperador() {
            const rol = document.getElementById("rol").value;
            const camposOperador = document.getElementById("camposOperador");
            const campoEdad = document.getElementById("campoEdad");

            camposOperador.style.display = (rol === "operador") ? "block" : "none";
            campoEdad.style.display = "none";
            
            // Restablecer el campo de correo a su estado original
            const campoCorreo = document.getElementById("campoCorreo");
            campoCorreo.innerHTML = `
                <input type="email" placeholder="Correo Electrónico" name="correo" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
            `;
        }

        function mostrarCampoEdad() {
            const tipoOperador = document.getElementById("tipoOperador").value;
            const campoEdad = document.getElementById("campoEdad");

            campoEdad.style.display = (tipoOperador === "hijo") ? "block" : "none";
        }

        function cambiarTipoCorreo() {
    const tipoOperador = document.getElementById("tipoOperador").value;
    const campoCorreo = document.getElementById("campoCorreo");
    
    if (tipoOperador === "hijo") {
        campoCorreo.innerHTML = `
            <div class="text-center py-2 text-gray-500">
                Cargando correos disponibles...
            </div>
        `;
        
        fetch('php/obtener_correos.php')
            .then(response => {
                // Primero verificar el estado de la respuesta
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text().then(text => {
                    try {
                        return text ? JSON.parse(text) : [];
                    } catch (e) {
                        console.error('Error parsing JSON:', text);
                        throw new Error('Respuesta inválida del servidor');
                    }
                });
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (data.length > 0) {
                    let selectHTML = `
                        <select name="correo" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="">Seleccione un correo de padre/madre</option>`;
                    
                    data.forEach(correo => {
                        selectHTML += `<option value="${correo}">${correo}</option>`;
                    });
                    
                    selectHTML += `</select>`;
                    campoCorreo.innerHTML = selectHTML;
                } else {
                    campoCorreo.innerHTML = `
                        <div class="text-red-500 text-sm">
                            No se encontraron correos registrados
                        </div>
                        <input type="email" placeholder="Ingrese correo manualmente" name="correo" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 mt-2">
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                campoCorreo.innerHTML = `
                    <div class="text-red-500 text-sm">
                        Error al cargar correos: ${error.message}
                    </div>
                    <input type="email" placeholder="Ingrese correo manualmente" name="correo" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 mt-2">
                `;
            });
    } else {
        campoCorreo.innerHTML = `
            <input type="email" placeholder="Correo Electrónico" name="correo" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
        `;
    }
    
    mostrarCampoEdad();
}
    </script>
</body>
</html>