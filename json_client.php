<?php
$url = 'http://localhost/servicio_rpc/json_server.php'; // URL del servidor JSON-RPC

// Datos a enviar al servidor
$data = [
    'jsonrpc' => '2.0', // Versión del protocolo JSON-RPC
    'method' => 'sumar',
    'params' => [4, 9],
    'id' => 1
];

// Configuración de la solicitud HTTP
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json",
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options); //Crear contexto de flujo HTTP
$response = file_get_contents($url, false, $context); //Enviar la solicitud y recibir la respuesta

echo $response;
