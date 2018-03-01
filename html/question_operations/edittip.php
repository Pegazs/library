<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$tip_id = mysqli_real_escape_string($con, $_POST['tip_id']);

$tip_text = mysqli_real_escape_string($con, $_POST['tip_text']);
if ($tip_text != "") {
    mysqli_query($con, "UPDATE tips SET tip_text = '" . $tip_text . "' WHERE id = " . $tip_id);
}
?>