<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);
mysqli_query($con, "DELETE FROM questions WHERE id = '".$question_id."'");

?>