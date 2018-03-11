<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$id = mysqli_real_escape_string($con, $_REQUEST['id']);
$name = mysqli_real_escape_string($con, $_REQUEST['name']);
$query = "UPDATE sections SET name = '" . $name . "' WHERE id = " . $id;
$result = mysqli_query($con, $query);

// close connection
mysqli_close($con);
?>