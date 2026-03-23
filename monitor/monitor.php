<?php

// CONFIGURACIÓN
$url = "https://crm.ejam.cl";
$timeout = 10; // segundos
$correoDestino = "tuemail@dominio.com";
$correoOrigen = "monitor@dominio.com";

// Inicializar cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_NOBODY, true); // solo headers (más rápido)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_exec($ch);

// Obtener info
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Evaluar estado
$caido = false;
$mensajeError = "";

if ($error) {
    $caido = true;
    $mensajeError = "Error cURL: " . $error;
} elseif ($httpCode != 200) {
    $caido = true;
    $mensajeError = "Código HTTP inesperado: " . $httpCode;
}
$lockFile = __DIR__ . "/alert_sent.lock";

// Si está caído → enviar correo
if ($caido) {
    if (!file_exists($lockFile)) {
    $asunto = "⚠️ Alerta: CRM caído";
    $mensaje = "El sitio $url no está respondiendo correctamente.\n\n";
    $mensaje .= "Detalle:\n" . $mensajeError . "\n";
    $mensaje .= "Fecha: " . date("Y-m-d H:i:s");

    $headers = "From: $correoOrigen\r\n";

    mail($correoDestino, $asunto, $mensaje, $headers);
     file_put_contents($lockFile, time());
    }
    // Opcional: log
    file_put_contents(__DIR__ . "/monitor.log", $mensaje . "\n\n", FILE_APPEND);
} else {
    // si volvió a la normalidad → eliminar lock
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}


?>