<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Escape user inputs for security
if (!empty($_REQUEST['term']) && isset($_REQUEST['term'])) {
    $term = mysqli_real_escape_string($con, $_REQUEST['term']);
}
if (!empty($_REQUEST['type']) && isset($_REQUEST['type'])) {
    $type = mysqli_real_escape_string($con, $_REQUEST['type']);
}
if (!empty($_REQUEST['id']) && isset($_REQUEST['id'])) {
    $id = mysqli_real_escape_string($con, $_REQUEST['id']);
}

if (isset($term) && isset($type)) {
    $sql = "SELECT * FROM sections WHERE type = '" . $type . "' AND name = '" . $term . "'";
    if ($result = mysqli_query($con, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            echo $row['id'];
            mysqli_free_result($result);
        }
    }
} else if (isset($id)) {
    $sql = "SELECT * FROM sections WHERE id = '" . $id . "'";
    if ($result = mysqli_query($con, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            echo $row['name'];
            echo "|";
            echo $row['type'];
            mysqli_free_result($result);
        }
    }
}

// close connection
mysqli_close($con);
?>