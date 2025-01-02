<?php
$keys = include('keys.php');

$host = $keys['host'];
$dbname = $keys['dbname'];
$username = $keys['username'];
$password = $keys['password'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $method = $_GET['met'] ?? null;
    if ($method === 'insert') {
        handleInsert($pdo);
    } elseif ($method === 'select') {
        handleSelect($pdo);
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid or missing 'met' parameter"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Only GET requests are allowed"]);
}

function handleInsert($pdo) {
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $method = $_GET['met'] ?? null;

    if (!$method) {
        http_response_code(400);
        echo json_encode(["error" => "Missing 'met' parameter"]);
        return;
    }

    $createdAt = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("INSERT INTO connection (created_at, ip, method) VALUES (:created_at, :ip, :method)");
        $stmt->execute([
            ':created_at' => $createdAt,
            ':ip' => $clientIp,
            ':method' => $method
        ]);

        echo json_encode(["success" => true, "message" => "Data inserted successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to insert data: " . $e->getMessage()]);
    }
}

function handleSelect($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM connection");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "data" => $results]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch data: " . $e->getMessage()]);
    }
}
?>
