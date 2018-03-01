<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST['question_id']);
$question_type = mysqli_real_escape_string($con, $_POST['question_type']);
$answer_id = mysqli_real_escape_string($con, $_POST['answer_id']);
if ($question_type == "radiobutton") {
    mysqli_query($con, "UPDATE answers SET answer_true = 0 WHERE question_id = " . $question_id);
    mysqli_query($con, "UPDATE answers SET answer_true = 1 WHERE id = " . $answer_id);
} else {
    mysqli_query($con, "UPDATE answers SET answer_true = (!answer_true) WHERE id = " . $answer_id);
}
?>