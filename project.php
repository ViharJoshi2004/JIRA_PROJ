<?php
session_start();
require 'Config.php'; // must create $conn as mysqli connection and set charset

// require login (optional) -- remove if you don't use sessions
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$key_code = '';
$name = '';
$description = '';
$client = '';
$start_date = '';
$end_date = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize inputs
    $key_code   = strtoupper(trim($_POST['key_code'] ?? ''));
    $name       = trim($_POST['name'] ?? '');
    $description= trim($_POST['description'] ?? '');
    $client     = trim($_POST['client'] ?? '');
    $start_date = trim($_POST['start_date'] ?? ''); // '' or 'YYYY-MM-DD'
    $end_date   = trim($_POST['end_date'] ?? '');

    // basic validation
    if (strlen($name) < 3) { $errors[] = 'Project name must be at least 3 characters.'; }
    if ($key_code === '' || !preg_match('/^[A-Z0-9\-]+$/', $key_code)) {
        $errors[] = 'Project key required: only letters, numbers and dashes (no spaces).';
    }

    if (empty($errors)) {
        // build dynamic insert so we only include date columns if provided
        $fields = ['key_code','name','description','client'];
        $placeholders = ['?','?','?','?'];
        $values = [$key_code, $name, $description, $client];

        if ($start_date !== '') {
            $fields[] = 'start_date';
            $placeholders[] = '?';
            $values[] = $start_date;
        }
        if ($end_date !== '') {
            $fields[] = 'end_date';
            $placeholders[] = '?';
            $values[] = $end_date;
        }

        $sql = "INSERT INTO project (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errors[] = "DB prepare error: " . $conn->error;
        } else {
            // build types string; all our fields are strings or dates -> use 's'
            $types = str_repeat('s', count($values));

            // bind_param requires parameters by reference; prepare call_user_func_array
            $bind_params = [];
            $bind_params[] = & $types;
            for ($i = 0; $i < count($values); $i++) {
                $bind_params[] = & $values[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_params);

            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $stmt->close();
                header("Location: view.php?created=1&id=" . $new_id);
                exit;
            } else {
                if ($stmt->errno === 1062) {
                    $errors[] = "Project key '{$key_code}' already exists. Choose another key.";
                } else {
                    $errors[] = "DB error: (" . $stmt->errno . ") " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create Project</title>
  <link rel="stylesheet" href="style.css">
  <style>

    body.project-page
     {
      width: auto;
     min-height: 10vh;
     padding:24px 6px 20px; /* top/bottom space */
     background-size: cover;
     text-align: left;
     position: relative;
    }
    .card {max-width:550px;margin:50px; margin-bottom:11px;padding:20px;background:#fff;border-radius:11px;}
    label{display:block;margin-bottom:1px;font-weight:600}
    input, textarea { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;}
    .errors { background:#ffe6e6; font-size:14px; color:#900; border-radius:6px; margin-bottom:7px; }
    .actions { margin-top:16px; }
  </style>
</head>
<body class="project-page">
  <div class="card">
    <h2>Create Project</h2>
    <p>Logged in as <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?> — <a href="dashbord.php">Dashboard</a></p>

    <?php if (!empty($errors)): ?>
      <div class="errors"><ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul></div>
    <?php endif; ?>

    <form method="post">
      <label for="key_code">Project Key (unique, short e.g. PROJ)</label>
      <input id="key_code" name="key_code" type="text" maxlength="100" value="<?php echo htmlspecialchars($key_code); ?>" required>

      <label for="name">Project Name</label>
      <input id="name" name="name" type="text" maxlength="150" value="<?php echo htmlspecialchars($name); ?>" required>

      <label for="description">Description</label>
      <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>

      <label for="client">Client</label>
      <input id="client" name="client" type="text" maxlength="100" value="<?php echo htmlspecialchars($client); ?>">

      <div style="display:flex;gap:12px;">
        <div style="flex:1">
          <label for="start_date">Start Date</label>
          <input id="start_date" name="start_date" type="date" value="<?php echo htmlspecialchars($start_date); ?>">
        </div>
        <div style="flex:1">
          <label for="end_date">End Date</label>
          <input id="end_date" name="end_date" type="date" value="<?php echo htmlspecialchars($end_date); ?>">
        </div>
      </div>

      <div class="actions">
        <button type="submit">Create Project</button>
        &nbsp;<a href="view.php">View Projects</a>
      </div>
    </form>
  </div>
</body>
</html>
<?php include './footer.php'; ?>