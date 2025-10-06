<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    echo '<script>
             alert("Debes iniciar sesión");
             window.location = "inicio.php";
          </script>';
    session_destroy();
    die;
}

include('php/conexion.php');
$usuario = $_SESSION['usuario'];
$consulta_rol = mysqli_query($conexion, "SELECT rol FROM usuarios WHERE usuario='$usuario'");
$rol_usuario = mysqli_fetch_assoc($consulta_rol);

if ($rol_usuario['rol'] !== 'administrador') {
    echo '<script>
             alert("No tienes permisos para acceder a esta página");
             window.location = "inicio.php";
          </script>';
    die;
}

// Consulta inicial (sin filtros)
$consulta_usuarios = mysqli_query($conexion, "SELECT id, nombre_completo, correo, contrasena, usuario, rol FROM usuarios");
$usuarios = mysqli_fetch_all($consulta_usuarios, MYSQLI_ASSOC);
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Usuarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
            white-space: nowrap;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn-header {
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }

        .btn-register {
            background-color: var(--success-color);
            color: white;
        }

        .btn-register:hover {
            background-color: #3aa8d8;
            transform: translateY(-2px);
        }

        .btn-logout {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-logout:hover {
            background-color: #e5177a;
            transform: translateY(-2px);
        }

        .search-container {
            display: flex;
            width: 100%;
            max-width: 100%;
            position: relative;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            padding-right: 40px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
            transition: box-shadow 0.3s;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
        }

        .content {
            padding: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 600px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--gray-color);
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }

        .btn-view {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-view:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-edit {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-edit:hover {
            background-color: #e07e0b;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background-color: #e5177a;
            transform: translateY(-2px);
        }

        .password-field {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
            color: var(--gray-color);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .modal.active {
            display: flex;
            opacity: 1;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(-20px);
            transition: transform 0.3s;
            padding: 25px;
        }

        .modal.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            color: var(--primary-color);
            font-size: 22px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-color);
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: var(--danger-color);
        }

        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            font-size: 13px;
            color: var(--gray-color);
            margin-bottom: 5px;
            display: block;
        }

        .detail-value {
            font-size: 15px;
            font-weight: 500;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: var(--gray-color);
        }

        .no-results i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            th, td {
                padding: 10px;
                font-size: 14px;
            }
            
            .btn {
                padding: 6px 10px;
                font-size: 12px;
            }
            
            .user-details {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn, .btn-header {
                justify-content: center;
                width: 100%;
            }
            
            .modal-content {
                padding: 15px;
            }
        }
                @media (max-width: 768px) {
            .content {
                overflow-x: auto;
            }

            table {
                min-width: 100%;
            }

            .container {
                margin: 10px;
                border-radius: 10px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 18px;
            }

            .btn-header {
                font-size: 12px;
                padding: 6px 10px;
            }

            .search-input {
                font-size: 12px;
                padding: 8px 12px;
            }
        }

    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="header-top">
            <h1><i class="fas fa-users-cog"></i> Administración de Usuarios</h1>
            <div class="header-actions">
                <!-- Botón de Historiales -->
                <a href="php/historiales.php" class="btn-header btn-register">
                    <i class="fas fa-child"></i> Historiales
                </a>
                <a href="registro.php" class="btn-header btn-register">
                    <i class="fas fa-user-plus"></i> Registrar
                </a>
                <a href="php/salir.php" class="btn-header btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
        
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar usuarios...">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
    
    <div class="content">
        <div id="usersTableContainer">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Contraseña</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td> <!-- Aquí agregas el campo usuario -->
                            <td class="password-field"><?php echo str_repeat('•', strlen($usuario['contrasena'])); ?></td>
                            <td>
                                <span style="background-color: <?php echo $usuario['rol'] == 'administrador' ? '#4cc9f0' : '#f8961e'; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
                                    <?php echo htmlspecialchars($usuario['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="ver_usuarios.php?id=<?php echo $usuario['id']; ?>" class="btn btn-view">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href='php/editar.php?id=<?php echo $usuario['id']; ?>' class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href='php/eliminar.php?id=<?php echo $usuario['id']; ?>' class="btn btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?');">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="noResults" class="no-results" style="display: none;">
            <i class="fas fa-user-slash"></i>
            <h3>No se encontraron usuarios</h3>
            <p>No hay resultados que coincidan con tu búsqueda</p>
        </div>
        <div id="loadingIndicator" style="text-align: center; display: none; padding: 20px;">
            <div class="loading"></div>
            <p>Buscando usuarios...</p>
        </div>
    </div>
</div>

<!-- Modal para ver detalles -->
<div class="modal" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Detalles del Usuario</h2>
            <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="user-details" id="userDetails">
            <!-- Los detalles se llenarán con JavaScript -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Variables para controlar la búsqueda
let searchTimeout;
const searchDelay = 300; // 300ms de retraso después de escribir

// Función para realizar la búsqueda con AJAX
function performSearch(searchTerm) {
    // Mostrar indicador de carga
    $('#loadingIndicator').show();
    $('#usersTableContainer').hide();
    $('#noResults').hide();

    // Realizar la petición AJAX
    $.ajax({
        url: 'php/buscar_usuarios.php',
        type: 'GET',
        data: {
            busqueda: searchTerm
        },
        success: function(response) {
            $('#usersTableBody').html(response);
            $('#loadingIndicator').hide();
            
            if ($('#usersTableBody tr').length > 0 && !$('#usersTableBody tr td').first().text().includes('No se encontraron')) {
                $('#usersTableContainer').show();
                $('#noResults').hide();
            } else {
                $('#usersTableContainer').hide();
                $('#noResults').show();
            }
        },
        error: function() {
            $('#loadingIndicator').hide();
            $('#usersTableContainer').show();
            alert('Error al realizar la búsqueda');
        }
    });
}

// Evento para el input de búsqueda
$('#searchInput').on('input', function() {
    const searchTerm = $(this).val().trim();
    
    // Cancelar el timeout anterior si existe
    clearTimeout(searchTimeout);
    
    // Si el campo está vacío, mostrar todos los usuarios inmediatamente
    if (searchTerm === '') {
        performSearch('');
        return;
    }
    
    // Establecer un nuevo timeout para evitar muchas peticiones
    searchTimeout = setTimeout(() => {
        performSearch(searchTerm);
    }, searchDelay);
});

// Cargar todos los usuarios al inicio
$(document).ready(function() {
    performSearch('');
});

    // Función para mostrar el modal con los detalles del usuario
    function viewUser(user) {
        const modal = document.getElementById('userModal');
        const userDetails = document.getElementById('userDetails');
        
        // Formatear la contraseña para mostrar asteriscos
        const passwordStars = '*'.repeat(user.contrasena.length);
        
        // Crear el contenido HTML para los detalles
        userDetails.innerHTML = `
            <div class="detail-item">
                <span class="detail-label">ID</span>
                <div class="detail-value">${user.id}</div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nombre Completo</span>
                <div class="detail-value">${user.nombre_completo}</div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Correo Electrónico</span>
                <div class="detail-value">${user.correo}</div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Contraseña</span>
                <div class="detail-value">${passwordStars}</div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Rol</span>
                <div class="detail-value" style="background-color: ${user.rol === 'administrador' ? '#4cc9f0' : '#f8961e'}; color: white;">
                    ${user.rol}
                </div>
            </div>
        `;
        
        // Mostrar el modal
        modal.classList.add('active');
    }
    
    // Función para cerrar el modal
    function closeModal() {
        const modal = document.getElementById('userModal');
        modal.classList.remove('active');
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>
</body>
</html>