<?php
include_once '../dbconnect.php';
session_start();
// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Escape user inputs for security
$id = mysqli_real_escape_string($con, $_REQUEST['id']);
$type = mysqli_real_escape_string($con, $_REQUEST['type']);

if (isset($id)) {
    if ($result_select = mysqli_query($con, "SELECT * FROM sessions WHERE test_id = " . $id . " AND user_id = " . $_SESSION['usr_id'] . " AND status = 'started'")) {
        if (mysqli_num_rows($result_select) == 0) {
            $result_select = mysqli_query($con, "SELECT * FROM questions WHERE test_id = " . $id . " ORDER BY RAND()");
            mysqli_query($con, "INSERT INTO sessions(test_id, user_id) VALUES (" . $id . "," . $_SESSION['usr_id'] . ")");
            $session_id = mysqli_insert_id($con);
            for ($i = 1; $question = mysqli_fetch_object($result_select); $i++) {
                mysqli_query($con, "INSERT INTO questions_session(session_id, question_id, order_num) VALUES (" . $session_id . "," . ($question->id) . "," . $i . ")");
            }
        } else {
            $session_id = mysqli_fetch_object($result_select)->id;
        }
       echo $session_id;
    }

}

// close connection
mysqli_close($con);
?>