<?php
session_start();
if (empty($_SESSION['usr_id'])) {
    header("Location: ../index.php");
}
include_once 'dbconnectLib.php';
include_once '../dbconnect.php';
header('X-Frame-Options: GOFORIT');
$page_id = $_POST["page_id"];
$user_id = $_POST["user_id"];
if (empty($_POST["session_id"])) {
    $session_id = null;
} else {
    $session_id = $_POST["session_id"];
}
if (empty($_POST["question_id"])) {
    $question_id = null;
} else {
    $question_id = $_POST["question_id"];
}
mysqli_query($conLib, "SET sql_mode = ''");

$query = "INSERT INTO page_clicked(page_id, user_id, session_id, question_id) VALUES (" . $page_id . "," . $user_id . "," . (!empty($session_id) ? $session_id : 'NULL') . "," . (!empty($question_id) ? $question_id : 'NULL') . ")";

mysqli_query($con, $query);

//echo mysqli_error($con);
?>