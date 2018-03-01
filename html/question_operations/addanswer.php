<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);
$question_type = mysqli_real_escape_string($con, $_POST['question_type']);
$new_answer_number = ((mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $question_id))->num_rows) + 1;

if ($question_type == "order") {
    $answer_text = mysqli_real_escape_string($con, $_POST['answer_text']);
    $answer_text2 = mysqli_real_escape_string($con, $_POST['answer_text2']);
    if ($answer_text != "" && $answer_text2 != "") {
        mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_text2,answer_true) VALUES(" . $question_id . "," . $new_answer_number . ",'" . $answer_text . "','" . $answer_text2 . "',0)");
    }
} else {
    $answer_text = mysqli_real_escape_string($con, $_POST['answer_text']);
    if ($answer_text != "") {
        mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_true) VALUES(" . $question_id . "," . $new_answer_number . ",'" . $answer_text . "',0)");
    }
}
?>