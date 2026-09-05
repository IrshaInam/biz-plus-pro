<?php
// ============================================================
//  BizPulse Pro – Database Configuration
//  Place this file at: C:\xampp\htdocs\bizpulse\config.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // XAMPP default
define('DB_PASS', '');              // XAMPP default (no password)
define('DB_NAME', 'bizpulsepro');   // ← changed to match new database
define('DB_CHARSET', 'utf8mb4');

// PHP session cookie settings (set BEFORE session_start())
define('SESSION_LIFETIME', 3600);   // 1 hour

// ── PDO Connection (used by all API files) ──────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ── CORS & JSON headers for API endpoints ───────────────────
function apiHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Session-Token');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ── Helper: get client IP ────────────────────────────────────
function getClientIP(): string {
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return explode(',', $_SERVER[$key])[0];
        }
    }
    return '0.0.0.0';
}

// ── Helper: verify session token from header ─────────────────
function requireAuth(): array {
    $token = $_SERVER['HTTP_X_SESSION_TOKEN'] ?? ($_GET['token'] ?? '');
    if (empty($token)) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Unauthorized – no token']));
    }
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT s.user_id, u.name, u.email, u.role, s.session_token
        FROM   sessions s
        JOIN   users u ON u.id = s.user_id
        WHERE  s.session_token = ? AND s.is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if (!$user) {
        http_response_code(401);
        die(json_encode(['success' => false, 'error' => 'Session expired or invalid']));
    }
    return $user;
}