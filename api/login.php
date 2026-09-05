<?php
// ============================================================
//  BizPulse Pro – Login API (with enhanced error handling)
//  URL: POST http://localhost/bizpulse/api/login.php
//  Body (JSON): { "email": "...", "password": "..." }
// ============================================================
require_once __DIR__ . '/../config.php';
apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$body     = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]));
}

$email    = trim($body['email']    ?? '');
$password = trim($body['password'] ?? '');
$ip       = getClientIP();
$agent    = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Email and password are required']));
}

try {
    $pdo = getDB();

    // Call stored procedure sp_login
    $stmt = $pdo->prepare("CALL sp_login(?, ?, ?, ?, @success, @user_id, @role, @name, @token)");
    $stmt->execute([$email, $password, $ip, $agent]);
    $stmt->closeCursor();

    // Read OUT parameters
    $out = $pdo->query("SELECT @success AS success, @user_id AS user_id, @role AS role, @name AS name, @token AS token")->fetch();

    if (!$out || !$out['success']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'token'   => $out['token'],
        'user'    => [
            'id'    => (int) $out['user_id'],
            'name'  => $out['name'],
            'email' => $email,
            'role'  => $out['role'],
        ],
        'message' => 'Login successful',
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database error: ' . $e->getMessage(),
        'code'    => $e->getCode()
    ]);
}