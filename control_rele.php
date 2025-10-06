<?php
// Permitir solicitudes desde otros orígenes (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Obtener datos POST (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["action"])) {
    echo json_encode(["success" => false, "error" => "No se recibió la acción"]);
    exit;
}

$action = $data["action"];

// Dirección IP del ESP32 (ajústala si es diferente)
$esp32_ip = "192.168.137.43";

// Comandos para encender o apagar
if ($action == 1) {
    $command = "1"; // ENCENDER
} elseif ($action == 2) {
    $command = "2"; // APAGAR
} else {
    echo json_encode(["success" => false, "error" => "Acción no válida"]);
    exit;
}

// Enviar solicitud al ESP32 (usando HTTP)
$url = "http://$esp32_ip/control?state=$command"; // Tu ESP32 debe entender esta URL

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Ejecutar y obtener respuesta
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Verificar resultado
if ($httpCode == 200) {
    echo json_encode(["success" => true, "estado" => $command]);
} else {
    echo json_encode(["success" => false, "error" => "No se pudo conectar al ESP32: $error"]);
}
