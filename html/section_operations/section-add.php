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
    mysqli_query($con, "INSERT INTO sections(name,type) VALUES('" . $term . "','" . $type . "')");
    $section_id = mysqli_insert_id($con);
    echo $section_id;
}

// close connection
mysqli_close($con);
?>