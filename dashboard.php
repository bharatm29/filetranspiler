<?php
// database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "php";

$dbconn = new mysqli($servername, $username, $password, $dbname);

if ($dbconn->connect_error) {
    die("Connection failed: " . $dbconn->connect_error);
}

$result = $dbconn->query("show tables;");

echo "Tables: <ul>";

while ($row = $result->fetch_assoc()) {
    echo "<li>".$row["Tables_in_php"] . "</li>";
}

echo "</ul>";