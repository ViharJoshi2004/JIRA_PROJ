<?php
session_start();
require 'Config.php'; // must create $conn as mysqli connection

// --- Require login ---
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ---------- MODE: CREATE vs EDIT ----------
$isEdit  = false;
$task_id = null;
$errors  = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $task_id = (int) $_GET['id'];
    $isEdit  = true;
}

// --- Fetch projects for dropdown ---
$projects = [];
$projStmt = $conn->prepare("SELECT id, name FROM project ORDER BY name ASC");
if ($projStmt && $projStmt->execute()) {
    $res = $projStmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $projects[] = $r;
    }
    $projStmt->close();
}

// Helper: create a candidate task key when user leaves it blank
function generate_task_key($pro_id) {
    // Simple predictable-ish key: PRO<pro_id>-<timestamp suffix>
    return 'PRO' . $pro_id . '-' . substr(time(), -5) . '-' . substr(bin2hex(random_bytes(2)),0,4);
}

// -----------------------
// DEFAULT FORM VARIABLES
// -----------------------
$pro_id      = isset($_GET['pro_id']) ? (int)$_GET['pro_id'] : 0;
$task_key    = '';
$title       = '';
$description = '';
$type        = 'Task';
$priority    = 'Medium';
$status      = 'Backlog';
$due_date    = '';
$estmt_hour  = '';

