<?php
// ============================================================
//  BizPulse Pro – PDO Connection Test
// ============================================================

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=bizpulsepro;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database Connected Successfully to bizpulsepro!";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>