<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

$db_host = getenv('DB_HOST') ?: 'db';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'incidencias_db';
$db_user = getenv('DB_USER') ?: 'app_user';
$db_password = getenv('DB_PASSWORD') ?: '';

$redis_host = getenv('REDIS_HOST') ?: 'redis';
$redis_port = getenv('REDIS_PORT') ?: 6379;

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la BD: " . $e->getMessage()]);
    exit();
}

$redis = new Redis();
$redis_connected = false;
try {
    $redis_connected = $redis->connect($redis_host, $redis_port);
} catch (Exception $e) {
}

// 1. PETICIÓN GET: Listar incidencias
if ($method === 'GET') {
    if ($redis_connected) {
        $redis->incr("contador_consultas");
        $cached_data = $redis->get("cache_incidencias");
        if ($cached_data) {
            echo $cached_data;
            exit();
        }
    }

    $stmt = $pdo->query("SELECT * FROM incidencias ORDER BY fecha_creacion DESC");
    $incidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $json_response = json_encode($incidencias);

    if ($redis_connected) {
        $redis->setex("cache_incidencias", 30, $json_response);
    }

    echo $json_response;
    exit();
}

// 2. PETICIÓN POST: Crear incidencia y enviar correo a Mailpit
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['titulo']) || !isset($data['descripcion'])) {
        http_response_code(400);
        echo json_encode(["error" => "Título y descripción son obligatorios"]);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO incidencias (titulo, descripcion, prioridad, estado) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['titulo'],
        $data['descripcion'],
        $data['prioridad'] ?? 'media',
        $data['estado'] ?? 'abierta'
    ]);

    if ($redis_connected) {
        $redis->del("cache_incidencias");
    }

    $to = "admin@incidencias.local";
    $subject = "Nueva Incidencia Creada: " . $data['titulo'];
    $message = "Se ha registrado una nueva incidencia:\n\nTítulo: " . $data['titulo'] . "\nDescripción: " . $data['descripcion'];

    $sock = @fsockopen('mailpit', 1025, $errno, $errstr, 2);
    if ($sock) {
        fread($sock, 512);
        fwrite($sock, "HELO localhost\r\n");
        fread($sock, 512);
        fwrite($sock, "MAIL FROM: <sistema@incidencias.local>\r\n");
        fread($sock, 512);
        fwrite($sock, "RCPT TO: <$to>\r\n");
        fread($sock, 512);
        fwrite($sock, "DATA\r\n");
        fread($sock, 512);
        fwrite($sock, "Subject: $subject\r\nTo: $to\r\nFrom: sistema@incidencias.local\r\n\r\n$message\r\n.\r\n");
        fread($sock, 512);
        fwrite($sock, "QUIT\r\n");
        fclose($sock);
    }

    http_response_code(201);
    echo json_encode(["success" => true, "message" => "Incidencia creada y correo enviado"]);
    exit();
}

// 3. PETICIÓN DELETE: Eliminar incidencia
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(["error" => "ID requerido para eliminar"]);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM incidencias WHERE id = ?");
    $stmt->execute([$id]);

    if ($redis_connected) {
        $redis->del("cache_incidencias");
    }

    echo json_encode(["success" => true, "message" => "Incidencia eliminada"]);
    exit();
}
