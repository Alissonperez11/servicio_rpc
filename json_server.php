<?php
header('Content-Type: application/json'); //Indica que la respuesta que se va a devolver será en formato JSON.

// Leer el JSON desde el cuerpo de la solicitud
$json = file_get_contents('php://input');
$request = json_decode($json, true);

// Validar entrada JSON-RPC básica
if (!is_array($request) || !isset($request['method']) || !isset($request['id'])) {
    echo json_encode([
        'jsonrpc' => '2.0',
        'error' => [
            'code' => -32600,
            'message' => 'Solicitud inválida'
        ],
        'id' => $request['id'] ?? null
    ]);
    exit;
}

// Definir métodos válidos
$methods = [
    'sumar' => function ($params) {
        // Conexión a PostgreSQL
        $conn = new PDO('pgsql:host=localhost;port=5433;dbname=postgres', 'clases', '123456');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $resultado = $params[0] + $params[1];

        // Insertar operación en tabla
        $stmt = $conn->prepare("INSERT INTO base (numero1, numero2, resultado) VALUES (:n1, :n2, :res)");
        $stmt->execute([
            ':n1' => $params[0],
            ':n2' => $params[1],
            ':res' => $resultado
        ]);

        return $resultado;
    }
];

// Verificar existencia del método
$method = $request['method'];
if (!array_key_exists($method, $methods)) {
    echo json_encode([
        'jsonrpc' => '2.0',
        'error' => [
            'code' => -32601,
            'message' => 'Método no encontrado'
        ],
        'id' => $request['id']
    ]);
    exit;
}

// Ejecutar y devolver resultado
$result = $methods[$method]($request['params']);

echo json_encode([
    'jsonrpc' => '2.0',
    'result' => $result,
    'id' => $request['id']
]);
