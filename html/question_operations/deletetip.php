<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST['question_id']);
$tip_id = mysqli_real_escape_string($con, $_POST['tip_id']);
$tip_number = mysqli_real_escape_string($con, $_POST['tip_number']);
mysqli_query($con, "DELETE FROM tips WHERE id = " . $tip_id);
mysqli_query($con, "UPDATE tips SET tip_number = (tip_number - 1) WHERE question_id = " . $question_id . " AND tip_number > " . $tip_number);
?>