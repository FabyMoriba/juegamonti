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
    <title>Consejos para Padres - Desarrollo Infantil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a6fa5;
            --secondary: #ff9e4a;
            --accent: #6bbf59;
            --light: #f8f9fa;
            --dark: #343a40;
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
            color: var(--dark);
            line-height: 1.6;
            scroll-behavior: smooth;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        header {
            background: linear-gradient(135deg, var(--primary), #6a98d6);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo i {
            font-size: 2.5rem;
            color: var(--secondary);
        }
        
        .logo-text h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .logo-text p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 25px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 20px;
        }
        
        nav a:hover, nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(74, 111, 165, 0.8), rgba(74, 111, 165, 0.9)), url('https://images.unsplash.com/photo-1549056572-75914d6d7e1a?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 0 0 20px 20px;
        }
        
        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .btn {
            display: inline-block;
            background: var(--secondary);
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #ff8c2a;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Sección de consejos */
        .tips-section {
            margin-bottom: 50px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: var(--primary);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--secondary);
            margin: 10px auto;
            border-radius: 2px;
        }
        
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .tip-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .tip-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        .tip-icon {
            height: 180px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .tip-icon i {
            font-size: 4rem;
            color: white;
        }
        
        .tip-info {
            padding: 20px;
        }
        
        .tip-info h3 {
            margin-bottom: 10px;
            color: var(--dark);
            font-size: 1.2rem;
        }
        
        .tip-info p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .tip-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.8rem;
            margin-bottom: 15px;
        }
        
        .age-badge {
            background: var(--accent);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        /* Sección de importancia */
        .importance-section {
            background: white;
            padding: 60px 0;
            margin: 50px 0;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }
        
        .importance-content {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: center;
        }
        
        .importance-text {
            flex: 1;
            min-width: 300px;
        }
        
        .importance-text h2 {
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .importance-text p {
            margin-bottom: 15px;
            color: #555;
        }
        
        .importance-image {
            flex: 1;
            min-width: 300px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .importance-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Sección de actividades */
        .activities-section {
            margin-bottom: 50px;
        }
        
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }
        
        .activity-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            text-align: center;
        }
        
        .activity-card:hover {
            transform: translateY(-5px);
        }
        
        .activity-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .activity-card h3 {
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .activity-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Sección de recursos adicionales */
        .resources-section {
            background: linear-gradient(135deg, var(--primary), #6a98d6);
            color: white;
            padding: 60px 0;
            border-radius: 20px;
            margin-bottom: 50px;
        }
        
        .resources-content {
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .resources-content h2 {
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .resources-content p {
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .footer-column h3 {
            margin-bottom: 20px;
            color: var(--secondary);
            font-size: 1.2rem;
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 10px;
        }
        
        .footer-column ul li a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-column ul li a:hover {
            color: var(--secondary);
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--secondary);
            transform: translateY(-3px);
        }
        
        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            color: #aaa;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            nav ul {
                gap: 15px;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .tips-grid, .activities-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-child"></i>
                <div class="logo-text">
                    <h1>Consejos para Padres</h1>
                    <p>Apoyo y desarrollo infantil en casa</p>
                </div>
            </div>
            <nav>
                <ul>
                    <li><a href="#inicio" class="active">Inicio</a></li>
                    <li><a href="#consejos">Consejos</a></li>
                    <li><a href="#importancia">Importancia</a></li>
                    <li><a href="#actividades">Actividades</a></li>
                    <li><a href="#recursos">Recursos</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="inicio">
        <div class="container">
            <h2>El Papel Fundamental de los Padres en el Desarrollo Infantil</h2>
            <p>Descubre cómo tu apoyo y participación en casa pueden marcar la diferencia en el crecimiento y desarrollo de tus hijos.</p>
            <a href="Guia_padres.php" class="btn">Volver</a>
        </div>
    </section>

    <!-- Sección de consejos -->
    <section class="tips-section container" id="consejos">
        <h2 class="section-title">Consejos Prácticos para Padres</h2>
        <div class="tips-grid">
            <!-- Consejo 1 -->
            <div class="tip-card">
                <div class="tip-icon" style="background: linear-gradient(45deg, #4a6fa5, #6bbf59);">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="tip-info">
                    <h3>Apoyo Emocional</h3>
                    <p>Establece un vínculo emocional fuerte con tu hijo. Escucha activamente sus preocupaciones y celebra sus logros, por pequeños que sean.</p>
                    <div class="tip-meta">
                        <span><i class="far fa-clock"></i> Diario</span>
                        <span class="age-badge">Todas las edades</span>
                    </div>
                </div>
            </div>
            
            <!-- Consejo 2 -->
            <div class="tip-card">
                <div class="tip-icon" style="background: linear-gradient(45deg, #ff9e4a, #ff6b6b);">
                    <i class="fas fa-book"></i>
                </div>
                <div class="tip-info">
                    <h3>Lectura Compartida</h3>
                    <p>Dedica al menos 15 minutos diarios a leer con tu hijo. Esto fortalece su vocabulario, imaginación y crea un hábito positivo.</p>
                    <div class="tip-meta">
                        <span><i class="far fa-clock"></i> 15 min/día</span>
                        <span class="age-badge">1-8 años</span>
                    </div>
                </div>
            </div>
            
            <!-- Consejo 3 -->
            <div class="tip-card">
                <div class="tip-icon" style="background: linear-gradient(45deg, #6bbf59, #4a6fa5);">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="tip-info">
                    <h3>Juego Libre</h3>
                    <p>Permite tiempo para el juego no estructurado. El juego libre desarrolla la creatividad, resolución de problemas y autonomía.</p>
                    <div class="tip-meta">
                        <span><i class="far fa-clock"></i> 30 min/día</span>
                        <span class="age-badge">2-10 años</span>
                    </div>
                </div>
            </div>
            
            <!-- Consejo 4 -->
            <div class="tip-card">
                <div class="tip-icon" style="background: linear-gradient(45deg, #ff6b6b, #ff9e4a);">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="tip-info">
                    <h3>Participación en Tareas Domésticas</h3>
                    <p>Involucra a tus hijos en tareas apropiadas para su edad. Esto desarrolla responsabilidad y habilidades prácticas.</p>
                    <div class="tip-meta">
                        <span><i class="far fa-clock"></i> Según tarea</span>
                        <span class="age-badge">3+ años</span>
                    </div>
                </div>
            </div>
            
            <!-- Consejo 5 -->
            <div class="tip-card">
                <div class="tip-icon" style="background: linear-gradient(45deg, #4a6fa5, #ff9e4a);">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="tip-info">
                    <h3>Comunicación Abierta</h3>
                    <p>Mantén conversaciones significativas con tu hijo. Pregúntale sobre su día, sus intereses y escucha sin juzgar.</p>
                    <div class="tip-meta">
                        <span><i class="far fa-clock"></i> Diario</span>
                        <span class="age-badge">Todas las edades</span>
                    </div>
                </div>
            </div>
            
            <!-- Consejo 6 -->
            <div class="tip-card">
                <div class="tip-icon" style="background: linear-gradient(45deg, #6bbf59, #ff6b6b);">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="tip-info">
                    <h3>Rutinas Estables</h3>
                    <p>Establece horarios consistentes para dormir, comer y jugar. Las rutinas proporcionan seguridad y ayudan al desarrollo cerebral.</p>
                    <div class="tip-meta">
                        <span><i class="far fa-clock"></i> Constante</span>
                        <span class="age-badge">Todas las edades</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de importancia -->
    <section class="importance-section" id="importancia">
        <div class="container">
            <div class="importance-content">
                <div class="importance-text">
                    <h2>¿Por Qué es Tan Importante el Apoyo de los Padres?</h2>
                    <p>El entorno familiar es el primer y más influyente contexto de desarrollo para los niños. Tu participación activa en casa no solo fortalece el vínculo emocional, sino que también:</p>
                    <p><i class="fas fa-check" style="color: var(--accent);"></i> <strong>Estimula el desarrollo cerebral:</strong> Las experiencias en casa ayudan a formar conexiones neuronales cruciales.</p>
                    <p><i class="fas fa-check" style="color: var(--accent);"></i> <strong>Fomenta la autoestima:</strong> Los niños que se sienten apoyados desarrollan una imagen positiva de sí mismos.</p>
                    <p><i class="fas fa-check" style="color: var(--accent);"></i> <strong>Desarrolla habilidades sociales:</strong> Aprenden a comunicarse, cooperar y resolver conflictos.</p>
                    <p><i class="fas fa-check" style="color: var(--accent);"></i> <strong>Mejora el rendimiento académico:</strong> El apoyo parental está directamente relacionado con el éxito escolar.</p>
                    <p>Recuerda: no se trata de ser perfecto, sino de estar presente y comprometido con el desarrollo de tu hijo.</p>
                </div>
                <div class="importance-image">
                    <img src="https://static.guiainfantil.com/media/52594/importancia-tiempo-de-calidad-ninos.jpg" alt="Padre e hijo jugando">
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de actividades -->
    <section class="activities-section container" id="actividades">
        <h2 class="section-title">Actividades para Realizar en Casa</h2>
        <div class="activities-grid">
            <!-- Actividad 1 -->
            <div class="activity-card">
                <div class="activity-icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <h3>Rompecabezas y Juegos de Mesa</h3>
                <p>Desarrollan pensamiento lógico, paciencia y habilidades para resolver problemas.</p>
            </div>
            
            <!-- Actividad 2 -->
            <div class="activity-card">
                <div class="activity-icon">
                    <i class="fas fa-paint-brush"></i>
                </div>
                <h3>Arte y Manualidades</h3>
                <p>Estimulan la creatividad, motricidad fina y expresión emocional.</p>
            </div>
            
            <!-- Actividad 3 -->
            <div class="activity-card">
                <div class="activity-icon">
                    <i class="fas fa-music"></i>
                </div>
                <h3>Música y Baile</h3>
                <p>Fomentan la coordinación, ritmo y expresión corporal.</p>
            </div>
            
            <!-- Actividad 4 -->
            <div class="activity-card">
                <div class="activity-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3>Cocina Sencilla</h3>
                <p>Enseña mediciones, secuencias y responsabilidad mientras se divierten.</p>
            </div>
            
            <!-- Actividad 5 -->
            <div class="activity-card">
                <div class="activity-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3>Crear Cuentos</h3>
                <p>Desarrolla imaginación, vocabulario y habilidades narrativas.</p>
            </div>
            
            <!-- Actividad 6 -->
            <div class="activity-card">
                <div class="activity-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <h3>Construcciones</h3>
                <p>Mejoran la coordinación viso-motora y el pensamiento espacial.</p>
            </div>
        </div>
    </section>

    <!-- Sección de recursos adicionales -->
    <section class="resources-section" id="recursos">
        <div class="container">
            <div class="resources-content">
                <h2>Recursos Adicionales para Padres</h2>
                <p>Encuentra más información y herramientas para apoyar el desarrollo de tus hijos en casa. Recuerda que cada niño es único y se desarrolla a su propio ritmo.</p>
                <a href="videos_retroalimentacion.php" class="btn">Ideas de juegos en casa</a>
            </div>
        </div>
    </section>
    <script>
        // Navegación suave
        document.querySelectorAll('nav a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            });
        });
        
        // Actualizar navegación activa al hacer scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('nav a');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (pageYOffset >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>