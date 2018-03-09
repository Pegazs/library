<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$section_id = mysqli_real_escape_string($con, $_REQUEST['section_id']);
$book_id = mysqli_real_escape_string($con, $_REQUEST['book_id']);
$query = "DELETE FROM sections_books WHERE section_id = " . $section_id . " AND book_id = " . $book_id;
$result = mysqli_query($con, $query);

// close connection
mysqli_close($con);
?>