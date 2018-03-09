<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$master_id = mysqli_real_escape_string($con, $_REQUEST['master_id']);
$slave_id = mysqli_real_escape_string($con, $_REQUEST['slave_id']);
$query = "DELETE FROM sections_hierarchy WHERE id_master = " . $master_id . " AND id_slave = " . $slave_id;
$result = mysqli_query($con, $query);

// close connection
mysqli_close($con);
?>