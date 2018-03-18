<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$section_id = mysqli_real_escape_string($con, $_REQUEST['section_id']);
$page = mysqli_real_escape_string($con, $_REQUEST['page']);
$start_end = mysqli_real_escape_string($con, $_REQUEST['start_end']);

if (isset($section_id) && isset($page) && isset($start_end)) {

    if ($start_end == "start") {
        if (strlen($page) > 0) {
            $query = "UPDATE sections_books SET start_page = " . $page . " WHERE section_id = " . $section_id;
        } else {
            $query = "UPDATE sections_books SET start_page = NULL WHERE section_id = " . $section_id;
        }
        mysqli_query($con, $query);
    } else if ($start_end == "end") {
        if (strlen($page) > 0) {
            $query = "UPDATE sections_books SET end_page = " . $page . " WHERE section_id = " . $section_id;
        } else {
            $query = "UPDATE sections_books SET end_page = NULL WHERE section_id = " . $section_id;
        }
        mysqli_query($con, $query);
    }

}
// close connection
mysqli_close($con);
?>