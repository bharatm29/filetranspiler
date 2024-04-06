<html lang="en">
<body>
<form action="dashboard.php" method="post">
    <input type="submit" value="Go to Dashboard"/>
</form>
</body>
</html>

<?php
if (pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION) !== "csv") {
    die("Unsupported file format");
}

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

if (file_exists($target_file)) {
    die("File already exists<br>");
}

if ($_FILES["fileToUpload"]["size"] > 1000000) {
    die("File is too large<br>");
}

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
        $data = array_map(function ($field) {
            // Replace field names like 'Number of Crashes' with 'Number_of_Crashes'
            return preg_replace("/\s+/", "_", $field);
        }, $data);

        // FIXME: Handle long field names
        $queryFields = implode(" VARCHAR(256), ", $data) . " VARCHAR(256)";
        $fields = implode(" , ", $data);

        $createQuery = <<<sql
            CREATE TABLE $basename (
                $queryFields
            );
        sql;

        // echo $createQuery . "<br>";

        try {
            if ($dbconn->query($createQuery) !== TRUE) {
                die("Error creating table: " . $dbconn->error);
            }
        } catch (Exception $e) {
            echo "At line: ".__LINE__." | [MySQL Error]: " . $e->getMessage();
        }
    }

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $values = "";

        for ($c = 0; $c < count($data); $c++) {
            $values .= "'" . $data[$c] . "',";
        }

        $values = substr($values, 0, -1);

        // FIXME: Handle attributes values like "O'Reily!"
        $insertQuery = <<<sql
        INSERT INTO $basename ($fields) VALUES ($values);
        sql;

        // echo $insertQuery . "<br>";

        try {
            if ($dbconn->query($insertQuery) !== TRUE) {
                die("Error inserting values into table: " . $dbconn->error);
            }
        } catch (Exception $e) {
            echo "At line: ".__LINE__." | [MySQL Error]: " . $e->getMessage();
        }
    }
    fclose($handle);
}

if (!$dbconn->close()) {
    die ("Error closing database: " . $dbconn->error);
}