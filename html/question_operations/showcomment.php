<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'student') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);

$qustion = mysqli_query($con, "SELECT * FROM questions WHERE id=".$question_id);
$comment = mysqli_fetch_object($qustion)->comment;
echo $comment;
?>