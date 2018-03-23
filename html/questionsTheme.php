<?php
session_start();

if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}
include_once 'dbconnect.php';

//получаем id из адресной строки
if (!empty($_GET['id']) && isset($_GET['id'])) {
    $theme_id = mysqli_real_escape_string($con, $_GET['id']);
} else {
    $theme_id = mysqli_real_escape_string($con, $_POST['id']);
}

if (empty($theme_id)) {
    header("Location: index.php");
}

//данные, хранящиеся в базе
$result_theme = mysqli_query($con, "SELECT * FROM sections WHERE id=" . $theme_id . "");
if (mysqli_num_rows($result_theme) > 0) {
    $row = mysqli_fetch_array($result_theme);
    $theme_name = $row['name'];
} else {
    header("Location: index.php");
}

$result_test = mysqli_query($con, "SELECT * FROM tests WHERE theme_id='" . $theme_id . "'");
if (mysqli_num_rows($result_test) > 0) {
    $row2 = mysqli_fetch_array($result_test);
    $test_id = $row2['id'];
    header("Location: questions.php?id=".$test_id);
} else {
    mysqli_query($con, "INSERT INTO tests(name,mode,theme_id) VALUES('" . $theme_name . "','TRAINING','".$theme_id."')");
    header("Location: questions.php?id=".mysqli_insert_id($con));
}
