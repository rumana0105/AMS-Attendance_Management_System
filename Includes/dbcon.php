<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "attendancemsystem";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(
        "<div style='padding:20px; background:#f8d7da; color:#721c24;
                    border:1px solid #f5c6cb; font-family:sans-serif;'>
            <strong>Database Error:</strong> Failed to connect to database.<br>
            <code>" . $conn->connect_error . "</code>
        </div>"
    );
}
?>
