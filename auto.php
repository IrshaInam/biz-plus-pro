<?php
// ============================================================
//  BizPulse Pro – Live Database Viewer
//  File: C:\xampp\htdocs\bizpulse\view_db.php
//  Auto-refresh har 5 second mein (changes live dikhein)
// ============================================================

// ── Database Connection ──────────────────────────────────────
$host = "localhost";
$user = "root";
$password = "";
$dbname = "bizpulsepro";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("❌ Connection failed: " . mysqli_connect_error());
}

// ── Auto-refresh every 5 seconds ────────────────────────────
header("Refresh: 5");   // 5 seconds mein page reload ho jayega

// ── HTML + CSS (Simple Table Display) ──────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BizPulse Pro – Live Database Viewer</title>
    <style>
        * { font-family: 'Segoe UI', sans-serif; }
        body { background: #f0f4f8; padding: 20px; }
        .container { max-width: 95%; margin: auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h1 { color: #1e293b; border-bottom: 3px solid #3b82f6; padding-bottom: 10px; }
        .refresh-info { color: #475569; margin-bottom: 20px; font-size: 14px; }
        .refresh-info span { background: #dbeafe; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e293b; color: white; padding: 10px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        tr:hover { background: #f8fafc; }
        .table-name { background: #e2e8f0; font-weight: bold; padding: 12px; margin-top: 20px; border-radius: 6px; }
        .no-data { color: #94a3b8; font-style: italic; }
        .timestamp { color: #64748b; margin-top: 20px; text-align: right; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 BizPulse Pro – Database Viewer</h1>
        <div class="refresh-info">
            🔄 Auto-refresh: <span>Every 5 seconds</span> &nbsp;|&nbsp; 
            ⏱️ Last updated: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <?php
        // ── Get all table names ──────────────────────────────────
        $tables_result = mysqli_query($conn, "SHOW TABLES");
        if (!$tables_result) {
            echo "<p style='color:red;'>❌ Error fetching tables: " . mysqli_error($conn) . "</p>";
        } else {
            while ($table_row = mysqli_fetch_array($tables_result)) {
                $table_name = $table_row[0];
                echo "<div class='table-name'>📋 Table: <strong>$table_name</strong></div>";

                // ── Fetch all data from this table ──────────────
                $data_result = mysqli_query($conn, "SELECT * FROM `$table_name`");
                if (!$data_result) {
                    echo "<p style='color:red;'>Error reading table: " . mysqli_error($conn) . "</p>";
                    continue;
                }

                if (mysqli_num_rows($data_result) == 0) {
                    echo "<p class='no-data'>✨ No records found.</p>";
                } else {
                    // Get column names
                    $fields = mysqli_fetch_fields($data_result);
                    echo "<table>";
                    echo "<thead><tr>";
                    foreach ($fields as $field) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    echo "</tr></thead><tbody>";

                    // Display rows
                    while ($row = mysqli_fetch_assoc($data_result)) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            $display = ($value === null) ? '<span style="color:#94a3b8;">NULL</span>' : htmlspecialchars($value);
                            echo "<td>$display</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                }
                echo "<br>";
            }
        }

        // ── Close connection ─────────────────────────────────────
        mysqli_close($conn);
        ?>

        <div class="timestamp">
            🔄 Page auto-refreshes every 5 seconds to show live changes.
        </div>
    </div>
</body>
</html>