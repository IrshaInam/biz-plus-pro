<?php
// ================================================================
//  Super Database Manager – Full CRUD + Search + Sort + Export
//  Local development only
// ================================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "bizpulsepro";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}

// ─── Helper: Get primary key (with safety) ────────────────────
function getPrimaryKey($conn, $table) {
    if (empty($table)) return 'id';
    $res = $conn->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if ($res && $row = $res->fetch_assoc()) {
        return $row['Column_name'];
    }
    return 'id';
}

// ─── Helper: Get column info ──────────────────────────────────
function getColumns($conn, $table) {
    if (empty($table)) return [];
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $cols[] = $row;
        }
    }
    return $cols;
}

// ─── Helper: Get foreign keys ─────────────────────────────────
function getForeignKeys($conn, $table) {
    if (empty($table)) return [];
    $fk = [];
    $query = "
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = '$table'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $fk[$row['COLUMN_NAME']] = [
                'table' => $row['REFERENCED_TABLE_NAME'],
                'column' => $row['REFERENCED_COLUMN_NAME']
            ];
        }
    }
    return $fk;
}

// ─── Get all tables ────────────────────────────────────────────
$tables = [];
$res = $conn->query("SHOW TABLES");
if ($res) {
    while ($row = $res->fetch_array()) {
        $tables[] = $row[0];
    }
}

// ─── AJAX get_row (MUST BE BEFORE ANY HTML OUTPUT) ──────────
if (isset($_GET['action']) && $_GET['action'] === 'get_row') {
    header('Content-Type: application/json');
    try {
        $table = $_GET['table'] ?? '';
        $id = (int)($_GET['id'] ?? 0);
        
        if (empty($table)) {
            throw new Exception('Table name is required');
        }
        if (!$id) {
            throw new Exception('Record ID is required');
        }
        if (!in_array($table, $tables)) {
            throw new Exception('Invalid table: ' . $table);
        }
        
        $pk = getPrimaryKey($conn, $table);
        $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row) {
            $fk = getForeignKeys($conn, $table);
            $row['_fk'] = array_keys($fk);
            echo json_encode($row);
        } else {
            throw new Exception('Record not found');
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ─── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    if (empty($table) || !in_array($table, $tables)) {
        die("❌ Invalid table.");
    }
    $pk = getPrimaryKey($conn, $table);
    $cols = getColumns($conn, $table);
    $fk = getForeignKeys($conn, $table);

    if (isset($_POST['delete_id']) && $_POST['delete_id'] !== '') {
        $del_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        $stmt->bind_param('i', $del_id);
        $stmt->execute();
        header("Location: ?table=$table&msg=✅ Record deleted.");
        exit;
    }

    $is_update = isset($_POST['id']) && $_POST['id'] !== '';
    $id_val = $is_update ? (int)$_POST['id'] : 0;

    $set_parts = [];
    $params = [];
    $types = '';
    foreach ($cols as $col) {
        $name = $col['Field'];
        if ($name === $pk && $col['Extra'] === 'auto_increment') continue;
        $val = $_POST[$name] ?? null;
        if ($val === '' && $col['Null'] === 'NO') $val = null;
        $set_parts[] = "`$name` = ?";
        $params[] = $val;
        $types .= 's';
    }

    if ($is_update) {
        $set_str = implode(', ', $set_parts);
        $sql = "UPDATE `$table` SET $set_str WHERE `$pk` = ?";
        $params[] = $id_val;
        $types .= 'i';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $msg = "✅ Record updated.";
    } else {
        $set_str = implode(', ', $set_parts);
        $sql = "INSERT INTO `$table` SET $set_str";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $msg = "✅ Record added.";
    }
    header("Location: ?table=$table&msg=" . urlencode($msg));
    exit;
}

// ─── Handle Export CSV ────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $table = $_GET['table'] ?? '';
    if ($table && in_array($table, $tables)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $table . '.csv"');
        $out = fopen('php://output', 'w');
        $cols = getColumns($conn, $table);
        $headers = array_column($cols, 'Field');
        fputcsv($out, $headers);
        $res = $conn->query("SELECT * FROM `$table`");
        while ($row = $res->fetch_assoc()) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }
}

// ─── Current table ────────────────────────────────────────────
$selected_table = $_GET['table'] ?? '';
if (!empty($selected_table) && !in_array($selected_table, $tables)) {
    $selected_table = '';
}
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$sort_col = $_GET['sort'] ?? '';
$sort_dir = $_GET['dir'] ?? 'ASC';

$rows = [];
$total_rows = 0;
$columns = [];
$pk = '';
$fk = [];

