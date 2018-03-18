<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$master_id = mysqli_real_escape_string($con, $_REQUEST['master_id']);
$slave_id = mysqli_real_escape_string($con, $_REQUEST['slave_id']);

$sql_sum = "SELECT * FROM sections_hierarchy WHERE id_master = " . $master_id . " AND id_slave = " . $slave_id;
$result_sum = mysqli_query($con, $sql_sum);
$row = mysqli_fetch_array($result_sum);
$slave_number = $row['slave_number'];
$query1 = "UPDATE sections_hierarchy SET slave_number=(slave_number-1) WHERE slave_number > " . $slave_number;
$query2 = "DELETE FROM sections_hierarchy WHERE id_master = " . $master_id . " AND id_slave = " . $slave_id;
mysqli_query($con, $query1);
mysqli_query($con, $query2);

// close connection
mysqli_close($con);
?>