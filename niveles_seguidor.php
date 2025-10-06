<?php
session_start();
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'operador' || $_SESSION['tipo_operador'] != 'hijo') {
    header("Location: inicio.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminitos - Selección de Fichas de Líneas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #4b6cb7, #8E44AD, #2ECC71, #F1C40F);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            color: #2C3E50;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
            width: 100%;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.5);
            color: #FFFFFF;
        }
        
        .subtitle {
            font-size: 1.0rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            color: #ECF0F1;
        }
        
        .game-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1200px;
            width: 100%;
            justify-content: center;
        }
        
        .tokens-section {
            flex: 2;
            min-width: 300px;
            background: rgba(236, 240, 241, 0.9);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 20px;
            text-align: center;
            color: #2980B9;
        }
        
        .tokens-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .token-option {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 140px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .token-option:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .token-option.selected {
            border-color: #F1C40F;
            background: rgba(241, 196, 15, 0.2);
            transform: scale(1.05);
        }
        
        .token-svg {
            width: 70px;
            height: 70px;
            margin-bottom: 12px;
        }
        
        .token-name {
            font-size: 1rem;
            font-weight: bold;
            color: #2C3E50;
        }
        
        .selection-section {
            flex: 1;
            min-width: 300px;
            background: rgba(236, 240, 241, 0.9);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }
        
        .selected-token-display {
            text-align: center;
            padding: 20px;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }
        
        .selected-token-svg {
            width: 100px;
            height: 100px;
            margin-bottom: 15px;
        }
        
        .selected-token-text {
            font-size: 1.2rem;
            color: #2C3E50;
        }
        
        .selected-token-name {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2980B9;
            margin-top: 5px;
        }
        
        .selection-history {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            max-height: 150px;
            overflow-y: auto;
        }
        
        .history-title {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #2980B9;
        }
        
        .history-item {
            padding: 5px 0;
            border-bottom: 1px solid rgba(44, 62, 80, 0.1);
            display: flex;
            justify-content: space-between;
            color: #2C3E50;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            font-size: 1.1rem;
        }
        
        .btn-primary {
            background: #F1C40F;
            color: #2C3E50;
        }
        
        .btn-primary:hover {
            background: #F39C12;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: #2980B9;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #3498DB;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        footer {
            margin-top: 50px;
            text-align: center;
            opacity: 0.9;
            font-size: 0.9rem;
            color: #ECF0F1;
        }
        
        @media (max-width: 768px) {
            .game-container {
                flex-direction: column;
            }
            
            h1 {
                font-size: 2.2rem;
            }
        }
        /* Botón atrás mejorado */
    #back-button {
      position: absolute;
      top: 20px;
      left: 20px;
      background-color: var(--primary-color);
      border: none;
      color: white;
      padding: 12px 24px;
      font-size: 1em;
      border-radius: 50px;
      cursor: pointer;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      transition: all 0.3s;
      z-index: 10;
    }
    
    #back-button:hover {
      background-color: #2980b9;
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    #back-button:active {
      transform: translateY(0);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    </style>
