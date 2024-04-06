<style>
    <?php include 'dashboard.css'; ?>
</style>

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
    echo "At line: " . __LINE__ . " | [MySQL Error]: " . $e->getMessage();
}

echo "<p>Loaded Files:</p><ul>";

while ($row = $result->fetch_assoc()) {
    $table_name = $row["Tables_in_php"];
    echo "<li><a href='dashboard.php?table_name=$table_name" . "'>$table_name" . "</a></li>";
}

echo "</ul>";

if (isset($_GET["table_name"]) && $_GET["table_name"] != "") {
    $table_name = $_GET["table_name"];
    $selectQuery = "SELECT * FROM $table_name;";

    try {
        $result = $dbconn->query($selectQuery);
    } catch (Exception $e) {
        echo "At line: " . __LINE__ . " | [MySQL Error]: " . $e->getMessage();
    }

    echo "<table border='1'>";
    if ($result->num_rows > 0) {
        $tuples = array();
        $fields = array();

        if ($row = $result->fetch_assoc()) {
            $fields = array_keys($row);
            $tuples[] = $row;
        }

        $fieldSortForm = "<form action='dashboard.php?table_name=$table_name' method='post'><select name='sortBy'>";

        foreach ($fields as $field) {
            if (isset($_POST["sortBy"]) && $_POST["sortBy"] != "" && $_POST["sortBy"] == $field) {
                $fieldSortForm .= "<option selected='selected' value='" . $field . "'>" . $field . "</option>";
            } else {
                $fieldSortForm .= "<option value='" . $field . "'>" . $field . "</option>";
            }
        }

        $fieldSortForm .= <<<form
                </select>
                <input type="submit" value="Sort">
        form;

        $fieldSortForm .= "<form action='dashboard.php?table_name=$table_name' method='post'><select name='filterBy[]' multiple size='2'>";

        foreach ($fields as $field) {
            if (isset($_POST["filterBy"]) && in_array($field, $_POST["filterBy"])) {
                $fieldSortForm .= "<option selected='selected' value='" . $field . "'>" . $field . "</option>";
            } else {
                $fieldSortForm .= "<option value='" . $field . "'>" . $field . "</option>";
            }
        }

        $fieldSortForm .= <<<form
                </select>
                <input type="submit" value="Filter">
        </form>
        form;

        echo $fieldSortForm;

        while ($row = $result->fetch_assoc()) {
            $tuples[] = $row;
        }

        if (isset($_POST["sortBy"]) && $_POST["sortBy"] != "") {
            usort($tuples, function ($a, $b) {
                $sortByField = $_POST["sortBy"];
                return $a[$sortByField] <=> $b[$sortByField];
            });
        }

        echo "<tr>";
        foreach ($fields as $field) {
            if (isset($_POST["filterBy"])) {
                if (in_array($field, $_POST["filterBy"])) {
                    echo "<th>" . $field. "</th>";
                }
            } else {
                echo "<th>" . $field. "</th>";
            }
        }
        echo "</tr>";

        foreach ($tuples as $tuple) {
            if (isset($_POST["filterBy"])) {
                $tuple = array_filter($tuple, function ($key) {
                    return in_array($key, $_POST["filterBy"]);
                }, ARRAY_FILTER_USE_KEY);
            }
            echo "<tr>";
            foreach ($tuple as $_ => $value) {
                echo "<td>" . $value . "</td>";
            }
            echo "</tr>";
        }
    }

    echo "</table>";
}