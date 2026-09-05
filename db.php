<?php
// ============================================================
//  BizPulse Pro – Simple Database Connection Test
//  File: C:\xampp\htdocs\bizpulse\test_connection.php
// ============================================================

$host = "localhost";
$user = "root";
$password = "";
$dbname = "bizpulsepro";   // ← BizPulse Pro database

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Database Connected Successfully to bizpulsepro!";

// Optional: Show all tables
$result = mysqli_query($conn, "SHOW TABLES");
echo "<br><br>📋 Tables in bizpulsepro:<br>";
while ($row = mysqli_fetch_array($result)) {
    echo "- " . $row[0] . "<br>";
}

// Close connection
mysqli_close($conn);
?>