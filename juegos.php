<?php
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'operador' || $_SESSION['tipo_operador'] != 'hijo') {
    header("Location: inicio.php");
    exit();
}

$nombre_usuario = $_SESSION['nombre_completo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zona de Juegos</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            display: flex;
            flex-direction: column;
            background: #ffecd2;
            overflow-x: hidden;
            position: relative;
        }

        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;
            top: 0;
            left: 0;
        }

        .header {
            width: 100%;
            background: linear-gradient(45deg, #ff6b6b, #ffcc5c, #88d8b0);
            background-size: 600% 600%;
            animation: gradientBG 8s ease infinite;
            color: white;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .welcome-message {
            font-size: 2em;
            margin: 30px 0;
            color: #333;
            text-align: center;
            animation: bounce 2s infinite;
            z-index: 1;
        }

        .games-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 30px;
            padding: 20px;
            flex-grow: 1;
            z-index: 1;
            position: relative;
        }

        .game-button {
            background: linear-gradient(135deg, #89f7fe, #66a6ff);
            border: none;
            color: white;
            padding: 20px 40px;
            text-align: center;
            text-decoration: none;
            font-size: 1.5em;
            cursor: pointer;
            border-radius: 50px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            width: 200px;
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .game-button:hover {
            background: linear-gradient(135deg, #f6d365, #fda085);
            transform: scale(1.1);
        }

        .game-button i {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .caminitos-icon {
            position: relative;
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
        }

        .caminitos-icon::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 8px;
            background-color: white;
            border-radius: 4px;
            top: 10px;
            left: 0;
        }

        .caminitos-icon::after {
            content: "";
            position: absolute;
            width: 8px;
            height: 100%;
            background-color: white;
            border-radius: 4px;
            left: 50%;
            transform: translateX(-50%);
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }

        .footer {
            width: 100%;
            background: linear-gradient(45deg, #ff6b6b, #ffcc5c, #88d8b0);
            background-size: 600% 600%;
            animation: gradientBG 8s ease infinite;
            color: white;
            text-align: center;
            padding: 15px 0;
            position: relative;
            z-index: 1;
        }

        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ff5e5e;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 1em;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #e84141;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="particles-js"></div>

    <div class="header">
        <h1>Zona de Juegos para Niños</h1>
        <a href="php/salir.php">
            <button class="logout-button"><i class="fas fa-sign-out-alt"></i> Salir</button>
        </a>
    </div>

    <div class="welcome-message">
        ¡Hola <span id="nombreUsuario"><?php echo htmlspecialchars($nombre_usuario); ?></span>, bienvenido a tu zona de juegos!
    </div>

    <div class="games-container">
        <button class="game-button" onclick="iniciarJuego('memoria')">
            <i class="fas fa-brain"></i>
            Juego de Memoria
        </button>

        <button class="game-button" onclick="iniciarJuego('caminitos')">
            <div class="caminitos-icon"></div>
            Caminitos
        </button>

        <button class="game-button" onclick="iniciarJuego('colores')">
            <i class="fas fa-palette"></i>
            Aprende Colores
        </button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        // Cargar configuración de particles.js
        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": ["#ff6b6b", "#ffcc5c", "#88d8b0", "#6a89cc", "#f8c291"]
                },
                "shape": {
                    "type": "circle",
                },
                "opacity": {
                    "value": 0.7,
                    "random": true,
                },
                "size": {
                    "value": 8,
                    "random": true,
                },
                "line_linked": {
                    "enable": false
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": true,
                    "straight": false,
                    "out_mode": "out"
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    }
                }
            },
            "retina_detect": true
        });

        function hablar(texto) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance();
                utterance.text = texto;
                utterance.lang = 'es-ES';
                utterance.rate = 0.9;
                window.speechSynthesis.speak(utterance);
            }
        }

        window.onload = function() {
            const nombre = document.getElementById('nombreUsuario').textContent;
            hablar(`Hola ${nombre}, bienvenido a tu zona de juegos`);
        };

        function iniciarJuego(juego) {
            if (juego === 'caminitos') {
                window.location.href = 'niveles_seguidor.php';
            } else if (juego === 'colores') {
                window.location.href = 'http://192.168.0.16:5000';
            } else if (juego === 'memoria') {
                window.location.href = 'memoria.php';
            }else {
                alert(`¡Vamos a jugar ${juego}! (Esta funcionalidad se implementará después)`);
            }
        }
    </script>
</body>
</html>