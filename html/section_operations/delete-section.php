<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$id = mysqli_real_escape_string($con, $_REQUEST['id']);
$query = "DELETE FROM sections WHERE id = " . $id;
$result = mysqli_query($con, $query);

// close connection
mysqli_close($con);
?>