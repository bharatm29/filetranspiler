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

try {
    $result = $dbconn->query("show tables;");
} catch (Exception $e) {
    echo "At line: ".__LINE__." | [MySQL Error]: " . $e->getMessage();
}

echo "Tables: <ul>";

while ($row = $result->fetch_assoc()) {
    $table_name = $row["Tables_in_php"];
    echo "<li><a href='dashboard.php?table_name=$table_name" . "'>$table_name" . "</a></li>";
}

echo "</ul > ";

if (isset($_GET["table_name"]) && $_GET["table_name"] != "") {
    $table_name = $_GET["table_name"];
    $selectQuery = "SELECT * FROM $table_name;";

    try {
        $result = $dbconn->query($selectQuery);
    } catch (Exception $e) {
        echo "At line: ".__LINE__." | [MySQL Error]: " . $e->getMessage();
    }

    echo "<table border='1'>";
    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $_) {
                echo "<th>" . $key . "</th>";
            }
            echo "</tr><tr>";
            foreach ($row as $_ => $value) {
                echo "<td>" . $value . "</td>";
            }
            echo "</tr>";
        }

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $_ => $value) {
                echo "<td>" . $value . "</td>";
            }
            echo "</tr>";
        }
    }

    echo "</table>";
}