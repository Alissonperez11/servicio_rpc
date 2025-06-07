<?php
require __DIR__ . '/vendor/autoload.php'; //Carga del Autoload de Composer

use PhpXmlRpc\Client;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;

$client = new Client("http://localhost/servicio_rpc/xml_server.php"); // URL del servidor XML-RPC

$request = new Request("sumar", [
    new Value(4, "int"),
    new Value(9, "int")
]);

$response = $client->send($request);

if (!$response->faultCode()) {
    echo "Resultado: " . $response->value()->scalarval();
} else {
    echo "Error: " . $response->faultString();
}
