<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'student') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);
$session_id = mysqli_real_escape_string($con, $_POST["session_id"]);
$plus_tip = mysqli_real_escape_string($con, $_POST["plus_tip"]);

if ($plus_tip == "1") {
    mysqli_query($con, "UPDATE questions_session SET tips_used = tips_used+1 WHERE question_id = ".$question_id." AND session_id =".$session_id);
}

$questions_session = mysqli_query($con, "SELECT * FROM questions_session WHERE question_id = ".$question_id." AND session_id =".$session_id);
$tips_used = mysqli_fetch_object($questions_session)->tips_used;

$result_select = mysqli_query($con, "SELECT * FROM tips WHERE question_id = ".$question_id." ORDER BY tip_number");

$counter = 0;

if (($result_select->num_rows) - $tips_used > 0) {
    echo "<a name = 'newtip' onclick = \"newtip('$question_id')\" class='btn btn-primary'>Получить новую подсказку</a>";
}

if ($result_select->num_rows > 0) {
    while(($tips_list = mysqli_fetch_object($result_select)) && ($counter < $tips_used))
    {
        echo "<br><br><b>Подсказка №$tips_list->tip_number</b><br>";
        $comment = $tips_list->tip_text;
        echo $comment;
        $counter = $counter + 1;
    }
}



?>