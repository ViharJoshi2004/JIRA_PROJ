<?php
// User1.php

class User
{
    
    public function display_data(PDO $conn, string $sql, array $params = []): string
    {
        try {
            $stmt = $conn->prepare($sql);

            // Bind all parameters (if any)
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                return "<p>No records found.</p>";
            }

            // Build HTML table
            $html = "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>";
            $html .= "<tr>";
            foreach (array_keys($rows[0]) as $colName) {
                $html .= "<th>" . htmlspecialchars($colName) . "</th>";
            }
            $html .= "</tr>";

            foreach ($rows as $row) {
                $html .= "<tr>";
                foreach ($row as $value) {
                    $html .= "<td>" . htmlspecialchars((string)$value) . "</td>";
                }
                $html .= "</tr>";
            }

            $html .= "</table>";
            return $html;

        } catch (PDOException $e) {
            return "<p style='color:red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
