<?php
// view.php
session_start();
require 'Config.php'; // must create $conn (mysqli)

// --- Require login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// friendly variables
$dbError      = '';
$projects     = [];
$createdMsg   = '';
$singleProject = null;
$projectTasks  = [];
$singleTask    = null;
$mode          = 'list'; // list | project | task

// Optional: message after creating project
if (isset($_GET['created']) && $_GET['created'] == 1 && isset($_GET['id'])) {
    $createdMsg = "Project created successfully (ID: " . (int)$_GET['id'] . ").";
}

/*
 * Decide mode: list, project, or task
 * priority: task view > project view > list
 */
if (isset($_GET['task_id']) && is_numeric($_GET['task_id'])) {
    $mode = 'task';
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $mode = 'project';
} else {
    $mode = 'list';
}

// --- Fetch projects list (always, for list view) ---
$sql = "SELECT id, key_code, name, description, client, start_date, end_date, Created_at, Updated_at 
        FROM project 
        ORDER BY Created_at DESC";
$res = $conn->query($sql);

if ($res === false) {
    $dbError = "DB error: " . htmlspecialchars($conn->error);
} else {
    $projects = $res->fetch_all(MYSQLI_ASSOC) ?: [];
}

/* -------------------------
   PROJECT DETAIL + TASKS
--------------------------*/
if ($mode === 'project') {
    $pid = (int) $_GET['id'];

    // Fetch single project
    $stmt = $conn->prepare("SELECT id, key_code, name, description, client, start_date, end_date, Created_at, Updated_at 
                            FROM project 
                            WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $pid);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $singleProject = $result->fetch_assoc() ?: null;
        } else {
            $dbError = "DB error (project fetch): " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $dbError = "DB error (prepare project): " . htmlspecialchars($conn->error);
    }

    // Fetch tasks for that project
    if ($singleProject !== null) {
        // Adjust columns if your `task` table has different names
        $tstmt = $conn->prepare("SELECT id, title, description, status, created_at 
                                 FROM task 
                                 WHERE pro_id = ? 
                                 ORDER BY created_at DESC");
        if ($tstmt) {
            $tstmt->bind_param('i', $pid);
            if ($tstmt->execute()) {
                $tres = $tstmt->get_result();
                $projectTasks = $tres->fetch_all(MYSQLI_ASSOC) ?: [];
            } else {
                $dbError = "DB error (tasks fetch): " . htmlspecialchars($tstmt->error);
            }
            $tstmt->close();
        } else {
            $dbError = "DB error (prepare tasks): " . htmlspecialchars($conn->error);
        }
    }
}

