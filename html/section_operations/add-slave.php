<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$section_id = mysqli_real_escape_string($con, $_REQUEST['global_id']);
$slave_name = mysqli_real_escape_string($con, $_REQUEST['name']);
$slave_type = mysqli_real_escape_string($con, $_REQUEST['type']);

if ($type == "Дисциплина") {
    $slave_type = "discipline";
} else if ($type == "Раздел") {
    $slave_type = "supersection";
} else if ($type == "Подаздел") {
    $slave_type = "section";
} else if ($type == "Тема") {
    $slave_type = "theme";
}

if (isset($section_id) && isset($slave_name) && isset($slave_type)) {

    $sql = "SELECT * FROM sections WHERE name = '" . $slave_name . "' AND type = '" . $slave_type . "'";
    $sql_sum = "SELECT * FROM sections_hierarchy WHERE id_master = " . $section_id;
    if (($result = mysqli_query($con, $sql)) && ($result_sum = mysqli_query($con, $sql_sum))) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $slave_id = $row['id'];
            if ($section_id != $slave_id) {
                $slave_number = mysqli_num_rows($result_sum)+1;

                if ($slave_type = "section") {
                    mysqli_query($con, "DELETE FROM sections_hierarchy WHERE id_slave='" . $slave_id . "' AND (id_master='" . $section_id . "' OR id_master IN (SELECT id_master from (SELECT * from sections_hierarchy) b where id_slave='" . $section_id . "') OR id_master IN (SELECT id_slave from (SELECT * from sections_hierarchy) b where id_master='" . $section_id . "'))");
                }

                mysqli_query($con, "INSERT INTO sections_hierarchy(id_master,id_slave,slave_number) VALUES('" . $section_id . "','" . $slave_id . "','" . $slave_number . "')");
            }
            mysqli_free_result($result);
        }
    }
}

// close connection
mysqli_close($con);
?>