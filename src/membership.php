<?php
session_start();
require 'Config.php';

// --- Require login ---
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';

// Handle create membership
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    // sanitize + cast to int
    $pro_id    = isset($_POST['pro_id']) ? (int) $_POST['pro_id'] : 0;
    $user_id   = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $role_id   = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0;
    $is_active = isset($_POST['is_active']) && $_POST['is_active'] == '1' ? 1 : 0;

    if ($pro_id <= 0)  $errors[] = 'Please select a project.';
    if ($user_id <= 0) $errors[] = 'Please select a user.';
    if ($role_id <= 0) $errors[] = 'Please select a role.';

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO membership (pro_id, user_id, role_id, is_active) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $errors[] = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("iiii", $pro_id, $user_id, $role_id, $is_active);
            if ($stmt->execute()) {
                $success = "Membership added successfully.";
            } else {
                $errors[] = "Insert failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle delete (correct delete by ID)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int) $_GET['delete'];
    $dstmt = $conn->prepare("DELETE FROM membership WHERE id = ?");
    if ($dstmt) {
        $dstmt->bind_param("i", $del_id);
        $dstmt->execute();
        if ($dstmt->affected_rows > 0) {
            $success = "Membership deleted.";
        } else {
            $errors[] = "Membership not found or could not be deleted.";
        }
        $dstmt->close();
    } else {
        $errors[] = "Prepare failed: " . $conn->error;
    }
}

// --- Fetch dropdown data ---
$projects = $conn->query("SELECT id, name FROM project ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$users    = $conn->query("SELECT id, Username, email FROM users ORDER BY Username")->fetch_all(MYSQLI_ASSOC);
$roles    = $conn->query("SELECT id, name FROM role ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// --- Fetch memberships to list ---
$memberships = [];
$listSql = "
SELECT 
    m.id,
    p.name       AS project_name,
    u.Username   AS user_name,
    u.email      AS user_email,
    r.name       AS role_name,
    m.is_active
FROM membership AS m
LEFT JOIN project AS p ON p.id = m.pro_id
LEFT JOIN users   AS u ON u.id = m.user_id
LEFT JOIN role    AS r ON r.id = m.role_id
ORDER BY m.id DESC
";

$lq = $conn->query($listSql);
if ($lq) {
    $memberships = $lq->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "List query failed: " . $conn->error;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Memberships - JIRA Project</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container { max-width: 980px; margin: 40px auto; background: rgba(255,255,255,0.92); padding: 20px; border-radius: 8px; }
    table { width:100%; border-collapse: collapse; margin-top: 12px; }
    table th, table td { padding: 8px 10px; border: 1px solid #ddd; text-align:left; }
    .msg { padding:10px; margin-bottom:12px; border-radius:4px; }
    .error { background:#ffe6e6; color:#900; }
    .ok { background:#e6ffed; color:#026a00; }
    .actions a { margin-right:8px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Manage Memberships</h2>
    <a href="view.php"> 🔙 Back to View Page</a>

    <?php if ($errors): ?>
      <div class="msg error">
        <ul><?php foreach ($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="msg ok"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Add Membership -->
    <form method="post" style="display:flex;flex-wrap:wrap;gap:8px;align-items:end;">
      <input type="hidden" name="action" value="create">

      <div style="flex:1;">
        <label>Project</label>
        <select name="pro_id" required>
          <option value="">-- Select Project --</option>
          <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="flex:1;">uuyitriiutiuitiouyoiutiuituiuut
        <label>User</label>
        <select name="user_id" required>
          <option value="">-- Select User --</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['Username'].' ('.$u['email'].')') ?></option>
          <?php endforeach; ?>
        </select> 
      </div>

      <div style="flex:1;">
        <label>Role</label>
        <select name="role_id" required>
          <option value="">-- Select Role --</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Active?</label>
        <select name="is_active">
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
      </div>

      <div>
        <button type="submit">Add</button>
      </div>
    </form>

    <!-- Membership List -->
    <h3>Existing Memberships</h3>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Project</th>
          <th>User</th>
          <th>Role</th>
          <th>Active</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$memberships): ?>
          <tr><td colspan="6">No memberships yet.</td></tr>
        <?php else: ?>
          <?php foreach ($memberships as $m): ?>
            <tr>
              <td><?= $m['id'] ?></td>
              <td><?= htmlspecialchars($m['project_name']) ?></td>
              <td><?= htmlspecialchars($m['user_name'].' ('.$m['user_email'].')') ?></td>
              <td><?= htmlspecialchars($m['role_name']) ?></td>
              <td><?= $m['is_active'] ? 'Yes' : 'No' ?></td>
              <td><a href="membership.php?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this membership?')">❌ Delete</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</body>
</html>
