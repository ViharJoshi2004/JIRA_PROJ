<?php
// dashboard.php
include 'pdo.php';    // yaha par $conn = new PDO(...) hona chahiye
include 'User1.php';

$user = new User();

// Selected values
$selectedTable     = $_GET['table']      ?? '';
$selectedView      = $_GET['view']       ?? '';
$selectedProjectId = $_GET['project_id'] ?? '';

// Project list (dropdown ke liye)
$projects = [];
try {
    $stmt = $conn->query("SELECT id, name FROM project ORDER BY name");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // optional: error ignore
}

// Allowed views
$views = [
    'project' => [
        'all'      => [
            'label' => 'All Columns',
            'sql'   => 'SELECT * FROM project LIMIT 10'
        ],
        'key_code' => [
            'label' => 'ID, KEY_CODE, Name',
            'sql'   => 'SELECT id, key_code, name FROM project LIMIT 10'
        ],
        'dates'   => [
            'label' => 'Name + Dates',
            'sql'   => 'SELECT id, name, start_date, end_date FROM project LIMIT 10'
        ],
    ],
    'users' => [
        'all'    => [
            'label' => 'All Columns',
            'sql'   => 'SELECT * FROM users LIMIT 10'
        ],
        'basic'  => [
            'label' => 'ID, Username, Email',
            'sql'   => 'SELECT id, Username, email FROM users LIMIT 10'
        ],
    ],
    'task' => [
        'all'    => [
            'label' => 'All Columns (by Project)',
            'sql'   => 'SELECT * FROM task WHERE pro_id = :pro_id LIMIT 10'
        ],
        'status' => [
            'label' => 'ID, Title, Status, Priority (by Project)',
            'sql'   => 'SELECT id, title, status, priority FROM task WHERE pro_id = :pro_id LIMIT 10'
        ],
    ],
];

$tableHtml = "";
$errorMsg  = "";

// Form submit handle
if ($selectedTable !== '' && $selectedView !== '') {
    if (isset($views[$selectedTable]) && isset($views[$selectedTable][$selectedView])) {
        $sql    = $views[$selectedTable][$selectedView]['sql'];
        $params = [];

        // Agar task table hai to project_id (yaha pro_id column) required
        if ($selectedTable === 'task') {
            if ($selectedProjectId === '') {
                $errorMsg = "Please select a project to see its tasks.";
            } else {
                $params[':pro_id'] = $selectedProjectId;
            }
        }

        if ($errorMsg === "") {
            $tableHtml = $user->display_data($conn, $sql, $params);
        }
    } else {
        $errorMsg = "Invalid table or view selected.";
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Internship Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    /* Background image + blur effect */
    body {
        background: url("images/bg.jpg") no-repeat center center fixed;
        background-size: cover;
        margin: 0;
        padding: 0;
        font-family: 'Times New Roman', serif;
    }

    /* Page wrapper */
    .dashboard-container {
        background: rgba(15, 72, 124, 0.4);
        backdrop-filter: blur(8px);
        width: 85%;
        margin: 30px auto;
        padding: 25px;
        border-radius: 12px;
        color: white;
    }

    h1 {
        text-align: center;
        color: #ffffffff;
        margin-bottom: 20px;
    }

    form {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }

    select, button {
        width: auto;
        padding: 10px 14px;          /* 👈 button & select small */
        border-radius: 6px;
        border: none;
        font-size: 15px;           /* 👈 small text */
    }

    select {
        min-width: 150px;
    }

    button {
        cursor: pointer;
    }
    button[type="button"] {
        background: #28a745;
    }
    button[type="button"]:hover {
        background: #1e7e34;
    }
    button[type="submit"] {
        background: #007bff;
    }
    button[type="submit"]:hover {
        background: #0056b3;
    }

    /* View options (radio list) */
    .view-options {
        display: flex;
        flex-direction: column;
        gap: 3px;
        font-size: 13px;
        background: rgba(0,0,0,0.35);
        padding: 6px 8px;
        border-radius: 6px;
    }
    .view-options label {
        cursor: pointer;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.15);
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #ddddddff;
        padding: 8px;
        color:white;
    }
    th {
        background: rgba(0, 0, 0, 0.5);
        font-weight: bold;
    }

    p[style='color:red;'] {
        text-align: center;
        font-weight: bold;
    }
    .form-container{
        width: 100%;
        background: rgba(24, 201, 195, 0.3);
        display: flex;
        justify-content: space-between;
    }
</style>

<body>
<div class="dashboard-container">
    <h1>Welcome to the Project Dashboard</h1>

    <!-- action="" rakha, file naam kuch bhi ho chalega -->
    <form method="GET" action="">
    <div class="form-container">
          <div>
        <!-- Table Dropdown -->
        <label for="table">Select Table:</label>
        <select name="table" id="table" onchange="this.form.submit()">
            <option value="">-- Select Table --</option>
            <option value="project" <?php if($selectedTable === 'project') echo 'selected'; ?>>Project</option>
            <option value="users"   <?php if($selectedTable === 'users')   echo 'selected'; ?>>Users</option>
            <option value="task"    <?php if($selectedTable === 'task')    echo 'selected'; ?>>Task</option>
        </select>
        </div>
        <!-- View Options (checkbox-style list) -->
        <div>
            <div><strong>Select View:</strong></div>
            <div class="view-options" id="view-options">
                <?php
                if ($selectedTable && isset($views[$selectedTable])) {
                    foreach ($views[$selectedTable] as $viewKey => $viewData) {
                        $checked = ($selectedView === $viewKey) ? 'checked' : '';
                        echo "<label>
                                <input type='radio' name='view' value='{$viewKey}' {$checked}>
                                ".htmlspecialchars($viewData['label'])."
                              </label>";
                    }
                } else {
                    echo "<span style='font-size:12px;'>Please select a table first.</span>";
                }
                ?>
            </div>
        </div>

        

        <!-- Project dropdown -->
        <div id="projectBlock"
             style="display: <?php echo ($selectedProjectId !== '' ? 'block' : 'none'); ?>; margin-top:5px;">
            <label for="project_id">Select Project:</label>
            <select name="project_id" id="project_id">
                <option value="">-- Select Project --</option>
                <?php foreach ($projects as $proj): ?>
                    <?php
                        $pid   = $proj['id'];
                        $pname = $proj['name'];
                        $sel   = ($selectedProjectId == $pid) ? 'selected' : '';
                    ?>
                    <option value="<?php echo htmlspecialchars($pid); ?>" <?php echo $sel; ?>>
                        <?php echo htmlspecialchars($pname); ?> (ID: <?php echo htmlspecialchars($pid); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
        <button type="submit" style="margin-top:5px;">Fetch Data</button>
        </div>
    </div>
    </form>

    <hr>

    <?php
    if ($errorMsg) {
        echo "<p style='color:red;'>{$errorMsg}</p>";
    }

    if ($tableHtml !== "") {
        echo $tableHtml;
    } else {
        if (!$errorMsg) {
            echo "<p>Please select table, view and project (for task), then click Fetch Data.</p>";
        }
    }
    ?>
</div>
</body>
</html>
