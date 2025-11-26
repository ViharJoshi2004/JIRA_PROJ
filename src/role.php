<?php
session_start();
require 'Config.php'; // must set $conn as mysqli connection

// require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';
$editRole = null; // for edit mode

// helper
function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Handle POST actions: add, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD ROLE
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $errors[] = 'Role name cannot be empty.';
        } elseif (strlen($name) < 2) {
            $errors[] = 'Role name must have at least 2 characters.';
        } else {
            $stmt = $conn->prepare("INSERT INTO role (name) VALUES (?)");
            if ($stmt) {
                $stmt->bind_param('s', $name);
                if ($stmt->execute()) {
                    $success = 'Role added successfully.';
                } else {
                    $errors[] = 'Database error while adding role: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = 'Database prepare failed: ' . $conn->error;
            }
        }
    }

    // UPDATE ROLE
    if ($action === 'update') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0) {
            $errors[] = 'Invalid role id for update.';
        } elseif ($name === '') {
            $errors[] = 'Role name cannot be empty.';
        } else {
            $stmt = $conn->prepare("UPDATE role SET name = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('si', $name, $id);
                if ($stmt->execute()) {
                    $success = 'Role updated successfully.';
                } else {
                    $errors[] = 'Database error while updating role: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = 'Database prepare failed: ' . $conn->error;
            }
        }
    }

    // DELETE ROLE
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $errors[] = 'Invalid role id for deletion.';
        } else {
            $stmt = $conn->prepare("DELETE FROM role WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $success = 'Role deleted successfully.';
                } else {
                    $errors[] = 'Database error while deleting role: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = 'Database prepare failed: ' . $conn->error;
            }
        }
    }
}

// If GET ?id=... then load that role for edit
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT id, name FROM role WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $editRole = $res->fetch_assoc() ?: null;
        }
        $stmt->close();
    }
}

// Fetch roles list
$roles = [];
$sel = $conn->prepare("SELECT id, name FROM role ORDER BY id ASC");
if ($sel) {
    $sel->execute();
    $res = $sel->get_result();
    while ($row = $res->fetch_assoc()) {
        $roles[] = $row;
    }
    $sel->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Roles</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { color: #fff; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
    main.roles-wrap {
      position: relative;
      z-index: 2;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 60px 20px;
      min-height: calc(100vh - 120px);
    }
    .roles-card {
      width: 760px;
      max-width: 95%;
      background: rgba(0,0,0,0.65);
      border-radius: 12px;
      padding: 28px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.55);
    }
    .roles-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:18px; }
    .roles-header h1 { margin:0; font-size:1.6rem; }
    .muted { color: rgba(255,255,255,0.65); font-size:0.95rem; margin:6px 0 0 0; }
    .form-row { display:flex; gap:10px; margin-top:12px; }
    .form-row input[type="text"]{
      flex:1; padding:10px 12px; border-radius:8px;
      border:1px solid rgba(255,255,255,0.08);
      background: rgba(255,255,255,0.03); color:#fff;
    }
    .btn { padding:10px 14px; border-radius:8px; border:none; cursor:pointer; background:#0d7b87; color:#fff; font-weight:600; text-decoration:none; display:inline-block; }
    .btn.danger { background:#b93b3b; }
    table.role-table { width:100%; border-collapse:collapse; margin-top:18px; }
    table.role-table th, table.role-table td {
      padding:10px 12px; text-align:left;
      border-bottom:1px dashed rgba(255,255,255,0.06);
    }
    table.role-table th { color: #e6f7fb; font-weight:700; }
    .actions { display:flex; gap:8px; align-items:center; }
    .notice { padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    .notice.success { background: rgba(18, 117, 82, 0.12); color:#b8f1cf; }
    .notice.error { background: rgba(160, 40, 40, 0.08); color:#ffbcbc; }
    .link { color: rgba(255,255,255,0.85); text-decoration:none; font-size:0.95rem; }
  </style>
</head>
<body>
  <div class="overlay"></div>

  <main class="roles-wrap">
    <div class="roles-card">
      <div class="roles-header">
        <div>
          <h1>Roles</h1>
          <p class="muted">Manage role names used in the system (table: <code>role(id, name)</code>).</p>
        </div>
        <div style="text-align:right">
          <a href="dashbord.php" class="link">← Back to dashboard</a>
        </div>
      </div>

      <?php if (!empty($success)): ?>
        <div class="notice success"><?= esc($success) ?></div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="notice error">
          <?php foreach ($errors as $e): ?>
            <div><?= esc($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Add / Edit role -->
      <form method="POST" class="form-row" style="align-items:center;">
        <?php if ($editRole): ?>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= esc($editRole['id']) ?>">
          <input id="role-name" name="name" type="text"
                 placeholder="Role name"
                 value="<?= esc($editRole['name']) ?>" required>
          <button type="submit" class="btn">Update Role</button>
          <a href="role.php" class="btn" style="background:#555;">Cancel</a>
        <?php else: ?>
          <input type="hidden" name="action" value="add">
          <input id="role-name" name="name" type="text"
                 placeholder="New role name (e.g. Admin)" required>
          <button type="submit" class="btn">Add Role</button>
        <?php endif; ?>
      </form>

      <!-- Roles table -->
      <table class="role-table" aria-live="polite">
        <thead>
          <tr>
            <th style="width:72px;">ID</th>
            <th>Role Name</th>
            <th style="width:240px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($roles) === 0): ?>
            <tr><td colspan="3" class="muted">No roles yet. Add one above.</td></tr>
          <?php else: ?>
            <?php foreach ($roles as $r): ?>
              <tr>
                <td><?= esc($r['id']) ?></td>
                <td><?= esc($r['name']) ?></td>
                <td>
                  <div class="actions">
                    <a class="btn" href="role.php?id=<?= esc($r['id']) ?>">Edit</a>

                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this role?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= esc($r['id']) ?>">
                      <button type="submit" class="btn danger">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
