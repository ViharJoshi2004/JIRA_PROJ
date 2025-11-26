<?php
session_start();
require 'Config.php';

// --- Require login ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// --- Task id required (id OR task_id dono chalega) ---
$task_id = null;

if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $task_id = (int) $_GET['id'];
} elseif (isset($_GET['task_id']) && ctype_digit((string)$_GET['task_id'])) {
    $task_id = (int) $_GET['task_id'];
}

if ($task_id === null) {
    echo "Invalid task id. Please open this page from Tasks list.";
    exit;
}

// === yaha se tumhara purana code: comment insert, task fetch, comments fetch, assignments fetch ===


// --- Handle new comment submit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment'] ?? '');

    if ($comment === '') {
        $message = "Please write a comment.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO task_comments (task_id, user_id, comment, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("iis", $task_id, $user_id, $comment);

        if ($stmt->execute()) {
            $message = "Comment added successfully.";
        } else {
            $message = "Failed to add comment.";
        }
        $stmt->close();
    }
}

// --- Fetch task details ---
$stmt = $conn->prepare("
    SELECT id, title, description, status, priority
    FROM task
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    die("Task not found.");
}

// --- Fetch comments ---
$comments = [];
$stmt = $conn->prepare("
    SELECT c.comment, c.created_at, u.name AS user_name
    FROM task_comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.task_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();

// --- Fetch assignment history from `issue` table ---
$assignments = [];
$stmt = $conn->prepare("
    SELECT i.user_id, i.assigned_at, u.name AS user_name
    FROM issue i
    JOIN users u ON i.user_id = u.id
    WHERE i.issue_id = ?
    ORDER BY i.assigned_at DESC
");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $assignments[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Issue & Comments</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: "Poppins", Arial, sans-serif;
            min-height: 100vh;
            background: url("img2.jpg") no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: -1;
        }

        .page-wrapper {
            width: 100%;
            max-width: 900px;
        }

        .card {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 22px 20px 18px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.24);
            color: #fff;
            margin-bottom: 14px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        .task-title {
            font-size: 20px;
            font-weight: 600;
        }
        .task-meta {
            font-size: 12px;
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            border: 1px solid rgba(255,255,255,0.6);
            margin-left: 4px;
        }

        .task-desc {
            font-size: 13px;
            line-height: 1.5;
            margin-top: 6px;
            color: #eceff1;
            white-space: pre-wrap;
        }

        .section-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .alert {
            padding: 8px 10px;
            border-radius: 8px;
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.35);
            font-size: 13px;
            margin-bottom: 10px;
        }

        .comment-form textarea {
            width: 100%;
            min-height: 70px;
            resize: vertical;
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.35);
            background: rgba(0,0,0,0.25);
            color: #fff;
            font-size: 13px;
            outline: none;
        }
        .comment-form textarea:focus {
            border-color: #42a5f5;
            box-shadow: 0 0 0 1px rgba(66,165,245,0.5);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            background: #1e88e5;
            color: #fff;
            margin-top: 6px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }
        .btn:hover {
            background: #1565c0;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(21,101,192,0.45);
        }

        .comments-list,
        .history-list {
            max-height: 260px;
            overflow-y: auto;
            padding-right: 4px;
        }
        .comment-item,
        .history-item {
            padding: 8px 8px 6px;
            border-radius: 10px;
            background: rgba(0,0,0,0.25);
            margin-bottom: 8px;
            font-size: 12px;
        }
        .comment-header,
        .history-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            color: #cfd8dc;
        }
        .comment-body {
            color: #eceff1;
            white-space: pre-wrap;
        }

        .top-actions {
            margin-bottom: 10px;
            font-size: 12px;
        }
        .top-actions a {
            color: #90caf9;
            text-decoration: none;
        }
        .top-actions a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .task-meta {
                text-align: left;
            }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <!-- Task details -->
    <div class="card">
        <div class="top-actions">
            <a href="view.php">&larr; Back to Tasks</a>
        </div>

        <div class="card-header">
            <div>
                <div class="task-title">
                    <?php echo htmlspecialchars($task['title']); ?>
                </div>
            </div>
            <div class="task-meta">
                Status:
                <span class="badge">
                    <?php echo htmlspecialchars($task['status']); ?>
                </span><br>
                Priority:
                <span class="badge">
                    <?php echo htmlspecialchars($task['priority']); ?>
                </span>
            </div>
        </div>

        <div class="task-desc">
            <?php
            echo $task['description'] !== ''
                ? nl2br(htmlspecialchars($task['description']))
                : '<em>No description provided.</em>';
            ?>
        </div>
    </div>

    <!-- Assignment history from `issue` table -->
    <div class="card">
        <div class="section-title">Assignment History</div>
        <div class="history-list">
            <?php if (empty($assignments)): ?>
                <p style="font-size:12px; color:#cfd8dc;">No assignment history available for this task.</p>
            <?php else: ?>
                <?php foreach ($assignments as $a): ?>
                    <div class="history-item">
                        <div class="history-header">
                            <span><?php echo htmlspecialchars($a['user_name']); ?></span>
                            <span><?php echo htmlspecialchars($a['assigned_at']); ?></span>
                        </div>
                        <div style="color:#eceff1; font-size:12px;">
                            Assigned to this task
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add comment -->
    <div class="card">
        <div class="section-title">Add Comment / Issue Detail</div>

        <?php if (!empty($message)): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post" class="comment-form">
            <textarea name="comment" placeholder="Describe issue, update, or comment..."></textarea>
            <button type="submit" class="btn">Post Comment</button>
        </form>
    </div>

    <!-- Previous comments -->
    <div class="card">
        <div class="section-title">Previous Comments</div>
        <div class="comments-list">
            <?php if (empty($comments)): ?>
                <p style="font-size:12px; color:#cfd8dc;">No comments yet. Be the first to add one.</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <span><?php echo htmlspecialchars($c['user_name']); ?></span>
                            <span><?php echo htmlspecialchars($c['created_at']); ?></span>
                        </div>
                        <div class="comment-body">
                            <?php echo nl2br(htmlspecialchars($c['comment'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
