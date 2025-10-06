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
    <title>Juegos Educativos - Desarrollo Infantil</title>
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
        
        /* Header unificado */
        header {
            background: linear-gradient(135deg, var(--primary), #6a98d6);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: var(--shadow);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        header p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        
        /* Tarjetas de juegos mejoradas */
        .juego {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            padding: 2rem;
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            transition: all 0.3s ease;
            border-top: 5px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .juego::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }
        
        .juego:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 25px rgba(0,0,0,0.15);
        }
        
        .juego img {
            width: 250px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .juego:hover img {
            transform: scale(1.05);
        }
        
        .juego-info {
            flex: 1;
        }
        
        .juego h2 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
        }
        
        .juego h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--secondary);
            margin-top: 5px;
            border-radius: 2px;
        }
        
        .juego p {
            color: var(--text);
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            line-height: 1.6;
        }
        
        .juego h4 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .juego h4 i {
            color: var(--accent);
        }
        
        .juego ul {
            list-style: none;
            padding-left: 0;
        }
        
        .juego li {
            margin-bottom: 0.8rem;
            padding-left: 2rem;
            position: relative;
            color: var(--text);
            line-height: 1.5;
        }
        
        .juego li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent);
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        /* Botón de volver */
        .back-container {
            text-align: center;
            margin: 3rem 0;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 25px;
            background: var(--secondary);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 158, 74, 0.3);
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .back-btn:hover {
            background: #ff8c2a;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(255, 158, 74, 0.4);
        }
        
        /* Footer unificado */
        footer {
            background: var(--dark);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            padding: 0 20px;
        }
        
        .footer-content p {
            margin-bottom: 0.5rem;
            color: #ddd;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .juego {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 1.5rem;
            }
            
            .juego img {
                width: 100%;
                max-width: 300px;
                height: 200px;
            }
            
            .juego h2::after {
                margin: 5px auto 0;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            header p {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .juego {
                padding: 1.2rem;
            }
            
            .juego h2 {
                font-size: 1.5rem;
            }
            
            header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <h1>Descubre Nuestros Juegos Educativos</h1>
        <p>Impulsando el desarrollo motriz y cognitivo de tus hijos</p>
    </div>
</header>

<div class="container">
    <?php
    $juegos = [
        [
            "nombre" => "Caminitos",
            "descripcion" => "Caminitos es un juego híbrido inspirado en los trazos Montessori. En lugar de usar lápices, los niños siguen caminos usando sensores, lo que hace la experiencia más interactiva y tecnológica.",
            "beneficios" => [
                "Mejora la coordinación ojo-mano",
                "Estimula la motricidad fina",
                "Fomenta la concentración y el seguimiento de instrucciones"
            ],
            "imagen" => "caminitos.jpg"
        ],
        [
            "nombre" => "Aprende Colores",
            "descripcion" => "Basado en el test de cajas y bloques, este juego permite que los niños clasifiquen cubos de colores (rojo, azul, verde y amarillo) en diferentes cajas, desarrollando habilidades de lógica y percepción visual.",
            "beneficios" => [
                "Reconocimiento y clasificación de colores",
                "Coordinación motora al manipular objetos",
                "Estimulación del pensamiento lógico"
            ],
            "imagen" => "aprende_colores.jpg"
        ],
        [
            "nombre" => "Memoria",
            "descripcion" => "Un juego de secuencia de movimientos con la mano, como el clásico de luces y sonidos, pero con gestos. Con un guante touch, los niños imitan una serie de movimientos que ven frente a ellos, del 1 al 5.",
            "beneficios" => [
                "Ejercita la memoria visual y motriz",
                "Fomenta la atención y la concentración",
                "Desarrolla la coordinación mano-cerebro"
            ],
            "imagen" => "memoria.jpg"
        ]
    ];

    foreach ($juegos as $juego) {
        echo "<div class='juego'>";
        echo "<img src='imagenes/{$juego['imagen']}' alt='{$juego['nombre']}'>";
        echo "<div class='juego-info'>";
        echo "<h2>{$juego['nombre']}</h2>";
        echo "<p>{$juego['descripcion']}</p>";
        echo "<h4><i class='fas fa-star'></i> Beneficios:</h4>";
        echo "<ul>";
        foreach ($juego['beneficios'] as $beneficio) {
            echo "<li>$beneficio</li>";
        }
        echo "</ul>";
        echo "</div></div>";
    }
    ?>
</div>

<div class="back-container">
    <a href="Guia_padres.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Volver
    </a>
</div>
</body>
</html>