<?php
if (pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION) !== "csv") {
    die("Unsupported file format");
}

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

//if (file_exists($target_file)) {
//    die("File already exists<br>");
//}

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "File uploaded successfully<br>";
} else {
    echo "Could not Upload the file to server.<br>";
}

// base name of file to use as the table name
$basename = pathinfo($target_file, PATHINFO_FILENAME);

// database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "php";

$dbconn = new mysqli($servername, $username, $password, $dbname);

if ($dbconn->connect_error) {
    die("Connection failed: " . $dbconn->connect_error);
}

if (($handle = fopen($target_file, "r")) !== FALSE) {
    $fields = "";
    if (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Number of Crashes
        $data = array_map(function ($field) {
            return preg_replace("/\s+/", "_", $field);
        }, $data);

        $queryFields = implode(" VARCHAR(256), ", $data) . " VARCHAR(256)";
        $fields = implode(" , ", $data);

        $createQuery = <<<sql
            CREATE TABLE $basename (
                $queryFields
            );
        sql;

        echo $createQuery . "<br>";

        if ($dbconn->query($createQuery) !== TRUE) {
            die("Error creating table: " . $dbconn->error);
        }
    }

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $values = "";

        for ($c = 0; $c < count($data); $c++) {
            $values .= "'" . $data[$c] . "',";
        }
        $values = substr($values, 0, -1);

        $insertQuery = <<<sql
        INSERT INTO $basename ($fields) VALUES ($values);
        sql;
        echo $insertQuery . "<br>";

        if ($dbconn->query($insertQuery) !== TRUE) {
            die("Error inserting values into table: " . $dbconn->error);
        }
    }
    fclose($handle);
}
?>

<html lang="en">
<body>
<form action="dashboard.php" method="post">
    <input type="submit" value="Go to Dashboard"/>
</form>
</body>
</html>
