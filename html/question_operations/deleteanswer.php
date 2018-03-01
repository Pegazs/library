<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST['question_id']);
$answer_id = mysqli_real_escape_string($con, $_POST['answer_id']);
$answer_number = mysqli_real_escape_string($con, $_POST['answer_number']);
mysqli_query($con, "DELETE FROM answers WHERE id = " . $answer_id);
mysqli_query($con, "UPDATE answers SET answer_number = (answer_number - 1) WHERE question_id = " . $question_id . " AND answer_number > " . $answer_number);
?>