if (!empty($selected_table)) {
    $pk = getPrimaryKey($conn, $selected_table);
    $columns = getColumns($conn, $selected_table);
    $fk = getForeignKeys($conn, $selected_table);

    $where = '';
    if ($search) {
        $search_terms = [];
        foreach ($columns as $col) {
            $search_terms[] = "`{$col['Field']}` LIKE '%" . $conn->real_escape_string($search) . "%'";
        }
        $where = "WHERE " . implode(' OR ', $search_terms);
    }

    $count_res = $conn->query("SELECT COUNT(*) AS c FROM `$selected_table` $where");
    $total_rows = $count_res ? $count_res->fetch_assoc()['c'] : 0;

    $order = '';
    if ($sort_col && in_array($sort_col, array_column($columns, 'Field'))) {
        $order = "ORDER BY `$sort_col` " . ($sort_dir === 'DESC' ? 'DESC' : 'ASC');
    } else {
        $order = "ORDER BY `$pk` DESC";
    }

    $sql = "SELECT * FROM `$selected_table` $where $order LIMIT $limit OFFSET $offset";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Database Manager – BizPulse Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; padding: 15px; }
        .sidebar { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 15px; height: calc(100vh - 30px); overflow-y: auto; position: sticky; top: 15px; }
        .table-list a { display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; border-radius: 8px; color: #333; text-decoration: none; margin-bottom: 2px; }
        .table-list a:hover { background: #e9ecef; }
        .table-list a.active { background: #0d6efd; color: white; }
        .table-list a .count { background: #e9ecef; color: #495057; padding: 1px 10px; border-radius: 20px; font-size: 12px; }
        .table-list a.active .count { background: rgba(255,255,255,0.2); color: white; }
        .main-card { background: white; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 20px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 15px; }
        .toolbar .form-control { width: 200px; }
        .table-responsive { margin-top: 10px; }
        th { cursor: pointer; white-space: nowrap; user-select: none; }
        th:hover { background: #e9ecef; }
        .action-btns .btn { padding: 2px 8px; font-size: 13px; }
        .modal-body .form-label { font-weight: 600; font-size: 14px; }
        .badge-null { color: #adb5bd; font-style: italic; }
        .theme-toggle { cursor: pointer; }
        .dark { background: #1a1a2e; color: #eee; }
        .dark .sidebar, .dark .main-card { background: #16213e; color: #eee; }
        .dark .table-list a { color: #ddd; }
        .dark .table-list a:hover { background: #1a1a3e; }
        .dark .table-list a.active { background: #0d6efd; }
        .dark .table-list a .count { background: #2a2a4a; color: #aaa; }
        .dark .table { color: #eee; }
        .dark .table td, .dark .table th { border-color: #2a2a4a; }
        .dark .modal-content { background: #16213e; color: #eee; }
        .dark .modal-header, .dark .modal-footer { border-color: #2a2a4a; }
        .dark .form-control, .dark .form-select { background: #1a1a3e; color: #eee; border-color: #2a2a4a; }
        .dark .form-control:focus, .dark .form-select:focus { background: #1a1a3e; color: #eee; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row g-3">
        <div class="col-md-2">
            <div class="sidebar" id="sidebar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">📋 Tables</h5>
                    <span class="theme-toggle" onclick="toggleTheme()" title="Toggle dark/light mode">🌙</span>
                </div>
                <div class="table-list">
                    <?php foreach ($tables as $t): ?>
                        <a href="?table=<?= urlencode($t) ?>" class="<?= ($t === $selected_table) ? 'active' : '' ?>">
                            <?= htmlspecialchars($t) ?>
                            <span class="count">
                                <?php
                                    $cnt = $conn->query("SELECT COUNT(*) AS c FROM `$t`")->fetch_assoc()['c'] ?? 0;
                                    echo $cnt;
                                ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="small text-muted"><i class="fas fa-database"></i> <?= $db ?></div>
            </div>
        </div>

        <div class="col-md-10">
            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($selected_table)): ?>
                <div class="main-card">
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                        <h4 class="mb-0">📄 <?= htmlspecialchars($selected_table) ?> <span class="badge bg-secondary"><?= $total_rows ?> rows</span></h4>
                        <div>
                            <a href="?export=csv&table=<?= urlencode($selected_table) ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </a>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-plus"></i> Add Record
                            </button>
                        </div>
                    </div>

                    <div class="toolbar">
                        <form method="GET" class="d-flex gap-2 flex-wrap">
                            <input type="hidden" name="table" value="<?= htmlspecialchars($selected_table) ?>">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="🔍 Search..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Search</button>
                            <a href="?table=<?= urlencode($selected_table) ?>" class="btn btn-outline-danger btn-sm">Clear</a>
                        </form>
                        <div class="ms-auto">
                            <span class="text-muted small">Page <?= $page ?> of <?= ceil($total_rows / $limit) ?></span>
                        </div>
                    </div>

                    <?php if (empty($rows)): ?>
                        <p class="text-muted text-center py-4">No records found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm">
                                <thead>
                                    <tr>
                                        <?php foreach ($columns as $col): ?>
                                            <th onclick="sortTable('<?= $col['Field'] ?>')">
                                                <?= htmlspecialchars($col['Field']) ?>
                                                <?php if ($sort_col === $col['Field']): ?>
                                                    <i class="fas fa-sort-<?= $sort_dir === 'ASC' ? 'up' : 'down' ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort text-muted" style="opacity:0.3;"></i>
                                                <?php endif; ?>
                                            </th>
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
                                                        if ($val === null) echo '<span class="badge-null">NULL</span>';
                                                        elseif (strlen($val) > 100) echo substr($val, 0, 100) . '…';
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
                                                ><i class="fas fa-edit"></i></button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this record?')">
                                                    <input type="hidden" name="table" value="<?= $selected_table ?>">
                                                    <input type="hidden" name="delete_id" value="<?= $row[$pk] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_rows > $limit): ?>
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center">
                                    <?php
                                        $total_pages = ceil($total_rows / $limit);
                                        for ($i = 1; $i <= $total_pages; $i++):
                                    ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?table=<?= urlencode($selected_table) ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort_col) ?>&dir=<?= urlencode($sort_dir) ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- ADD MODAL -->
                <div class="modal fade" id="addModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-plus-circle text-success"></i> Add Record – <?= htmlspecialchars($selected_table) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="table" value="<?= $selected_table ?>">
                                    <div class="row">
                                        <?php foreach ($columns as $col): ?>
                                            <?php
                                                $name = $col['Field'];
                                                if ($name === $pk && $col['Extra'] === 'auto_increment') continue;
                                                $required = ($col['Null'] === 'NO' && $col['Default'] === null) ? 'required' : '';
                                                $is_fk = isset($fk[$name]);
                                                $type = $col['Type'];
                                            ?>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label"><?= htmlspecialchars($name) ?></label>
                                                <?php if ($is_fk): ?>
                                                    <?php
                                                        $ref_table = $fk[$name]['table'];
                                                        $ref_col   = $fk[$name]['column'];
                                                        $options = [];
                                                        $res2 = $conn->query("SELECT `$ref_col` FROM `$ref_table`");
                                                        if ($res2) {
                                                            while ($r = $res2->fetch_assoc()) {
                                                                $options[] = $r[$ref_col];
                                                            }
                                                        }
                                                    ?>
                                                    <select name="<?= $name ?>" class="form-select">
                                                        <option value="">-- Select --</option>
                                                        <?php foreach ($options as $opt): ?>
                                                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php elseif (strpos($type, 'enum') === 0): ?>
                                                    <?php
                                                        preg_match("/^enum\('(.*)'\)$/", $type, $matches);
                                                        $enum_values = explode("','", $matches[1] ?? '');
                                                    ?>
                                                    <select name="<?= $name ?>" class="form-select">
                                                        <option value="">-- Select --</option>
                                                        <?php foreach ($enum_values as $opt): ?>
                                                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php elseif (strpos($type, 'int') !== false): ?>
                                                    <input type="number" name="<?= $name ?>" class="form-control" <?= $required ?>>
                                                <?php else: ?>
                                                    <input type="text" name="<?= $name ?>" class="form-control" <?= $required ?>>
                                                <?php endif; ?>
                                                <small class="text-muted"><?= $type ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- EDIT MODAL -->
                <div class="modal fade" id="editModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-edit text-primary"></i> Edit Record – <?= htmlspecialchars($selected_table) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body" id="editModalBody">
                                    <div class="text-center text-muted">Loading...</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="main-card text-center py-5">
                    <h3>👈 Select a table from the left</h3>
                    <p class="text-muted">View, search, sort, add, edit, delete, and export any table.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ─── Edit Button: Fetch row data ─────────────────────────────
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const table = this.dataset.table;
        const pk = this.dataset.pk;
        const id = this.dataset.id;
        
        // Show loading
        document.getElementById('editModalBody').innerHTML = '<div class="text-center text-muted">Loading...</div>';
        
        fetch(`?action=get_row&table=${encodeURIComponent(table)}&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    document.getElementById('editModalBody').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }
                let html = `<input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="row">`;
                for (const [key, val] of Object.entries(data)) {
                    if (key === '_fk' || key === pk) continue;
                    const displayVal = val === null ? '' : val;
                    html += `<div class="col-md-6 mb-3">
                                <label class="form-label">${key}</label>
                                <input type="text" name="${key}" class="form-control" value="${String(displayVal).replace(/"/g, '&quot;')}">
                            </div>`;
                }
                html += `</div>`;
                document.getElementById('editModalBody').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('editModalBody').innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
            });
    });
});

// ─── Sort Function ────────────────────────────────────────────
function sortTable(column) {
    const url = new URL(window.location);
    const currentSort = url.searchParams.get('sort');
    const currentDir = url.searchParams.get('dir');
    if (currentSort === column) {
        url.searchParams.set('dir', currentDir === 'ASC' ? 'DESC' : 'ASC');
    } else {
        url.searchParams.set('sort', column);
        url.searchParams.set('dir', 'ASC');
    }
    url.searchParams.set('page', '1');
    window.location = url;
}

// ─── Theme Toggle ─────────────────────────────────────────────
function toggleTheme() {
    document.body.classList.toggle('dark');
    const btn = document.querySelector('.theme-toggle');
    btn.textContent = document.body.classList.contains('dark') ? '☀️' : '🌙';
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
}

if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
    document.querySelector('.theme-toggle').textContent = '☀️';
}

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        const bs = bootstrap.Alert.getInstance(el);
        if (bs) bs.close();
    });
}, 4000);
</script>
</body>
</html>