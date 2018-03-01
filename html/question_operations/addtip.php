<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);
$new_tip_number = ((mysqli_query($con, "SELECT * FROM tips WHERE question_id = " . $question_id))->num_rows) + 1;


$tip_text = mysqli_real_escape_string($con, $_POST['tip_text']);
if ($tip_text != "") {
    mysqli_query($con, "INSERT INTO tips(question_id,tip_number,tip_text) VALUES(" . $question_id . "," . $new_tip_number . ",'" . $tip_text . "')");
}

?>