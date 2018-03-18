<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$section_id = mysqli_real_escape_string($con, $_REQUEST['global_id']);
$slave_name = mysqli_real_escape_string($con, $_REQUEST['name']);
$slave_type = mysqli_real_escape_string($con, $_REQUEST['type']);

if (isset($section_id) && isset($slave_name) && isset($slave_type)) {
    $sql_sum = "SELECT * FROM sections_hierarchy WHERE id_master = '" . $section_id . "'";
    if ($result_sum = mysqli_query($con, $sql_sum)) {
        mysqli_query($con, "INSERT INTO sections(name,type) VALUES('" . $slave_name . "','" . $slave_type . "')");
        $slave_id = mysqli_insert_id($con);
        mysqli_query($con, "INSERT INTO sections_hierarchy(id_master,id_slave,slave_number) VALUES(" . $section_id . "," . $slave_id . "," . (mysqli_num_rows($result_sum)+1) . ")");
        mysqli_free_result($result);
    }
}

// close connection
mysqli_close($con);
?>