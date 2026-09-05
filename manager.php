<?php
// ============================================================
//  BizPulse Pro – Complete Database Manager (Single File)
//  Use only in local development (root password empty)
// ============================================================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "bizpulsepro";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}

// ── Get action ────────────────────────────────────────────────
$action = $_GET['action'] ?? 'list';
$table  = $_GET['table'] ?? '';
$id     = $_GET['id'] ?? 0;

// ── Helper: get primary key column name ──────────────────────
function getPrimaryKey($conn, $table) {
    $result = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if ($row = $result->fetch_assoc()) {
        return $row['Column_name'];
    }
    return 'id';
}

// ── Helper: get column info ──────────────────────────────────
function getColumns($conn, $table) {
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row;
    }
    return $cols;
}

// ── Helper: get foreign key info ─────────────────────────────
function getForeignKeys($conn, $table) {
    $fk = [];
    $query = "
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = '$table'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    $res = $conn->query($query);
    while ($row = $res->fetch_assoc()) {
        $fk[$row['COLUMN_NAME']] = [
            'table' => $row['REFERENCED_TABLE_NAME'],
            'column' => $row['REFERENCED_COLUMN_NAME']
        ];
    }
    return $fk;
}

// ── Process POST (add / update / delete) ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    $pk = getPrimaryKey($conn, $table);
    $cols = getColumns($conn, $table);
    $fk = getForeignKeys($conn, $table);

    if (isset($_POST['delete_id']) && $_POST['delete_id'] !== '') {
        // Delete
        $del_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $stmt->bind_param('i', $del_id);
        $stmt->execute();
        $message = "✅ Record deleted.";
        header("Location: ?table=$table&msg=" . urlencode($message));
        exit;
    }

    // Add or Update
    $is_update = isset($_POST['id']) && $_POST['id'] !== '';
    $id_val = $is_update ? (int)$_POST['id'] : 0;

    // Build insert/update query
    $set_parts = [];
    $params = [];
    $types = '';
    foreach ($cols as $col) {
        $name = $col['Field'];
        if ($name === $pk && $col['Extra'] === 'auto_increment') continue; // skip auto increment
        if (isset($fk[$name])) {
            // Foreign key: allow NULL if empty
            $val = isset($_POST[$name]) && $_POST[$name] !== '' ? (int)$_POST[$name] : null;
        } else {
            $val = $_POST[$name] ?? null;
        }
        if ($val === '' && $col['Null'] === 'NO') {
            $val = null; // will be set to default or error
        }
        $set_parts[] = "`$name` = ?";
        $params[] = $val;
        $types .= 's'; // all as string for simplicity
    }

    if ($is_update) {
        // Update
        $set_str = implode(', ', $set_parts);
        $sql = "UPDATE `$table` SET $set_str WHERE `$pk` = ?";
        $params[] = $id_val;
        $types .= 'i';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $message = "✅ Record updated.";
    } else {
        // Insert
        $set_str = implode(', ', $set_parts);
        $sql = "INSERT INTO `$table` SET $set_str";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $message = "✅ Record added.";
    }
    header("Location: ?table=$table&msg=" . urlencode($message));
    exit;
}

// ── Get all tables ─────────────────────────────────────────────
$tables = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

// ── If a table is selected, fetch its data ───────────────────
$selected_table = $table;
$rows = [];
$columns = [];
$pk = '';
$fk = [];
if ($selected_table && in_array($selected_table, $tables)) {
    $pk = getPrimaryKey($conn, $selected_table);
    $columns = getColumns($conn, $selected_table);
    $fk = getForeignKeys($conn, $selected_table);
    $res = $conn->query("SELECT * FROM `$selected_table`");
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
}

