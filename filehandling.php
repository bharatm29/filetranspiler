<?php

if (pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION) !== "txt"){
    die("Unsupported file format");
}

$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    echo "File uploaded successfully<br>";
} else {
    echo "Could not Upload the file to server.<br>";
}