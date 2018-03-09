<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$section_id = mysqli_real_escape_string($con, $_REQUEST['section_id']);
$book_name = mysqli_real_escape_string($con, $_REQUEST['name']);

if (isset($book_name) && isset($section_id)) {

    $sql = "SELECT * FROM books WHERE name = '" . $book_name . "'";
    if ($result = mysqli_query($con, $sql)) {
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            $book_id = $row['id'];
            mysqli_query($con, "INSERT INTO sections_books(section_id,book_id) VALUES('" . $section_id . "','" . $book_id . "')");
            mysqli_free_result($result);
        }
    }
}

// close connection
mysqli_close($con);
?>