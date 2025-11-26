<?php
// Config.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "jira_project";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>
<?php
// --- Your existing DB connection code above ---

// ✅ Universal date formatter function
function formatDate($dateStr) {
    if (empty($dateStr) || $dateStr === '0000-00-00') {
        return '-';
    }
    $timestamp = strtotime($dateStr);
    return date('d_m_Y', $timestamp); // 👈 changes format to DD_MM_YYYY
}
?>
