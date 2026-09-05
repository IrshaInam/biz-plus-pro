<?php
// ============================================================
//  BizPulse Pro – Logout API
//  URL: POST http://localhost/bizpulse/api/logout.php
//  Header: X-Session-Token: <token>
// ============================================================
require_once __DIR__ . '/../config.php';
apiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$token = $_SERVER['HTTP_X_SESSION_TOKEN'] ?? '';
if (empty($token)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'No session token provided']));
}

$pdo = getDB();
$stmt = $pdo->prepare("CALL sp_logout(?, ?)");
$stmt->execute([$token, getClientIP()]);

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);