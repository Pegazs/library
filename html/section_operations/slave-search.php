<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Escape user inputs for security
$term = mysqli_real_escape_string($con, $_REQUEST['term']);
$type = mysqli_real_escape_string($con, $_REQUEST['type']);
$counter = 0;

if (isset($term)) {
    // Attempt select query execution
    $sql = "SELECT * FROM sections WHERE type = '" . $type . "' AND name LIKE '%" . $term . "%'";
    if ($result = mysqli_query($con, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            while (($row = mysqli_fetch_array($result)) && $counter < 10) {
                echo "<p>" . $row['name'] . "</p>";
                $counter++;
            }
            // Close result set
            mysqli_free_result($result);
        } else {
            echo "<a onclick='new_slave()'>Добавить новую запись</a>";
        }
    } else {
        echo "ERROR: Could not able to execute $sql. " . mysqli_error($con);
    }
}

// close connection
mysqli_close($con);
?>