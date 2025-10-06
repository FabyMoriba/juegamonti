from flask import Flask, render_template, Response, jsonify
import cv2
import numpy as np
import random
import pyttsx3
import threading
from queue import Queue
import time
import copy

app = Flask(__name__)

cap = cv2.VideoCapture(0)

# Inicializar motor de voz
engine = pyttsx3.init()
engine.setProperty('rate', 150)
engine.setProperty('volume', 1.0)

# Cola para manejar los mensajes de voz
voice_queue = Queue()

# Rangos de colores (HSV)
azul_bajos = np.array([100, 150, 50], np.uint8)
azul_altos = np.array([140, 255, 255], np.uint8)

verde_bajos = np.array([40, 70, 70], np.uint8)
verde_altos = np.array([80, 255, 255], np.uint8)

amarillo_bajos = np.array([10, 150, 150], np.uint8)
amarillo_altos = np.array([35, 255, 255], np.uint8)

rojo_bajos1 = np.array([0, 120, 70], np.uint8)
rojo_altos1 = np.array([10, 255, 255], np.uint8)
rojo_bajos2 = np.array([170, 120, 70], np.uint8)
rojo_altos2 = np.array([180, 255, 255], np.uint8)

# Variables globales
colores_nombres = ["Rojo", "Azul", "Amarillo", "Verde"]
nivel = 1
secuencia_actual = 0
objetivos = []
ultima_secuencia_anunciada = -1
error_count = 0
combinacion_actual_hash = ""  # Para identificar la combinación actual
combinacion_correcta_mostrada = False  # Para evitar contar errores después de mostrar la correcta
tiempo_inicio_secuencia = time.time()  # Tiempo de inicio de la secuencia actual
contador_errores_activo = False  # Controla cuando empezar a contar errores
tiempo_activacion_errores = 0  # Tiempo cuando se activa el contador de errores

# Hilo para manejar la voz
def voice_thread():
    while True:
        try:
            texto = voice_queue.get()
            if texto == "STOP":
                break
            engine.say(texto)
            engine.runAndWait()
        except Exception as e:
            print(f"Error en hilo de voz: {e}")
            time.sleep(0.1)

def generar_objetivo(nivel):
    secuencias = []

    if nivel == 1:
        for _ in range(4):
            color = random.choice(colores_nombres)
            secuencias.append({color: 1})

    elif nivel == 2:
        for _ in range(4):
            color = random.choice(colores_nombres)
            secuencias.append({color: 2})

    elif nivel == 3:
        cantidades = [3, 4, 3, 4]
        colores_orden = colores_nombres.copy()
        random.shuffle(colores_orden)
        for i in range(4):
            secuencias.append({colores_orden[i]: cantidades[i]})

    elif nivel == 4:
        for _ in range(4):
            color = random.choice(colores_nombres)
            secuencias.append({color: 5})

    elif nivel == 5:
        for _ in range(4):
            secuencia = {}
            for color in colores_nombres:
                secuencia[color] = random.randint(1, 5)
            secuencias.append(secuencia)

    return secuencias

def detectar_colores(frame):
    hsv = cv2.cvtColor(frame, cv2.COLOR_BGR2HSV)

    mask_azul = cv2.inRange(hsv, azul_bajos, azul_altos)
    mask_verde = cv2.inRange(hsv, verde_bajos, verde_altos)
    mask_amarillo = cv2.inRange(hsv, amarillo_bajos, amarillo_altos)

    mask_rojo1 = cv2.inRange(hsv, rojo_bajos1, rojo_altos1)
    mask_rojo2 = cv2.inRange(hsv, rojo_bajos2, rojo_altos2)
    mask_rojo = cv2.add(mask_rojo1, mask_rojo2)

    count_azul = dibujar_contornos(frame, mask_azul, (255, 0, 0), "Azul")
    count_verde = dibujar_contornos(frame, mask_verde, (0, 255, 0), "Verde")
    count_amarillo = dibujar_contornos(frame, mask_amarillo, (0, 255, 255), "Amarillo")
    count_rojo = dibujar_contornos(frame, mask_rojo, (0, 0, 255), "Rojo")

    return frame, {
        "Rojo": count_rojo,
        "Azul": count_azul,
        "Amarillo": count_amarillo,
        "Verde": count_verde
    }

