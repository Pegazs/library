<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$master_id = mysqli_real_escape_string($con, $_REQUEST['master_id']);
$slave_id = mysqli_real_escape_string($con, $_REQUEST['slave_id']);
$direction = mysqli_real_escape_string($con, $_REQUEST['direction']);

if (isset($master_id) && isset($slave_id) && isset($direction)) {

    $sql_sum = "SELECT * FROM sections_hierarchy WHERE id_master = " . $master_id;
    $result_sum = mysqli_query($con, $sql_sum);
    $found = mysqli_num_rows($result);

    $sql_current = "SELECT * FROM sections_hierarchy WHERE id_master = " . $master_id . " AND id_slave = " . $slave_id;
    $result_current = mysqli_query($con, $sql_current);
    $row_current = mysqli_fetch_array($result_current);
    $slave_number = $row_current['slave_number'];

    if ($direction == "down" && $slave_number != $found) {
        $query1 = "UPDATE sections_hierarchy SET slave_number=(slave_number-1) WHERE slave_number = " . ($slave_number+1);
        $query2 = "UPDATE sections_hierarchy SET slave_number=(slave_number+1) WHERE id_slave = " . $slave_id;
        mysqli_query($con, $query1);
        mysqli_query($con, $query2);
    } else if ($direction == "up" && $slave_number > 1) {
        $query1 = "UPDATE sections_hierarchy SET slave_number=(slave_number+1) WHERE slave_number = " . ($slave_number-1);
        $query2 = "UPDATE sections_hierarchy SET slave_number=(slave_number-1) WHERE id_slave = " . $slave_id;
        mysqli_query($con, $query1);
        mysqli_query($con, $query2);
    }

}

// close connection
mysqli_close($con);
?>