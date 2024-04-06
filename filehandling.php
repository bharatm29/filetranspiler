<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload and Database Insertion</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        * {
            font-family: "Poppins", sans-serif;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p {
            color: #333;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
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
    die("<p>File already uploaded.</p>");
}

if ($_FILES["fileToUpload"]["size"] > 1000000) {
    die("<p>File is too large</p>");
}

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "<p>File uploaded successfully</p>";
} else {
    echo "<p>Could not Upload the file to server.</p>";
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
            return preg_replace("/\s+/", "_", trim($field));
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
                die("<p>Error creating table: " . $dbconn->error."</p>");
            }
        } catch (Exception $e) {
            echo "<p>At line: " . __LINE__ . " | [MySQL Error]: " . $e->getMessage()."</p>";
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
                die("<p>Error inserting values into table: " . $dbconn->error."</p>");
            }
        } catch (Exception $e) {
            echo "<p>At line: " . __LINE__ . " | [MySQL Error]: " . $e->getMessage()."</p>";
        }
    }
    fclose($handle);
}

if (!$dbconn->close()) {
    die ("<p>Error closing database: " . $dbconn->error."</p>");
}