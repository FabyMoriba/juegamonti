<?php
session_start();
// Verificar permisos (solo rol operador padre/madre puede acceder)
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'operador' || 
    ($_SESSION['tipo_operador'] != 'padre' && $_SESSION['tipo_operador'] != 'madre')) {
    header("Location: inicio.php");
    exit();
}
// Guardar correo en la sesión
$_SESSION['correo'] = $_SESSION['usuario']; // <-- Este es el correo del padre/madre
$nombre_usuario = $_SESSION['nombre_completo'];
$tipo_operador = $_SESSION['tipo_operador'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía para Padres - Desarrollo Infantil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a6fa5;
            --secondary: #ff9e4a;
            --accent: #6bbf59;
            --light: #f8f9fa;
            --dark: #343a40;
            --text: #5a5c69;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #6a98d6);
            color: white;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .user-info {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .welcome-message {
            text-align: center;
            margin: 1rem 0;
        }
        
        .welcome-message h1 {
            margin-bottom: 0.5rem;
            font-size: 2.2rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .welcome-message p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        .video-section {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        
        .video-title {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
        }
        
        .video-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--secondary);
            margin: 10px auto;
            border-radius: 2px;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .video-container iframe {
            width: 100%;
            height: 450px;
            border: none;
        }
        
        .video-description {
            margin-top: 1.5rem;
            color: var(--text);
            font-size: 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .option-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border-top: 5px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .option-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }
        
        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
        }
        
        .option-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .option-card:hover .option-icon {
            transform: scale(1.1);
            color: var(--secondary);
        }
        
        .option-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .option-desc {
            color: var(--text);
            margin-bottom: 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.8rem 1.8rem;
            background: var(--secondary);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(255, 158, 74, 0.3);
        }
        
        .btn:hover {
            background: #ff8c2a;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 158, 74, 0.4);
        }
        
        .btn-logout {
            background: #e74a3b;
            box-shadow: 0 4px 10px rgba(231, 74, 59, 0.3);
            margin-right: auto;
        }
        
        .btn-logout:hover {
            background: #d52c1a;
            box-shadow: 0 6px 15px rgba(231, 74, 59, 0.4);
        }
        
        .footer {
            text-align: center;
            margin-top: 4rem;
            padding: 2rem 1rem;
            color: var(--text);
            font-size: 0.9rem;
            border-top: 1px solid #eaeaea;
        }
        
        @media (max-width: 768px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-message h1 {
                font-size: 1.8rem;
            }
            
            .video-container iframe {
                height: 300px;
            }
            
            .user-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-logout {
                margin-right: 0;
                align-self: flex-start;
            }
        }
        
        @media (max-width: 480px) {
            .option-card {
                padding: 1.5rem;
            }
            
            .video-section {
                padding: 1.5rem;
            }
            
            .video-container iframe {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="user-info">
                <a href="php/salir.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
                <div class="user-avatar"><?php echo strtoupper(substr($nombre_usuario, 0, 1)); ?></div>
                <span><?php echo htmlspecialchars($nombre_usuario); ?></span>
            </div>
            
            <div class="welcome-message">
                <h1>Guía para Padres</h1>
                <p>Selecciona qué información deseas consultar</p>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Sección de Video -->
        <div class="video-section">
            <h2 class="video-title">La Importancia del Juego en el Desarrollo Infantil</h2>
            <div class="video-container">
                <!-- Video en español sobre la importancia del juego -->
                <iframe src="videos/Los niños necesitan jugar.mp4" title="La importancia del juego en el desarrollo infantil - Video en español" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <p class="video-description">
                El juego es fundamental para el desarrollo integral de los niños. A través del juego, los niños desarrollan habilidades cognitivas, sociales, emocionales y físicas. Descubre en este video por qué el juego es tan importante y cómo puedes fomentarlo en casa.
            </p>
        </div>
        
        <!-- Opciones de Navegación -->
        <div class="options-grid">
            <!-- Opción 1: Información de Juegos -->
            <div class="option-card">
                <div class="option-icon">
                    <i class="fas fa-gamepad"></i>
                </div>
                <h3 class="option-title">Información de Juegos</h3>
                <p class="option-desc">Conoce en detalle cada juego educativo, sus beneficios y cómo ayudan al desarrollo de tus hijos.</p>
                <a href="informacion_juegos.php" class="btn">
                    <i class="fas fa-book-open"></i> Ver Información
                </a>
            </div>
            
            <!-- Opción 2: Progreso de Niños -->
            <div class="option-card">
                <div class="option-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="option-title">Progreso de tus Hijos</h3>
                <p class="option-desc">Revisa el avance y logros de tus hijos en cada uno de los juegos educativos.</p>
                <a href="php/ver_usuarios.php" class="btn">
                    <i class="fas fa-child"></i> Ver Progreso
                </a>
            </div>
            
            <!-- Opción 3: Consejos para Padres -->
            <div class="option-card">
                <div class="option-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <h3 class="option-title">Consejos para Padres</h3>
                <p class="option-desc">Aprende cómo sacar el máximo provecho de los juegos educativos en casa.</p>
                <a href="consejos_padres.php" class="btn">
                    <i class="fas fa-hands-helping"></i> Ver Consejos
                </a>
            </div>
        </div>
    </div>
</body>
</html>