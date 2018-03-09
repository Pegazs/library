<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Escape user inputs for security
$term = mysqli_real_escape_string($con, $_REQUEST['term']);
$type = mysqli_real_escape_string($con, $_REQUEST['type']);

if (isset($term)) {
    // Attempt select query execution
    $sql = "SELECT * FROM sections WHERE type = '" . $type . "' AND name = '" . $term . "'";
    if ($result = mysqli_query($con, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            echo $row['id'];
            mysqli_free_result($result);
        }
    }
}

// close connection
mysqli_close($con);
?>