/* -------------------------
   SINGLE TASK DETAIL
--------------------------*/
if ($mode === 'task') {
    $tid = (int) $_GET['task_id'];

    // Join project to show project name / key
    $tstmt = $conn->prepare("
        SELECT 
            t.id,
            t.title,
            t.description,
            t.status,
            t.created_at,
            t.pro_id,
            p.name AS project_name,
            p.key_code AS project_key
        FROM task t
        LEFT JOIN project p ON t.pro_id = p.id
        WHERE t.id = ?
        LIMIT 1
    ");

    if ($tstmt) {
        $tstmt->bind_param('i', $tid);
        if ($tstmt->execute()) {
            $tres = $tstmt->get_result();
            $singleTask = $tres->fetch_assoc() ?: null;
        } else {
            $dbError = "DB error (task fetch): " . htmlspecialchars($tstmt->error);
        }
        $tstmt->close();
    } else {
        $dbError = "DB error (prepare single task): " . htmlspecialchars($conn->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Projects & Tasks</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* lightweight override so container is readable over your blurred background */
        .wrap {
            max-width:980px;
            margin:28px auto;
            padding:18px;
            background: rgba(255,255,255,0.96);
            border-radius:8px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }
        table {
            width:100%;
            border-collapse:collapse;
            background: #fff;
            border-radius:6px;
            overflow:hidden;
        }
        th, td {
            padding:10px 12px;
            border-bottom:1px solid #f1f3f5;
            text-align:left;
        }
        thead th {
            background:#f7fafc;
            font-weight:700;
        }
        tr:hover td {
            background:#fcfeff;
        }
        .meta {
            font-size:13px;
            color:#5b6b73;
            margin-bottom:8px;
        }
        .actions {
            margin-bottom:12px;
        }
        .note {
            background:#e6fffa;
            color:#064e3b;
            padding:10px;
            border-radius:6px;
            margin-bottom:12px;
        }
        .error {
            background:#fff3f2;
            color:#7f1d1d;
            padding:10px;
            border-radius:6px;
            margin-bottom:12px;
        }
        a.btn {
            display:inline-block;
            padding:8px 12px;
            background:#00b4d8;
            color:#fff;
            border-radius:6px;
            text-decoration:none;
            margin-right:8px;
        }
        .small {
            font-size:13px;
            color:#555;
        }
        .muted {
            color:#666;
            font-size:13px;
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Projects</h1>
    <p class="meta">
        Logged in as <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
        — <a href="dashbord.php">Dashboard</a>
    </p>

    <div class="actions">
        <a class="btn" href="project.php">+ Create Project</a>
        <a class="btn" href="task.php" style="background:#0096c7">+ Create Task</a>
        <a class="btn" href="role.php" style="background:#fca311">Manage Roles</a>
        <a class="btn" href="view.php" style="background:#8e44ad">View Tasks</a>
         <a class="btn" href="membership.php" style="background:#2a9d8f">Manage Users</a>
         <a class="btn" href="report.php" style="background:#f77f00">View Reports</a>
        <a class="btn" href="logout.php" style="background:#e63946">Logout</a>
    </div>

    <?php if (!empty($createdMsg)): ?>
        <div class="note"><?php echo htmlspecialchars($createdMsg); ?></div>
    <?php endif; ?>

    <?php if (!empty($dbError)): ?>
        <div class="error"><?php echo $dbError; ?></div>
    <?php endif; ?>

    <?php if ($mode === 'list'): ?>

        <!-- Projects list -->
        <?php if (empty($projects)): ?>
            <div style="padding:18px;background:#fff;border-radius:6px">
                No projects found.
                <a href="project.php">Create your first project</a>.
            </div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Key</th>
                    <th>Name</th>
                    <th>Client</th>
                    <th>Start - End</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $p): ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['key_code']); ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['client']); ?></td>
                        <td>
                            <?php echo $p['start_date'] ? htmlspecialchars($p['start_date']) : '-'; ?>
                            —
                            <?php echo $p['end_date'] ? htmlspecialchars($p['end_date']) : '-'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($p['Created_at']); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo (int)$p['id']; ?>">View</a> |
                            <a href="task.php?pro_id=<?php echo (int)$p['id']; ?>">Create Task</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php elseif ($mode === 'project' && $singleProject !== null): ?>

        <!-- Single project detail + list its tasks -->
        <h2>
            <?php echo htmlspecialchars($singleProject['name']); ?>
            <span class="muted">(#<?php echo (int)$singleProject['id']; ?>)</span>
        </h2>
        <p class="small">
            <strong>Key:</strong> <?php echo htmlspecialchars($singleProject['key_code'] ?? '-'); ?>
            —
            <strong>Client:</strong> <?php echo htmlspecialchars($singleProject['client'] ?? '-'); ?>
        </p>
        <p><?php echo nl2br(htmlspecialchars($singleProject['description'] ?? 'No description')); ?></p>
        <p class="small">
            <strong>Start:</strong> <?php echo htmlspecialchars($singleProject['start_date'] ?? '-'); ?>
            &nbsp;
            <strong>End:</strong> <?php echo htmlspecialchars($singleProject['end_date'] ?? '-'); ?>
        </p>

        <div style="margin-top:18px;">
            <a class="btn" href="task.php?pro_id=<?php echo (int)$singleProject['id']; ?>">+ Create Task for this project</a>
            <a class="btn" href="view.php" style="background:#666">← Back to projects</a>
        </div>

        <h3 style="margin-top:18px;">Tasks for this project</h3>

        <?php if (empty($projectTasks)): ?>
            <div style="padding:12px;background:#fff;border-radius:6px">
                No tasks were found for this project.
                <a href="task.php?pro_id=<?php echo (int)$singleProject['id']; ?>">Create one</a>.
            </div>
        <?php else: ?>
            <table style="margin-top:10px;">
                <thead>
                <tr>
                    <th>id</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projectTasks as $t): ?>
    <tr>
        <td><?php echo (int)$t['id']; ?></td>
        <td><?php echo htmlspecialchars($t['title'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($t['status'] ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($t['created_at'] ?? '—'); ?></td>
        <td>
            <a href="view.php?task_id=<?php echo (int)$t['id']; ?>">View</a> |
            <a href="task.php?id=<?php echo (int)$t['id']; ?>">Edit</a> |
            <!-- 👇 yaha se sahi task id issue page ko milegi -->
            <a href="task_issue.php?id=<?php echo (int)$t['id']; ?>">Issues</a>
        </td>
    </tr>
<?php endforeach; ?>

                </tbody>
            </table>
        <?php endif; ?>

    <?php elseif ($mode === 'task' && $singleTask !== null): ?>

        <!-- Single task detail view -->
        <h2>
            Task: <?php echo htmlspecialchars($singleTask['title'] ?? 'Untitled'); ?>
            <span class="muted">(#<?php echo (int)$singleTask['id']; ?>)</span>
        </h2>

        <p class="small">
            <strong>Project:</strong>
            <?php if (!empty($singleTask['project_name'])): ?>
                <?php echo htmlspecialchars($singleTask['project_name']); ?>
                (<?php echo htmlspecialchars($singleTask['project_key'] ?? ''); ?>)
            <?php else: ?>
                -
            <?php endif; ?>
        </p>

        <p class="small">
            <strong>Status:</strong> <?php echo htmlspecialchars($singleTask['status'] ?? '-'); ?>
            &nbsp; | &nbsp;
            <strong>Created:</strong> <?php echo htmlspecialchars($singleTask['created_at'] ?? '-'); ?>
        </p>

        <h3>Description</h3>
        <div style="padding:12px;background:#fff;border-radius:6px;white-space:pre-wrap;">
            <?php echo nl2br(htmlspecialchars($singleTask['description'] ?? 'No description')); ?>
        </div>

        <div style="margin-top:18px;">
            <?php if (!empty($singleTask['pro_id'])): ?>
                <a class="btn" href="view.php?id=<?php echo (int)$singleTask['pro_id']; ?>">← Back to project</a>
            <?php else: ?>
                <a class="btn" href="view.php" style="background:#666">← Back to projects</a>
            <?php endif; ?>
            <a class="btn" href="task.php?id=<?php echo (int)$singleTask['id']; ?>" style="background:#0096c7">
                Edit Task
            </a>
        </div>

    <?php else: ?>

        <!-- Fallback if invalid id given -->
        <div style="padding:18px;background:#fff;border-radius:6px">
            Invalid ID given. <a href="view.php">Back to projects list</a>.
        </div>

    <?php endif; ?>

</div>
</body>
</html>
