<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'servicio') {
    header("Location: inicio.php");
    exit();
}
include 'php/conexion.php'; // <-- AGREGA ESTO
$nombre_tecnico = $_SESSION['usuario'];
$fecha_actual = date('d/m/Y H:i:s');
$contenido_activo = isset($_GET['seccion']) ? $_GET['seccion'] : 'inicio' 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificaciones y Mantenimiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background-color: var(--secondary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-custom {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }
        
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .card-header-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: scale(1.05);
        }
        
        .btn-secondary-custom {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .btn-secondary-custom:hover {
            background-color: #1a252f;
            border-color: #1a252f;
            transform: scale(1.05);
        }
        
        .btn-danger-custom {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .btn-danger-custom:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            transform: scale(1.05);
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-online {
            background-color: var(--success-color);
        }
        
        .status-offline {
            background-color: var(--danger-color);
        }
        
        .status-connecting {
            background-color: var(--warning-color);
        }
        
        .section-title {
            color: var(--secondary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .webcam-container {
            width: 100%;
            height: 300px;
            background: #000;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }
        
        .webcam-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .history-table {
            font-size: 0.9rem;
        }
        
        .history-table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .action-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
            text-decoration: none;
            color: var(--secondary-color);
            text-align: center;
        }
        
        .action-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: var(--primary-color);
        }
        
        .action-button i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        /* Centrar la cámara */
        .camera-center {
            display: flex;
            justify-content: center;
        }
        
        .camera-card {
            max-width: 600px;
            width: 100%;
        }
        
        /* Centrar los botones de la cámara */
        .camera-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-tools me-2"></i>Verificaciones y Mantenimiento
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger-custom text-white" href="#">
                            <i class="fas fa-sign-out-alt me-1"></i> Salir
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Sección de navegación a otras páginas -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="section-title"><i class="fas fa-map-signs me-2"></i>Navegación</h2>
                <div class="action-buttons">
                    <a href="caminitos.html" class="action-button">
                        <i class="fas fa-road"></i>
                        <span>Caminitos</span>
                    </a>
                    <a href="tablero_motriz.html" class="action-button">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tablero Motriz</span>
                    </a>
                    <a href="#" class="action-button">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reportes</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna centrada: Verificación de cámara -->
            <div class="col-12 camera-center">
                <div class="card card-custom camera-card">
                    <div class="card-header card-header-custom">
                        <h3 class="mb-0 text-center"><i class="fas fa-camera me-2"></i>Verificación de Cámara Web</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted text-center">Verifique el funcionamiento de la cámara web del sistema.</p>
                        
                        <div class="webcam-container" id="webcam-container">
                            <i class="fas fa-camera" style="font-size: 3rem; color: #666;"></i>
                        </div>
                        
                        <div class="camera-buttons mb-3">
                            <button onclick="startCameraTest()" class="btn btn-primary-custom">
                                <i class="fas fa-play me-1"></i> Iniciar Cámara
                            </button>
                            <button onclick="stopCameraTest()" class="btn btn-secondary-custom">
                                <i class="fas fa-stop me-1"></i> Detener Cámara
                            </button>
                        </div>
                        
                        <div id="camera-status" class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i> Cámara no iniciada
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables de estado
        let webcamStream = null;
        
        // Función para iniciar la cámara web
        async function startCameraTest() {
            const cameraStatus = document.getElementById("camera-status");
            const webcamContainer = document.getElementById("webcam-container");
            
            try {
                cameraStatus.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Activando cámara...';
                cameraStatus.className = 'alert alert-warning';
                
                // Acceder a la cámara del dispositivo
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: {
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: "environment"
                    },
                    audio: false
                });
                
                webcamStream = stream;
                const videoElement = document.createElement("video");
                videoElement.srcObject = stream;
                videoElement.autoplay = true;
                videoElement.playsInline = true;
                videoElement.style.width = "100%";
                videoElement.style.height = "100%";
                videoElement.style.objectFit = "cover";
                
                webcamContainer.innerHTML = "";
                webcamContainer.appendChild(videoElement);
                
                cameraStatus.innerHTML = '<i class="fas fa-check-circle me-2"></i> Cámara activada correctamente';
                cameraStatus.className = 'alert alert-success';
                
            } catch (error) {
                console.error("Error al acceder a la cámara:", error);
                cameraStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i> Error: ' + error.message;
                cameraStatus.className = 'alert alert-danger';
            }
        }
        
        // Función para detener la cámara web
        function stopCameraTest() {
            const cameraStatus = document.getElementById("camera-status");
            const webcamContainer = document.getElementById("webcam-container");
            
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }
            
            webcamContainer.innerHTML = '<i class="fas fa-camera" style="font-size: 3rem; color: #666;"></i>';
            cameraStatus.innerHTML = '<i class="fas fa-info-circle me-2"></i> Cámara detenida';
            cameraStatus.className = 'alert alert-info';
        }
        
        // Inicialización cuando la página carga
        document.addEventListener('DOMContentLoaded', function() {
            // Aquí podríamos inicializar la cámara automáticamente si se desea
            // startCameraTest();
        });
    </script>
</body>
</html>