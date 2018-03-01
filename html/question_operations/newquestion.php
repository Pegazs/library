<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_text = mysqli_real_escape_string($con, $_POST['question_text']);
$question_comment = mysqli_real_escape_string($con, $_POST['comment_text']);
$question_type = mysqli_real_escape_string($con, $_POST['question_type']);
$test_id = mysqli_real_escape_string($con, $_POST['id']);
if ($question_text == "") {
    $error = true;
    $errormsg = "Вопрос не может быть пустым";
}

if ($question_comment == "") {
    if(mysqli_query($con, "INSERT INTO questions(test_id,type,text) VALUES(" . $test_id . ",'" . $question_type . "','" . $question_text . "')"))
    {
        $successmsg = "Вопрос добавлен";
    } else {
        $errormsg = "Ошибка добавления вопроса";
    }
} else {
    if(mysqli_query($con, "INSERT INTO questions(test_id,type,text,comment) VALUES(" . $test_id . ",'" . $question_type . "','" . $question_text . "','" . $question_comment . "')"))
    {
        $successmsg = "Вопрос добавлен";
    } else {
        $errormsg = "Ошибка добавления вопроса";
    }
}
$question_id = mysqli_insert_id($con);
echo $question_id;
if ($question_type == "order") {
    mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_text2,answer_true) VALUES(" . $question_id . ",1,'Текст ответа 1','Текст ответа 2',1)");
} else {
    mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_true) VALUES(" . $question_id . ",1,'Текст ответа',1)");
}


?>