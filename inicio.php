<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Comic Neue', cursive;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Animación de globos */
        .balloon {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            animation: float 15s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) scale(0.5);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100px) scale(1);
                opacity: 0;
            }
        }

        .contenedor__todo {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            position: relative;
            z-index: 1;
            border: 3px solid #ffcc00;
            animation: pulse 2s infinite alternate;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 15px rgba(255, 204, 0, 0.5);
            }
            100% {
                box-shadow: 0 0 30px rgba(255, 204, 0, 0.8);
            }
        }

        .caja__trasera-login {
            text-align: center;
            margin-bottom: 25px;
        }

        .caja__trasera-login h3 {
            margin-bottom: 10px;
            color: #ff6b6b;
            font-size: 24px;
            text-shadow: 2px 2px 0px rgba(0,0,0,0.1);
        }

        .caja__trasera-login p {
            font-size: 16px;
            color: #666;
            margin-top: 0;
        }

        .formulario__login h2 {
            text-align: center;
            color: #4a90e2;
            margin-bottom: 25px;
            font-size: 28px;
            text-shadow: 2px 2px 0px rgba(0,0,0,0.1);
        }

        .formulario__login input {
            width: 100%;
            padding: 15px 20px;
            margin: 12px 0;
            border: 2px solid #ddd;
            border-radius: 12px;
            box-sizing: border-box;
            font-size: 16px;
            transition: all 0.3s;
            background-color: #f9f9f9;
        }

        .formulario__login input:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 10px rgba(74, 144, 226, 0.3);
            outline: none;
        }

        .formulario__login input::placeholder {
            color: #aaa;
        }

        .formulario__login button {
            width: 100%;
            padding: 15px;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 0 #e05555;
        }

        .formulario__login button:hover {
            background-color: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #e05555;
        }

        .formulario__login button:active {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #e05555;
        }

        /* Decoraciones infantiles */
        .decoration {
            position: absolute;
            font-size: 24px;
            z-index: 0;
            animation: bounce 2s infinite alternate;
        }

        @keyframes bounce {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body>
    <!-- Globos animados -->
    <div id="balloons-container"></div>
    
    <!-- Decoraciones -->
    <div class="decoration" style="top: 10%; left: 10%;">🎈</div>
    <div class="decoration" style="top: 15%; right: 15%;">🎨</div>
    <div class="decoration" style="bottom: 20%; left: 20%;">🧸</div>
    <div class="decoration" style="bottom: 15%; right: 10%;">🖍️</div>

    <main>
        <div class="contenedor__todo">
            <div class="caja__trasera-login">
                <h3>¡Bienvenido pequeño explorador!</h3>
                <p>Inicia sesión para comenzar la aventura</p>
            </div>

            <div class="contenedor__login-register">
                <form action="php/login.php" method="POST" class="formulario__login">
                    <h2>Iniciar Sesión</h2>
                    <input type="text" placeholder="Usuario" name="usuario" required>
                    <input type="password" placeholder="Contraseña" name="contrasena" required>
                    <button type="submit">¡Vamos a jugar!</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Crear globos animados
        function createBalloons() {
            const container = document.getElementById('balloons-container');
            const colors = ['#ff6b6b', '#4a90e2', '#ffcc00', '#6bceff', '#a5de6b', '#ff9ff3'];
            
            for (let i = 0; i < 15; i++) {
                const balloon = document.createElement('div');
                balloon.className = 'balloon';
                
                // Tamaño aleatorio
                const size = Math.random() * 60 + 40;
                balloon.style.width = `${size}px`;
                balloon.style.height = `${size}px`;
                
                // Posición inicial aleatoria
                balloon.style.left = `${Math.random() * 100}%`;
                
                // Color aleatorio
                balloon.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                
                // Animación con retraso aleatorio
                balloon.style.animationDelay = `${Math.random() * 15}s`;
                
                container.appendChild(balloon);
            }
        }
        
        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', createBalloons);
    </script>
</body>
</html>