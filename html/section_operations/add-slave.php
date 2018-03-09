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

    $sql = "SELECT * FROM sections WHERE name = '" . $slave_name . "' AND type = '" . $slave_type . "'";
    if ($result = mysqli_query($con, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $slave_id = $row['id'];
            if ($section_id != $slave_id) {
                mysqli_query($con, "INSERT INTO sections_hierarchy(id_master,id_slave) VALUES(" . $section_id . "," . $slave_id . ")");
            }
            mysqli_free_result($result);
        }
    }
}

// close connection
mysqli_close($con);
?>