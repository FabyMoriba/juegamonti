<?php
session_start();
// Verificar permisos (solo rol operador padre/madre puede acceder)
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'operador' || 
    ($_SESSION['tipo_operador'] != 'padre' && $_SESSION['tipo_operador'] != 'madre')) {
    header("Location: inicio.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos Sugeridos - Desarrollo Infantil</title>
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
            min-height: 100vh;
        }
        
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header con estilo unificado */
        header {
            background: linear-gradient(135deg, var(--primary), #6a98d6);
            color: white;
            padding: 15px 0;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }
        
        .logo i {
            font-size: 1.8rem;
            color: var(--secondary);
        }
        
        .logo-text {
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .back-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(255, 158, 74, 0.3);
        }
        
        .back-btn:hover {
            background: #ff8c2a;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 158, 74, 0.4);
        }

        /* Título de página */
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-title h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }
        
        .page-title h1::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--secondary);
            margin: 10px auto;
            border-radius: 2px;
        }
        
        .page-title p {
            color: var(--text);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }
        
        /* Grid de videos mejorado */
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .video-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
            border-top: 5px solid var(--primary);
        }
        
        .video-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }
        
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.15);
        }
        
        .video-container {
            position: relative;
            width: 100%;
            height: 200px;
            background: #000;
            overflow: hidden;
        }
        
        .video-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .video-card:hover .video-thumbnail {
            transform: scale(1.05);
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .video-card:hover .video-overlay {
            opacity: 1;
        }
        
        .play-btn {
            background: rgba(255, 255, 255, 0.9);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .play-btn:hover {
            transform: scale(1.1);
            background: white;
        }
        
        .video-info {
            padding: 20px;
        }
        
        .video-info h3 {
            margin-bottom: 10px;
            color: var(--dark);
            font-size: 1.2rem;
            line-height: 1.3;
        }
        
        .video-info p {
            color: var(--text);
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text);
            font-size: 0.85rem;
        }
        
        .duration {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .age-badge {
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        /* Modal de video mejorado */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: white;
            width: 100%;
            max-width: 800px;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            animation: scaleIn 0.3s ease;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .modal-header {
            padding: 20px;
            background: var(--primary);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .close-modal {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .modal-video-container {
            width: 100%;
            background: #000;
        }
        
        .modal-video {
            width: 100%;
            height: 450px;
            object-fit: contain;
        }
        
        .modal-info {
            padding: 20px;
            background: white;
        }
        
        .modal-info p {
            color: var(--text);
            line-height: 1.5;
            font-size: 1rem;
        }
        
        /* Footer unificado */
        footer {
            background: var(--dark);
            color: white;
            padding: 30px 0 20px;
            margin-top: 50px;
        }
        
        .footer-content {
            text-align: center;
            color: #ddd;
        }
        
        .footer-content p {
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .video-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .modal-video {
                height: 300px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .back-btn {
                align-self: flex-start;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                width: 98%;
                padding: 15px;
            }
            
            .video-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title h1 {
                font-size: 1.6rem;
            }
            
            .modal-content {
                margin: 10px;
            }
            
            .modal-video {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <a href="#" class="logo">
                <i class="fas fa-child"></i>
                <span class="logo-text">Actividades Infantiles</span>
            </a>
            <button class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Volver
            </button>
        </div>
    </header>

    <!-- Contenido principal -->
    <main class="container">        
        <div class="page-title">
            <h1>Videos Educativos</h1>
            <p>Descubre actividades divertidas para realizar en casa con tus hijos y fomentar su desarrollo</p>
        </div>
        
        <div class="video-grid">
            <!-- Video 1 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea0.PNG" alt="Juego con plastilina" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea0.mp4" data-title="Juegos con Plastilina Casera" data-description="Aprende a hacer plastilina no tóxica en casa y descubre actividades para desarrollar la motricidad fina.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos de colores</h3>
                    <p>Aprende a seguir instrucciones y reconocer patrones.</p>
                </div>
            </div>
            
            <!-- Video 2 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea1.PNG" alt="Juego de memoria" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea1.mp4" data-title="Juegos de Memoria con Objetos Cotidianos" data-description="Estimula la memoria y la concentración con materiales que tienes en casa.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos de patrones</h3>
                    <p>Estimula la coordinación, aprende colores y formas. Fomenta el pensamiento lógico.</p>
                    
                </div>
            </div>
            
            <!-- Video 3 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea2.PNG" alt="Juego sensorial" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea2.mp4" data-title="Caja Sensorial con Texturas" data-description="Actividad sensorial para estimular el tacto y la curiosidad en los más pequeños.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juego de Globos auto adhesivos</h3>
                    <p>Actividad que estimula la motricidad y creatividad, sin desorden y diversión</p>
                    
                </div>
            </div>
            
            <!-- Video 4 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea3.PNG" alt="Juego de construcción" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea3.mp4" data-title="Construcciones con Material Reciclado" data-description="Fomenta la creatividad y el pensamiento lógico construyendo con materiales reciclados.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juego de orden de colores</h3>
                    <p>Fomenta la creatividad y el pensamiento lógico al clasificar.</p>
                    
                </div>
            </div>
            
            <!-- Video 5 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea4.PNG" alt="Juego de equilibrio" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea4.mp4" data-title="Circuitos de Equilibrio en Casa" data-description="Crea divertidos circuitos para desarrollar el equilibrio y la coordinación motora.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juego de insertar</h3>
                    <p>Crea divertidos circuitos para la coordinación motora fina al momento de insertar.</p>
                    
                </div>
            </div>
            
            <!-- Video 6 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea5.PNG" alt="Juego de números" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea5.mp4" data-title="Introducción a los Números con Juegos" data-description="Actividades lúdicas para familiarizar a los niños con los números y las matemáticas básicas.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juego de Pinza Fina</h3>
                    <p>Al realizar el movimiento de pinza contribuimos al fortalecimiento de la escritura y agarre del lapiz.</p>
                    
                </div>
            </div>
            
            <!-- Video 7 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea6.PNG" alt="Juego de colores" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea6.mp4" data-title="Aprendiendo Colores con Juegos" data-description="Divertidas actividades para que los niños aprendan y reconozcan los colores.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juego de concentración</h3>
                    <p>Un ejemplo de en que contribuye el juego para el desarrollo infantil.</p>
                    
                </div>
            </div>
            
            <!-- Video 8 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea7.PNG" alt="Juego de roles" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea7.mp4" data-title="Juegos de Roles para Niños" data-description="Fomenta la imaginación y el desarrollo social con juegos de roles creativos.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos para la concentración</h3>
                    <p>Desarrolla la coordinación y fortalecimiento de la motricidad fina</p>
                    
                </div>
            </div>

            <!-- Video 9 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea8.PNG" alt="Juego musical" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea8.mp4" data-title="Actividades Musicales en Casa" data-description="Desarrolla el sentido del ritmo y la coordinación con juegos musicales divertidos.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Estimulación temprana</h3>
                    <p>Se contribuye a la concentración, atención y motricidad</p>
                    
                </div>
            </div>

            <!-- Video 10 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea9.PNG" alt="Juego de agua" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea9.mp4" data-title="Juegos con Agua Seguros" data-description="Actividades acuáticas seguras para desarrollar la coordinación y el disfrute sensorial.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos de insertar</h3>
                    <p>Desarrolla la coordinación ojo-mano y la motricidad fina</p>
                    
                </div>
            </div>

            <!-- Video 11 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea10.PNG" alt="Juego de concentración" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea10.mp4" data-title="Juegos para la Concentración" data-description="Actividades para mejorar la atención y precisión en niños.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos en casa para la concentración</h3>
                    <p>Actividades para mejorar la atención y precisión en niños</p>
                    
                </div>
            </div>

            <!-- Video 12 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea11.PNG" alt="Juego de atención" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea11.mp4" data-title="Juegos para la Atención" data-description="Ejercicios para desarrollar la capacidad de concentración en los niños.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos para la concentración</h3>
                    <p>Ejercicios para desarrollar la capacidad de concentración en los niños</p>
                    
                </div>
            </div>

            <!-- Video 13 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea12.PNG" alt="Motricidad fina" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea12.mp4" data-title="Estimulación de Motricidad Fina" data-description="Actividades para desarrollar la coordinación y destreza manual en casa.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Estimula la motricidad fina en casa</h3>
                    <p>Actividades para desarrollar la coordinación y destreza manual</p>
                    
                </div>
            </div>

            <!-- Video 14 -->
            <div class="video-card">
                <div class="video-container">
                    <img src="img/idea13.PNG" alt="Juego acuático" class="video-thumbnail">
                    <div class="video-overlay">
                        <div class="play-btn" data-video="videos/idea13.mp4" data-title="Juegos con Agua Seguros" data-description="Actividades acuáticas seguras para desarrollar la coordinación y el disfrute sensorial.">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="video-info">
                    <h3>Juegos con Agua Seguros</h3>
                    <p>Actividades acuáticas seguras para desarrollar la coordinación y el disfrute sensorial.</p>
                    
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de video -->
    <div class="modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">Título del video</div>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-video-container">
                <video class="modal-video" controls id="modalVideo">
                    Tu navegador no soporta la reproducción de videos.
                </video>
            </div>
            <div class="modal-info">
                <p id="modalDescription">Descripción del video</p>
            </div>
        </div>
    </div>   
    <script>
        // Modal de video
        const modal = document.getElementById('videoModal');
        const modalVideo = document.getElementById('modalVideo');
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        
        document.querySelectorAll('.play-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const videoSrc = btn.getAttribute('data-video');
                const title = btn.getAttribute('data-title');
                const description = btn.getAttribute('data-description');
                
                modalVideo.src = videoSrc;
                modalTitle.textContent = title;
                modalDescription.textContent = description;
                
                modal.style.display = 'flex';
                modalVideo.play();
            });
        });
        
        document.querySelector('.close-modal').addEventListener('click', () => {
            modal.style.display = 'none';
            modalVideo.pause();
            modalVideo.currentTime = 0;
        });
        
        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                modalVideo.pause();
                modalVideo.currentTime = 0;
            }
        });
        
        // Botón volver
        document.querySelector('.back-btn').addEventListener('click', () => {
            window.location.href = 'consejos_padres.php';
        });
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                modal.style.display = 'none';
                modalVideo.pause();
                modalVideo.currentTime = 0;
            }
        });
    </script>
</body>
</html>