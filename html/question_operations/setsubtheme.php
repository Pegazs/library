<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST['question_id']);
$subtheme = mysqli_real_escape_string($con, $_POST['subtheme']);
mysqli_query($con, "UPDATE questions SET subtheme = '" . $subtheme . "' WHERE id = " . $question_id);
?>