</head>
<body>
        <!-- Botón Atrás -->
  <button id="back-button" onclick="window.location.href='juegos.php'">← Atrás</button></div>
    <header>
        <h1>Caminitos</h1>
        <p class="subtitle">Selecciona una ficha de línea para comenzar el juego.</p>
    </header>
    <div class="game-container">
        <div class="tokens-section">
            <h2 class="section-title">Fichas de Líneas Disponibles</h2>
            <div class="tokens-grid">
                <div class="token-option" data-token="lineaRecta" data-name="Línea Recta">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <line x1="20" y1="50" x2="80" y2="50" stroke="#2980B9" stroke-width="8" />
                    </svg>
                    <div class="token-name">Línea Recta</div>
                </div>
                
                <div class="token-option" data-token="zigZag" data-name="Zig Zag">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <polyline points="20,30 40,70 60,30 80,70" stroke="#E74C3C" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Zig Zag</div>
                </div>
                
                <div class="token-option" data-token="lineaPico" data-name="Línea Pico">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <polyline points="20,70 35,30 50,70 65,30 80,70" stroke="#2ECC71" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Línea Pico</div>
                </div>
                
                <div class="token-option" data-token="escalera" data-name="Escalera">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <polyline points="20,30 20,70 80,70" stroke="#F1C40F" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Escalera</div>
                </div>
                
                <div class="token-option" data-token="lineaOla" data-name="Línea Ola">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <path d="M20,50 C30,30 50,30 60,50 C70,70 90,70 80,50" stroke="#8E44AD" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Línea Ola</div>
                </div>
                
                <div class="token-option" data-token="serpenteante" data-name="Serpenteante">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <path d="M20,50 C30,30 50,30 60,50 C70,70 80,70 90,50" stroke="#3498DB" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Serpenteante</div>
                </div>
                
                <div class="token-option" data-token="espiral" data-name="Espiral">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <path d="M50,50 Q60,40 70,50 Q60,60 50,50 Q40,60 30,50 Q40,40 50,50" stroke="#E67E22" stroke-width="6" fill="none" />
                    </svg>
                    <div class="token-name">Espiral</div>
                </div>
                
                <div class="token-option" data-token="elele" data-name="Elele">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <path d="M30,30 Q50,20 70,30 Q50,40 30,50 Q50,60 70,70" stroke="#1ABC9C" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Elele</div>
                </div>
                
                <div class="token-option" data-token="curvaContinua" data-name="Curva Continua">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <path d="M20,30 C40,10 60,10 80,30 C60,50 40,50 20,70 C40,90 60,90 80,70" stroke="#D35400" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Curva Continua</div>
                </div>
                
                <div class="token-option" data-token="ondulada" data-name="Ondulada">
                    <svg class="token-svg" viewBox="0 0 100 100">
                        <path d="M20,50 C30,30 50,30 60,50 C70,70 90,70 80,50" stroke="#9B59B6" stroke-width="8" fill="none" />
                    </svg>
                    <div class="token-name">Ondulada</div>
                </div>
            </div>
        </div>
        
        <div class="selection-section">
            <h2 class="section-title">Tu Selección</h2>
            
            <div class="selected-token-display">
                <svg class="selected-token-svg" viewBox="0 0 100 100">
                    <line x1="20" y1="50" x2="80" y2="50" stroke="#2980B9" stroke-width="8" />
                </svg>
                <div class="selected-token-text">Ficha seleccionada:</div>
                <div class="selected-token-name">Ninguna</div>
            </div>
            
            <div class="selection-history">
                <div class="history-title">Historial de Selecciones:</div>
                <div id="history-list"></div>
            </div>
            
            <div class="action-buttons">
                <button class="btn btn-secondary" id="clear-btn">Limpiar</button>
                <button class="btn btn-primary" id="continue-btn" disabled>Continuar</button>
            </div>
        </div>
    </div>
        
    <script>
        // Almacenamiento de selecciones
        let selectedToken = null;
        let selectionHistory = [];
        
        // Elementos DOM
        const tokenOptions = document.querySelectorAll('.token-option');
        const selectedTokenSvg = document.querySelector('.selected-token-svg');
        const selectedTokenName = document.querySelector('.selected-token-name');
        const continueBtn = document.getElementById('continue-btn');
        const clearBtn = document.getElementById('clear-btn');
        const historyList = document.getElementById('history-list');
        
        // Agregar event listeners a las fichas
        tokenOptions.forEach(token => {
            token.addEventListener('click', () => {
                // Deseleccionar todas las fichas
                tokenOptions.forEach(t => t.classList.remove('selected'));
                
                // Seleccionar la ficha clickeada
                token.classList.add('selected');
                
                // Obtener el SVG interno para mostrarlo en la selección
                const svgContent = token.querySelector('.token-svg').innerHTML;
                
                // Guardar la selección actual
                selectedToken = {
                    token: token.getAttribute('data-token'),
                    name: token.getAttribute('data-name'),
                    svg: svgContent,
                    timestamp: new Date().toLocaleTimeString()
                };
                
                // Agregar al historial
                selectionHistory.push({...selectedToken});
                
                // Actualizar la visualización
                updateSelectionDisplay();
                updateHistoryDisplay();
                
                // Habilitar el botón de continuar
                continueBtn.disabled = false;
            });
        });
        
        // Función para actualizar la visualización de la selección actual
        function updateSelectionDisplay() {
            selectedTokenSvg.innerHTML = selectedToken.svg;
            selectedTokenName.textContent = selectedToken.name;
        }
        
        // Función para actualizar el historial
        function updateHistoryDisplay() {
            historyList.innerHTML = '';
            
            // Mostrar solo las últimas 5 selecciones
            const recentHistory = selectionHistory.slice(-5);
            
            recentHistory.forEach((item, index) => {
                const historyItem = document.createElement('div');
                historyItem.className = 'history-item';
                historyItem.innerHTML = `
                    <span>${selectionHistory.length - recentHistory.length + index + 1}. ${item.name}</span>
                    <span>${item.timestamp}</span>
                `;
                historyList.appendChild(historyItem);
            });
            
            // Hacer scroll al último elemento
            historyList.scrollTop = historyList.scrollHeight;
        }
        
        // Botón limpiar
        clearBtn.addEventListener('click', () => {
            selectedToken = null;
            selectionHistory = [];
            
            // Restablecer visualización
            selectedTokenSvg.innerHTML = '<line x1="20" y1="50" x2="80" y2="50" stroke="#2980B9" stroke-width="8" />';
            selectedTokenName.textContent = 'Ninguna';
            historyList.innerHTML = '';
            
            // Deseleccionar todas las fichas
            tokenOptions.forEach(t => t.classList.remove('selected'));
            
            // Deshabilitar el botón de continuar
            continueBtn.disabled = true;
        });
        
        // Botón continuar
        continueBtn.addEventListener('click', () => {
            if (selectedToken) {
                // Guardar en localStorage para usar en la siguiente página
                localStorage.setItem('caminitosSelectedToken', JSON.stringify(selectedToken));
                localStorage.setItem('caminitosSelectionHistory', JSON.stringify(selectionHistory));
                
                // Guardar también la ficha seleccionada en sessionStorage para el proceso de guardado posterior
                sessionStorage.setItem('fichaSeleccionada', selectedToken.name);
                
                // Redirigir a la página de control de tiempo y errores
                alert(`Ficha "${selectedToken.name}" seleccionada. Redirigiendo a la página de control...`);
                window.location.href = 'seguidor.php';
            }
        });
        
        // Cargar historial previo si existe
        document.addEventListener('DOMContentLoaded', () => {
            const savedHistory = localStorage.getItem('caminitosSelectionHistory');
            if (savedHistory) {
                selectionHistory = JSON.parse(savedHistory);
                
                if (selectionHistory.length > 0) {
                    selectedToken = selectionHistory[selectionHistory.length - 1];
                    updateSelectionDisplay();
                    
                    // Resaltar la ficha seleccionada
                    tokenOptions.forEach(token => {
                        if (token.getAttribute('data-token') === selectedToken.token) {
                            token.classList.add('selected');
                        }
                    });
                    
                    // Habilitar el botón de continuar
                    continueBtn.disabled = false;
                }
                
                updateHistoryDisplay();
            }
        });
    </script>
</body>
</html>