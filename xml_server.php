<?php
// xml_server.php

// Habilitar reporte de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Leer la petición XML recibida
$request_xml = file_get_contents('php://input');

if (!$request_xml) {
    header("HTTP/1.1 400 Bad Request");
    echo "No se recibió ningún dato XML";
    exit;
}

// Función para responder con XML-RPC
function xmlrpc_response($result) {
    return '<?xml version="1.0"?>
<methodResponse>
  <params>
    <param>
      <value><int>' . $result . '</int></value>
    </param>
  </params>
</methodResponse>';
}

// Parsear la petición XML manualmente (simple y rápido)
$xml = simplexml_load_string($request_xml);

if (!$xml || !isset($xml->methodName)) {
    header("HTTP/1.1 400 Bad Request");
    echo "XML inválido o falta methodName";
    exit;
}

$method = (string)$xml->methodName;

if ($method !== 'sumar') {
    // Método no soportado
    header("HTTP/1.1 404 Not Found");
    echo "Método no encontrado";
    exit;
}

// Extraer parámetros
$params = [];
foreach ($xml->params->param as $param) {
    // Extraemos solo enteros en este ejemplo
    $params[] = (int)$param->value->int;
}

if (count($params) < 2) {
    header("HTTP/1.1 400 Bad Request");
    echo "Faltan parámetros";
    exit;
}

$num1 = $params[0];
$num2 = $params[1];
$resultado = $num1 + $num2;

// Guardar en PostgreSQL
try {
    $conn = new PDO('pgsql:host=localhost;port=5433;dbname=postgres', 'clases', '123456');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare('INSERT INTO operacionesguardadas (numero1, numero2, resultado) VALUES (:n1, :n2, :res)');
    $stmt->execute([
        ':n1' => $num1,
        ':n2' => $num2,
        ':res' => $resultado,
    ]);
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "Error de base de datos: " . $e->getMessage();
    exit;
}

// Responder con XML-RPC
header('Content-Type: text/xml');
echo xmlrpc_response($resultado);

