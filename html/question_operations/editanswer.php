<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_type = mysqli_real_escape_string($con, $_POST['question_type']);
$answer_id = mysqli_real_escape_string($con, $_POST['answer_id']);
if ($question_type == "order") {
    $answer_text = mysqli_real_escape_string($con, $_POST['answer_text']);
    $answer_text2 = mysqli_real_escape_string($con, $_POST['answer_text2']);
    if ($answer_text != "" && $answer_text2 != "") {
        mysqli_query($con, "UPDATE answers SET answer_text = '" . $answer_text . "', answer_text2 = '" . $answer_text2 . "' WHERE id = " . $answer_id);
    }
} else {
    $answer_text = mysqli_real_escape_string($con, $_POST['answer_text']);
    if ($answer_text != "") {
        mysqli_query($con, "UPDATE answers SET answer_text = '" . $answer_text . "' WHERE id = " . $answer_id);
    }
}
?>