// ── Message from redirect ────────────────────────────────────
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager – BizPulse Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .table-card { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 20px; }
        .table-list { max-height: 500px; overflow-y: auto; }
        .table-list .list-group-item { cursor: pointer; }
        .table-list .list-group-item.active { background: #0d6efd; color: white; border-color: #0d6efd; }
        .badge-count { float: right; background: #e9ecef; color: #495057; padding: 2px 10px; border-radius: 20px; }
        .action-btns .btn { margin: 0 2px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR: Table List -->
        <div class="col-md-3">
            <div class="table-card">
                <h4 class="mb-3">📋 Tables</h4>
                <div class="table-list">
                    <?php foreach ($tables as $t): ?>
                        <a href="?table=<?= urlencode($t) ?>" class="list-group-item list-group-item-action <?= ($t === $selected_table) ? 'active' : '' ?>">
                            <?= htmlspecialchars($t) ?>
                            <span class="badge-count">
                                <?php
                                    $cnt = $conn->query("SELECT COUNT(*) AS c FROM `$t`")->fetch_assoc()['c'];
                                    echo $cnt;
                                ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                <i>⚡ Auto‑detects columns & foreign keys</i>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-9">
            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($selected_table): ?>
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>📄 Table: <span class="text-primary"><?= htmlspecialchars($selected_table) ?></span></h3>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">➕ Add Record</button>
                    </div>

                    <?php if (empty($rows)): ?>
                        <p class="text-muted">No records in this table.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $col): ?>
                                            <th><?= htmlspecialchars($col['Field']) ?></th>
                                        <?php endforeach; ?>
                                        <th style="width:120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <?php foreach ($columns as $col): ?>
                                                <td>
                                                    <?php
                                                        $val = $row[$col['Field']];
                                                        if ($val === null) echo '<span class="text-muted">NULL</span>';
                                                        else echo htmlspecialchars($val);
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="action-btns">
                                                <button class="btn btn-primary btn-sm edit-btn"
                                                    data-table="<?= $selected_table ?>"
                                                    data-pk="<?= $pk ?>"
                                                    data-id="<?= $row[$pk] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                >✏️</button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this record?')">
                                                    <input type="hidden" name="table" value="<?= $selected_table ?>">
                                                    <input type="hidden" name="delete_id" value="<?= $row[$pk] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ─── ADD MODAL ─── -->
                <div class="modal fade" id="addModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">➕ Add Record – <?= htmlspecialchars($selected_table) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="table" value="<?= $selected_table ?>">
                                    <?php foreach ($columns as $col): ?>
                                        <?php
                                            $name = $col['Field'];
                                            if ($name === $pk && $col['Extra'] === 'auto_increment') continue;
                                            $label = $name;
                                            $type = 'text';
                                            $required = ($col['Null'] === 'NO' && $col['Default'] === null) ? 'required' : '';
                                            $default = $col['Default'];
                                            $is_fk = isset($fk[$name]);
                                        ?>
                                        <div class="mb-3">
                                            <label class="form-label"><?= htmlspecialchars($label) ?></label>
                                            <?php if ($is_fk): ?>
                                                <?php
                                                    $ref_table = $fk[$name]['table'];
                                                    $ref_col   = $fk[$name]['column'];
                                                    $options = [];
                                                    $res2 = $conn->query("SELECT `$ref_col` FROM `$ref_table`");
                                                    while ($r = $res2->fetch_assoc()) {
                                                        $options[] = $r[$ref_col];
                                                    }
                                                ?>
                                                <select name="<?= $name ?>" class="form-select">
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($options as $opt): ?>
                                                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" name="<?= $name ?>" class="form-control" placeholder="<?= $default ?: $name ?>" <?= $required ?>>
                                            <?php endif; ?>
                                            <small class="text-muted"><?= $col['Type'] ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ─── EDIT MODAL ─── -->
                <div class="modal fade" id="editModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">✏️ Edit Record – <?= htmlspecialchars($selected_table) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" id="editModalBody">
                                    <!-- Filled by JavaScript -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="table-card text-center py-5">
                    <h4>👈 Select a table from the left</h4>
                    <p class="text-muted">You can view, add, edit, and delete records.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Edit button: fetch row data and fill form ──────────────
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const table = this.dataset.table;
            const pk = this.dataset.pk;
            const id = this.dataset.id;

            fetch(`?action=get_row&table=${table}&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    let html = `<input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">`;
                    for (const [key, val] of Object.entries(data)) {
                        if (key === pk) continue; // primary key hidden already
                        const isFk = data._fk && data._fk.includes(key);
                        if (isFk) {
                            // build select with current value selected
                            // We'll just use text input for simplicity; but we can enhance.
                            html += `<div class="mb-3"><label class="form-label">${key}</label>
                                <input type="text" name="${key}" class="form-control" value="${val ?? ''}"></div>`;
                        } else {
                            html += `<div class="mb-3"><label class="form-label">${key}</label>
                                <input type="text" name="${key}" class="form-control" value="${val ?? ''}"></div>`;
                        }
                    }
                    document.getElementById('editModalBody').innerHTML = html;
                })
                .catch(err => alert('Error loading record: ' + err));
        });
    });
</script>

<?php
// ── AJAX handler for get_row ──────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'get_row') {
    header('Content-Type: application/json');
    $table = $_GET['table'] ?? '';
    $id = (int)($_GET['id'] ?? 0);
    if (!$table || !$id) {
        echo json_encode(['error' => 'Missing parameters']);
        exit;
    }
    $pk = getPrimaryKey($conn, $table);
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        // add foreign key info for frontend
        $fk = getForeignKeys($conn, $table);
        $row['_fk'] = array_keys($fk);
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Record not found']);
    }
    exit;
}
?>