// -----------------------
// IF EDIT: LOAD FROM DB
// -----------------------
if ($isEdit && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $tstmt = $conn->prepare("
        SELECT id, pro_id, task_key, title, description, type, priority, status, 
               due_date, estmt_hour
        FROM task
        WHERE id = ?
        LIMIT 1
    ");
    if ($tstmt) {
        $tstmt->bind_param('i', $task_id);
        if ($tstmt->execute()) {
            $tres = $tstmt->get_result();
            $taskRow = $tres->fetch_assoc();
            if ($taskRow) {
                $pro_id      = (int)$taskRow['pro_id'];
                $task_key    = $taskRow['task_key'];
                $title       = $taskRow['title'];
                $description = $taskRow['description'];
                $type        = $taskRow['type'];
                $priority    = $taskRow['priority'];
                $status      = $taskRow['status'];
                $due_date    = $taskRow['due_date'];
                $estmt_hour  = $taskRow['estmt_hour'];
            } else {
                $errors[] = "Task not found for editing.";
                $isEdit = false; // fallback to create if id invalid
            }
        } else {
            $errors[] = "Database error (load task): " . htmlspecialchars($tstmt->error);
        }
        $tstmt->close();
    } else {
        $errors[] = "Database error (prepare task load): " . htmlspecialchars($conn->error);
    }
}

// -----------------------
// HANDLE POST (CREATE / EDIT)
// -----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If hidden task_id aa raha ho to edit mode pakka
    if (isset($_POST['task_id']) && is_numeric($_POST['task_id'])) {
        $task_id = (int)$_POST['task_id'];
        $isEdit  = true;
    }

    // sanitize inputs
    $pro_id = isset($_POST['pro_id']) ? (int) $_POST['pro_id'] : 0;
    $task_key = trim($_POST['task_key'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'Task';
    $priority = $_POST['priority'] ?? 'Medium';
    $status = $_POST['status'] ?? 'Backlog';
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null; // YYYY-MM-DD
    $estmt_hour = isset($_POST['estmt_hour']) && $_POST['estmt_hour'] !== '' ? (float) $_POST['estmt_hour'] : null;

    // Basic validation
    if ($pro_id <= 0) { $errors[] = 'Please select a project.'; }
    if (strlen($title) < 3) { $errors[] = 'Title must be at least 3 characters.'; }

    if (empty($task_key)) {
        $task_key = generate_task_key($pro_id);
    } else {
        // sanitize: remove spaces and limit length
        $task_key = substr(preg_replace('/\s+/', '-', $task_key), 0, 50);
    }

    if (empty($errors)) {

        // ---------- EDIT MODE ----------
        if ($isEdit && $task_id > 0) {

            $updateSql = "UPDATE task 
                          SET pro_id = ?, 
                              task_key = ?, 
                              title = ?, 
                              description = ?, 
                              type = ?, 
                              priority = ?, 
                              status = ?, 
                              due_date = ?, 
                              estmt_hour = ?
                          WHERE id = ?";

            $stmt = $conn->prepare($updateSql);
            if (!$stmt) {
                $errors[] = "Database error (prepare update): " . $conn->error;
            } else {
                $due_date_param = $due_date ?: null;
                $estmt_param = $estmt_hour !== null ? $estmt_hour : null;

                // i (pro_id), s,s,s,s,s,s,s,d,i
                $bind_success = $stmt->bind_param(
                    "isssssssdi",
                    $pro_id,
                    $task_key,
                    $title,
                    $description,
                    $type,
                    $priority,
                    $status,
                    $due_date_param,
                    $estmt_param,
                    $task_id
                );

                if (!$bind_success) {
                    $errors[] = "Database error (bind update): " . $stmt->error;
                } else {
                    if ($stmt->execute()) {
                        $stmt->close();
                        // success! redirect to view single task
                        header("Location:view.php?task_id=" . $task_id . "&updated=1");
                        exit;
                    } else {
                        $errors[] = "Database error (execute update): [" . $stmt->errno . "] " . $stmt->error;
                    }
                }
                $stmt->close();
            }

        // ---------- CREATE MODE ----------
        } else {

            $insertSql = "INSERT INTO task (pro_id, task_key, title, description, type, priority, status, reporter_id, due_date, estmt_hour)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $maxAttempts = 5;
            $attempt = 0;
            $inserted = false;

            while ($attempt < $maxAttempts && !$inserted) {
                $attempt++;
                $stmt = $conn->prepare($insertSql);
                if (!$stmt) {
                    $errors[] = "Database error (prepare): " . $conn->error;
                    break;
                }

                // due_date may be null, estmt_hour may be null
                $due_date_param = $due_date ?: null;
                $estmt_param = $estmt_hour !== null ? $estmt_hour : null;
                $reporter = $user_id;

                // i (pro_id), s,s,s,s,s,s, i, s, d
                $bind_success = $stmt->bind_param(
                    "issssssisd",
                    $pro_id,
                    $task_key,
                    $title,
                    $description,
                    $type,
                    $priority,
                    $status,
                    $reporter,
                    $due_date_param,
                    $estmt_param
                );

                if (!$bind_success) {
                    $errors[] = "Database error (bind): " . $stmt->error;
                    $stmt->close();
                    break;
                }

                if ($stmt->execute()) {
                    $inserted = true;
                    $new_id = $stmt->insert_id;
                    $stmt->close();
                    // success! Redirect to single task view
                    header("Location:view.php?task_id=" . $new_id . "&created=1");
                    exit;
                } else {
                    // handle duplicate unique key error (MySQL error code 1062)
                    $errNo = $stmt->errno;
                    $errMsg = $stmt->error;
                    $stmt->close();

                    if ($errNo == 1062) {
                        // adjust the key and retry
                        $task_key .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                        // continue loop to retry
                    } else {
                        $errors[] = "Database error (execute): [$errNo] $errMsg";
                        break;
                    }
                }
            } // end retry loop

            if (!$inserted && empty($errors)) {
                $errors[] = "Could not create task after multiple attempts (unique key conflict). Try a different Task Key.";
            }
        } // end create/edit switch
    } // end if empty errors
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php echo $isEdit ? 'Edit Task' : 'Create Task'; ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.task-page {
      min-height: 100vh;
      margin: 0;
      padding: 24px 12px 20px;        /* top/bottom space */
      background-size: cover;
      position: relative;
    }
    .form-card { max-width:600px; margin:11px auto; padding:20px; border-radius:8px;background:#fff; }
    .form-row {margin-bottom:8px;}
    label { display:block; margin-bottom:4px; font-weight:600; }
    input[type="text"], textarea, select, input[type="date"], input[type="number"] {
        width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;
    }
    .errors { background:#ffe6e6; color:#900; padding:10px; border-radius:6px; margin-bottom:12px; }
    .actions { margin-top:16px; }
  </style>
</head>
<body class="task-page">
  <div class="form-card">
    <h2><?php echo $isEdit ? 'Edit Task / Issue' : 'Create Task / Issue'; ?></h2>
    <p>Logged in as <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong> — <a href="dashbord.php">Dashboard</a></p>

    <?php if (!empty($errors)): ?>
      <div class="errors">
        <strong>Errors:</strong>
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <?php if ($isEdit && $task_id): ?>
        <input type="hidden" name="task_id" value="<?php echo (int)$task_id; ?>">
      <?php endif; ?>

      <div class="form-row">
        <label for="pro_id">Project</label>
        <select id="pro_id" name="pro_id" required>
          <option value="">-- Select Project --</option>
          <?php foreach ($projects as $p): ?>
            <option value="<?php echo (int)$p['id']; ?>" <?php echo ($pro_id == $p['id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($p['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-row">
        <label for="task_key">Task Key (optional — leave blank to auto-generate)</label>
        <input id="task_key" name="task_key" type="text" maxlength="50"
               value="<?php echo htmlspecialchars($task_key ?? ''); ?>">
      </div>

      <div class="form-row">
        <label for="title">Title</label>
        <input id="title" name="title" type="text" required maxlength="255"
               value="<?php echo htmlspecialchars($title ?? ''); ?>">
      </div>

      <div class="form-row">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
      </div>

      <div class="form-row" style="display:flex; gap:6px;">
        <div style="flex:1">
          <label for="type">Type</label>
          <select id="type" name="type">
            <?php $types = ['Bug','Task','Story','Improvement']; foreach ($types as $t): ?>
              <option value="<?php echo $t; ?>" <?php echo ($type === $t) ? 'selected' : ''; ?>><?php echo $t; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:1">
          <label for="priority">Priority</label>
          <select id="priority" name="priority">
            <?php $ps = ['Low','Medium','High','Critical']; foreach ($ps as $p): ?>
              <option value="<?php echo $p; ?>" <?php echo ($priority === $p) ? 'selected' : ''; ?>><?php echo $p; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:1">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php $ss = ['Backlog','To Do','In Progress','In Review','Done','Closed']; foreach ($ss as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo ($status === $s) ? 'selected' : ''; ?>><?php echo $s; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row" style="display:flex; gap:12px;">
        <div style="flex:1">
          <label for="due_date">Due Date</label>
          <input id="due_date" name="due_date" type="date"
                 value="<?php echo htmlspecialchars($due_date ?? ''); ?>">
        </div>
        <div style="flex:1">
          <label for="estmt_hour">Estimate Hours (e.g. 3.50)</label>
          <input id="estmt_hour" name="estmt_hour" type="number" step="0.25" min="0"
                 value="<?php echo htmlspecialchars($estmt_hour ?? ''); ?>">
        </div>
      </div>

      <div class="actions">
        <button type="submit"><?php echo $isEdit ? 'Update Task' : 'Create Task'; ?></button>
      
        &nbsp; <a href="view.php">Back to Projects / Tasks</a>

      </div>
    </form>
  </div>
</body>
</html>
<?php include './footer.php'; ?>