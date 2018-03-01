<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$oldquestion_text = mysqli_real_escape_string($con, $_POST["question_text"]);
$oldquestion_comment = mysqli_real_escape_string($con, $_POST["comment_text"]);
$oldquestion_id = mysqli_real_escape_string($con, $_POST["question_id"]);
if ($oldquestion_comment == "") {
    mysqli_query($con, "UPDATE questions SET text = '" . $oldquestion_text . "', comment = NULL WHERE id = '" . $oldquestion_id . "'");
} else {
    mysqli_query($con, "UPDATE questions SET text = '" . $oldquestion_text . "', comment = '" . $oldquestion_comment . "' WHERE id = '" . $oldquestion_id . "'");
}
?>