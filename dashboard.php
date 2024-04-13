<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        <?php include 'dashboard.css'; ?>
    </style>
<body>
<a id="go-back-link" href="http://localhost/filetranspiler/">&#8592 Go Back</a>
</body>
</html>

<?php

if (isset($_POST["reset"])) { // If the reset button was clicked
    unset($_POST);
}

include ("conn.php");
global $dbconn;

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

/**
 * @param $field
 * @param string $fieldSortForm
 * @return string
 */
function getFieldSortForm($field, string $fieldSortForm): string
{
    if (isset($_POST["sortBy"]) && $_POST["sortBy"] != "" && $_POST["sortBy"] == $field) {
        $fieldSortForm .= "<option selected='selected' value='" . $field . "'>" . $field . "</option>";
    } else {
        $fieldSortForm .= "<option value='" . $field . "'>" . $field . "</option>";
    }
    return $fieldSortForm;
}

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

        $fieldSortForm = "<form action='dashboard.php?table_name=$table_name' method='post'><div id='form-container'><div id='sort-select-container'><select name='sortBy'>";

        foreach ($fields as $field) {
            if (isset($_POST["filterBy"])) {
                if (in_array($field, $_POST["filterBy"])) {
                    $fieldSortForm = getFieldSortForm($field, $fieldSortForm);
                }
            } else {
                $fieldSortForm = getFieldSortForm($field, $fieldSortForm);
            }
        }

        $fieldSortForm .= <<<form
                </select>
                <input type="submit" value="Sort">
                </div>
                <div id="filter-select-container">
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
                </div>
                <input type="submit" value="Reset" name="reset">
                </div>
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
                    echo "<th>" . $field . "</th>";
                }
            } else {
                echo "<th>" . $field . "</th>";
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