def dibujar_contornos(frame, mask, color, nombre):
    contornos, _ = cv2.findContours(mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    count = 0
    for contorno in contornos:
        if cv2.contourArea(contorno) > 500:
            count += 1
            x, y, w, h = cv2.boundingRect(contorno)
            cv2.drawContours(frame, [contorno], 0, color, 2)
            cv2.putText(frame, nombre, (x, y - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.7, color, 2)
    return count

def crear_hash_combinacion(conteos):
    """Crea un hash único para la combinación actual de objetos"""
    return f"{conteos['Rojo']},{conteos['Azul']},{conteos['Amarillo']},{conteos['Verde']}"

def verificar_secuencia(objetivo, conteos):
    global error_count, combinacion_actual_hash, combinacion_correcta_mostrada, contador_errores_activo
    
    # Verificar si se cumple EXACTAMENTE lo requerido
    cumplido = True
    for color, cantidad in objetivo.items():
        if conteos.get(color, 0) != cantidad:
            cumplido = False
            break
    
    # Verificar que no hay colores adicionales mostrados
    if cumplido:
        for color in colores_nombres:
            if color not in objetivo and conteos.get(color, 0) > 0:
                cumplido = False
                break
    
    # Solo contar errores si el contador está activo
    if contador_errores_activo:
        # Crear hash de la combinación actual
        nuevo_hash = crear_hash_combinacion(conteos)
        
        # Si la combinación cambió
        if nuevo_hash != combinacion_actual_hash:
            combinacion_actual_hash = nuevo_hash
            
            # Si la nueva combinación es incorrecta y hay objetos visibles, contar error
            if not cumplido and sum(conteos.values()) > 0:
                error_count += 1
                print(f"Error detectado! Combinación: {conteos}. Total de errores: {error_count}")
                combinacion_correcta_mostrada = False
        
        # Si la combinación es correcta, marcar que se mostró la correcta
        if cumplido:
            combinacion_correcta_mostrada = True
    
    return cumplido

def gen_frames():
    global nivel, secuencia_actual, objetivos, ultima_secuencia_anunciada, error_count, combinacion_actual_hash, combinacion_correcta_mostrada, tiempo_inicio_secuencia, contador_errores_activo, tiempo_activacion_errores

    while True:
        success, frame = cap.read()
        if not success:
            break
        else:
            frame, conteos = detectar_colores(frame)
            objetivo = objetivos[secuencia_actual]

            # Anunciar objetivo solo si cambia
            if secuencia_actual != ultima_secuencia_anunciada:
                try:
                    mensaje = f"Secuencia {secuencia_actual + 1}. "
                    for color, cantidad in objetivo.items():
                        if cantidad == 1:
                            mensaje += f"Muestra exactamente {cantidad} objeto {color}. "
                        else:
                            mensaje += f"Muestra exactamente {cantidad} objetos {color}. "
                    
                    # Limpiar la cola de voz antes de añadir nuevo mensaje
                    with voice_queue.mutex:
                        voice_queue.queue.clear()
                    
                    voice_queue.put(mensaje)
                    ultima_secuencia_anunciada = secuencia_actual
                    # REINICIAR CONTADOR DE ERRORES al cambiar de secuencia
                    error_count = 0
                    combinacion_actual_hash = ""
                    combinacion_correcta_mostrada = False
                    tiempo_inicio_secuencia = time.time()  # Reiniciar tiempo
                    contador_errores_activo = False  # Desactivar contador de errores inicialmente
                    tiempo_activacion_errores = tiempo_inicio_secuencia + 5  # Activar en 5 segundos
                    print(f"Secuencia {secuencia_actual + 1} iniciada. Contador de errores se activará en 5 segundos.")
                except Exception as e:
                    print(f"Error en generación de mensaje: {e}")

            # Verificar si es tiempo de activar el contador de errores
            tiempo_actual = time.time()
            if not contador_errores_activo and tiempo_actual >= tiempo_activacion_errores:
                contador_errores_activo = True
                print(f"Contador de errores ACTIVADO para secuencia {secuencia_actual + 1}")

            # Verificar la secuencia
            cumplido = verificar_secuencia(objetivo, conteos)

            if cumplido:
                # Calcular tiempo tomado para esta secuencia
                tiempo_tomado = time.time() - tiempo_inicio_secuencia
                secuencia_actual += 1
                if secuencia_actual >= 4:
                    secuencia_actual = 0
                    if nivel < 5:
                        nivel += 1
                        voice_queue.put(f"¡Nivel {nivel-1} completado! Tiempo: {tiempo_tomado:.1f}s. Errores: {error_count}. Pasando al nivel {nivel}")
                    else:
                        nivel = 1
                        voice_queue.put(f"¡Felicidades! Has completado todos los niveles. Tiempo total. Errores finales: {error_count}. Reiniciando al nivel 1")
                    objetivos = generar_objetivo(nivel)
                    # Reiniciar contador de errores al cambiar de nivel también
                    error_count = 0
                else:
                    voice_queue.put(f"¡Correcto! Tiempo: {tiempo_tomado:.1f}s. Errores: {error_count}. Pasando a la siguiente secuencia")

            # Mostrar información en el frame (incluyendo errores y tiempo)
            cv2.rectangle(frame, (10, 10), (450, 270), (0, 0, 0), -1)
            cv2.putText(frame, f"Nivel: {nivel}", (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
            cv2.putText(frame, f"Secuencia: {secuencia_actual + 1}/4", (20, 70), cv2.FONT_HERSHEY_SIMPLEX, 0.7,
                        (255, 255, 255), 2)
            cv2.putText(frame, f"Errores: {error_count}", (20, 100), cv2.FONT_HERSHEY_SIMPLEX, 0.7,
                        (255, 100, 100) if error_count > 0 else (255, 255, 255), 2)
            
            # Mostrar tiempo transcurrido en la secuencia actual
            tiempo_transcurrido = time.time() - tiempo_inicio_secuencia
            cv2.putText(frame, f"Tiempo: {tiempo_transcurrido:.1f}s", (20, 130), cv2.FONT_HERSHEY_SIMPLEX, 0.7,
                        (255, 255, 255), 2)
            
            # Mostrar estado del contador de errores
            if not contador_errores_activo:
                tiempo_restante = tiempo_activacion_errores - time.time()
                if tiempo_restante > 0:
                    cv2.putText(frame, f"Contador activa en: {tiempo_restante:.1f}s", (20, 160), cv2.FONT_HERSHEY_SIMPLEX, 0.6,
                                (255, 255, 100), 2)
                else:
                    cv2.putText(frame, "Contador ACTIVADO", (20, 160), cv2.FONT_HERSHEY_SIMPLEX, 0.6,
                                (100, 255, 100), 2)
            else:
                cv2.putText(frame, "Contador ACTIVADO", (20, 160), cv2.FONT_HERSHEY_SIMPLEX, 0.6,
                            (100, 255, 100), 2)
            
            # Mostrar estado actual
            estado_texto = "CORRECTO" if cumplido else "INCORRECTO" if sum(conteos.values()) > 0 else "SIN OBJETOS"
            estado_color = (100, 255, 100) if cumplido else (100, 100, 255) if sum(conteos.values()) > 0 else (200, 200, 200)
            cv2.putText(frame, f"Estado: {estado_texto}", (200, 100), cv2.FONT_HERSHEY_SIMPLEX, 0.6, estado_color, 2)
            
            # Mostrar conteo actual de objetos
            cv2.putText(frame, "Actual:", (20, 190), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
            y_offset = 210
            for color in colores_nombres:
                count = conteos.get(color, 0)
                cv2.putText(frame, f"{color}: {count}", (20, y_offset), cv2.FONT_HERSHEY_SIMPLEX, 0.5,
                            (200, 200, 200), 1)
                y_offset += 20

            # Mostrar objetivo
            cv2.putText(frame, "Objetivo:", (200, 190), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
            y_offset = 210
            for color, cant in objetivo.items():
                cv2.putText(frame, f"{color}: {cant}", (200, y_offset), cv2.FONT_HERSHEY_SIMPLEX, 0.5,
                            (255, 255, 255), 1)
                y_offset += 20

            ret, buffer = cv2.imencode('.jpg', frame)
            frame = buffer.tobytes()
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

@app.route('/check_progress')
def check_progress():
    global secuencia_actual, objetivos, error_count, tiempo_inicio_secuencia, contador_errores_activo, tiempo_activacion_errores
    objetivo = objetivos[secuencia_actual]
    success, frame = cap.read()
    if not success:
        return jsonify({"error": "No se pudo capturar el frame"})
    
    frame, conteos = detectar_colores(frame)
    
    # Verificar cumplimiento exacto
    cumplido = True
    for color, cantidad in objetivo.items():
        if conteos.get(color, 0) != cantidad:
            cumplido = False
            break
    
    # Verificar que no hay colores adicionales
    if cumplido:
        for color in colores_nombres:
            if color not in objetivo and conteos.get(color, 0) > 0:
                cumplido = False
                break
    
    tiempo_transcurrido = time.time() - tiempo_inicio_secuencia
    tiempo_restante_activacion = max(0, tiempo_activacion_errores - time.time()) if not contador_errores_activo else 0
    
    return jsonify({
        "current_sequence": secuencia_actual + 1,
        "level": nivel,
        "requirements": objetivo,
        "current_counts": conteos,
        "completed": cumplido,
        "error_count": error_count,
        "time_elapsed": round(tiempo_transcurrido, 1),
        "errors_active": contador_errores_activo,
        "time_until_errors_active": round(tiempo_restante_activacion, 1)
    })

@app.route('/get_current_sequence')
def get_current_sequence():
    global secuencia_actual, objetivos
    objetivo = objetivos[secuencia_actual]
    mensaje = f"Secuencia {secuencia_actual + 1}: "
    for color, cantidad in objetivo.items():
        mensaje += f"{cantidad} {color}, "
    return jsonify({"message": mensaje})

@app.route('/reset_errors')
def reset_errors():
    global error_count, combinacion_actual_hash, combinacion_correcta_mostrada, tiempo_inicio_secuencia, contador_errores_activo, tiempo_activacion_errores
    error_count = 0
    combinacion_actual_hash = ""
    combinacion_correcta_mostrada = False
    tiempo_inicio_secuencia = time.time()  # Reiniciar tiempo también
    contador_errores_activo = False
    tiempo_activacion_errores = tiempo_inicio_secuencia + 5  # Reactivar en 5 segundos
    return jsonify({"message": "Contador de errores reiniciado", "error_count": error_count})

@app.route('/')
def index():
    return render_template('prueba_colores.php')

@app.route('/video_feed')
def video_feed():
    return Response(gen_frames(), mimetype='multipart/x-mixed-replace; boundary=frame')

if __name__ == '__main__':
    objetivos = generar_objetivo(nivel)
    tiempo_inicio_secuencia = time.time()  # Inicializar tiempo al inicio
    contador_errores_activo = False  # Inicialmente desactivado
    tiempo_activacion_errores = tiempo_inicio_secuencia + 5  # Activar en 5 segundos
    # Iniciar hilo de voz
    voice_processor = threading.Thread(target=voice_thread)
    voice_processor.daemon = True
    voice_processor.start()
    
    try:
        app.run(host='0.0.0.0', port=5000, debug=True)
    finally:
        voice_queue.put("STOP")
        voice_processor.join()
