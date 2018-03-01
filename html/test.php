<?php
session_start();
include_once 'dbconnect.php';
if (empty($_SESSION['usr_id'])) {
    header("Location: index.php");
}
//получаем id из адресной строки
if (!empty($_GET['id']) && isset($_GET['id'])) {
    $session_id = mysqli_real_escape_string($con, $_GET['id']);
} else {
    $session_id = mysqli_real_escape_string($con, $_POST['id']);
}

if (empty($session_id)) {
    header("Location: index.php");
}

$result_select = mysqli_query($con, "SELECT * FROM sessions WHERE id = " . $session_id . " AND status = 'started'");

if (mysqli_num_rows($result_select) == 0) {
    header("Location: index.php");
}

$session_info = mysqli_fetch_object($result_select);

if (($session_info->user_id) != $_SESSION['usr_id']) {
    header("Location: index.php");
}

$date = new DateTime();
$test_info = mysqli_fetch_object(mysqli_query($con, "SELECT * FROM tests WHERE id =" . ($session_info->test_id)));
if ($test_info->minutes > 0) {
    $time_left = strtotime($session_info->start_time) + (60 * $test_info->minutes) - ($date->getTimestamp());
} else {
    $time_left = 1000;
}
//$user_info = mysqli_fetch_object(mysqli_query($con, "SELECT * FROM users WHERE id =" . ($session_info->user_id)));
$error = false;
$error_msg = "";


if (isset($_POST['submitAnswer'])) {
    $question_num = mysqli_real_escape_string($con, $_POST['questionnum']);
    $question_type = mysqli_real_escape_string($con, $_POST['questiontype']);
    $question_id = mysqli_real_escape_string($con, $_POST['questionid']);
    switch ($question_type) {
        case "radiobutton":
            $answers_select = mysqli_query($con, "SELECT * from answers WHERE answer_true = true AND question_id = " . $question_id);
            $answers = mysqli_fetch_object($answers_select);
            $answer = $_POST['answer'];
            if ($answer == ($answers->answer_number)) {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = true, answer = '" . $answer . ";' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            } else {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = false, answer = '" . $answer . ";' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            }
            break;
        case "checkbox":
            $answers_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . $question_id);
            $answer = "";
            $allcorrect = true;
            while ($answers = mysqli_fetch_object($answers_select)) {
                $answer_num = $answers->answer_number;
                if (isset($_POST['checkbox' . $answer_num])) {
                    $isset = 1;
                } else {
                    $isset = 0;
                }
                if ($isset == 1) {
                    $plus = $answer_num . "; ";
                    $answer .= $plus;
                }
                if ($isset != ($answers->answer_true)) {
                    $allcorrect = false;
                }
            }
            if ($allcorrect) {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = true, answer = '" . $answer . "' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            } else {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = 0, answer = '" . $answer . "' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            }
            break;
        case "input":
            $answers_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . $question_id);
            $answers = mysqli_fetch_object($answers_select);
            $input_answer = mysqli_real_escape_string($con, $_POST['input_answer']);
            if (strcmp(mb_strtolower($input_answer), mb_strtolower($answers->answer_text)) == 0) {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = true, answer = '" . $input_answer . "' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            } else {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = false, answer = '" . $input_answer . "' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            }
            break;
        case "order":
            $answers_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . $question_id);
            $answer = "";
            $allcorrect = true;
            while ($answers = mysqli_fetch_object($answers_select)) {
                $answer_num = $answers->answer_number;
                $user_answer = $_POST['order' . md5(($answers->answer_number) * $session_id)];
                $answers2_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . $question_id . " AND answer_number = " . $user_answer);
                $answers2 = mysqli_fetch_object($answers2_select);
                $user_answer_text = ($answers2->answer_text2);
                $plus = "<br>" . ($answers->answer_text) . " = " . $user_answer_text . ";";
                $answer .= $plus;
                if ($user_answer != $answer_num) {
                    $allcorrect = false;
                }
            }
            $answer = substr($answer, 0, 2000);
            if ($allcorrect) {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = true, answer = '" . $answer . "' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            } else {
                mysqli_query($con, "UPDATE questions_session SET answered = true, correct = false, answer = '" . $answer . "' WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
            }
            break;
    }
}

if (isset($_POST['skipAnswer'])) {
    $question_num = mysqli_real_escape_string($con, $_POST['questionnum']);
    mysqli_query($con, "UPDATE questions_session SET skipped = true WHERE order_num = " . $question_num . " AND session_id = " . $session_id);
}


if (isset($_POST['surrender']) || $time_left <= 0) {
    $correct = mysqli_num_rows(mysqli_query($con, "SELECT * FROM questions_session WHERE correct = true AND session_id=" . $session_id));
    mysqli_query($con, "UPDATE sessions SET status = 'finished', finish_time = CURRENT_TIMESTAMP, result = " . $correct . " WHERE id = " . $session_id);
    header("Location: result.php?id=" . $session_id);
}

$isFinished = mysqli_query($con, "SELECT * FROM questions_session WHERE answered = false AND skipped = false AND session_id=" . $session_id);
if (mysqli_num_rows($isFinished) == 0) {
    $isSkipped = mysqli_query($con, "SELECT * FROM questions_session WHERE skipped = true AND session_id=" . $session_id);
    if (mysqli_num_rows($isSkipped) == 0) {
        $correct = mysqli_num_rows(mysqli_query($con, "SELECT * FROM questions_session WHERE correct = true AND session_id=" . $session_id));
        mysqli_query($con, "UPDATE sessions SET status = 'finished', finish_time = CURRENT_TIMESTAMP, result = " . $correct . " WHERE id = " . $session_id);
        header("Location: result.php?id=" . $session_id);
    } else {
        mysqli_query($con, "UPDATE questions_session SET skipped = false WHERE session_id = " . $session_id);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Тест «<?php echo($test_info->name) ?>»
        — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>

    <style type="text/css">
        <!--

        .hide {
            display: none;
        }

        .show {
            display: block;
        }

        -->
    </style>

    <script type="text/javascript" language="javascript">
        // Hide from browsers without javascript

        window.onload = function ()  //executes when the page finishes loading
        {
            setTimeout(func1, 100);  //sets a timer which calls function func1 after 1,000 milliseconds = 1 sec.

        };

        function func1() {
            document.getElementById("questionForm").className = "show";
        }

        // End hiding -->

        function showcomment(q_id) {
            $.ajax({
                type: "post",
                url: "question_operations/showtips.php",
                "data": {
                    "plus_tip": "0",
                    "question_id": q_id,
                    "session_id": <?php echo $session_id; ?>
                },
                success: function (data) {
                    $("#comment").html(data);
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub, 'comment']);
                    $('#popupcomment').w2popup();
                }
            });
        }

        function newtip(q_id) {
            $.ajax({
                type: "post",
                url: "question_operations/showtips.php",
                "data": {
                    "plus_tip": "1",
                    "question_id": q_id,
                    "session_id": <?php echo $session_id; ?>
                },
                success: function (data) {
                    $("#comment").html(data);
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub, 'comment']);
                }
            });
        }

        function showlibrary(q_id) {
            $.ajax({
                type: "post",
                url: "library/filter.php",
                "data": {
                    "question_id": q_id
                },
                success: function (data) {
                    $("#library").html(data);
                    MathJax.Hub.Queue(["Typeset", MathJax.Hub, 'library']);
                    $('#popuplibrary').w2popup();
                }
            });
        }
    </script>

</head>
<body>

<nav class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar1">
                <span class="sr-only">Навигация</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
        </div>
        <div class="collapse navbar-collapse" id="navbar1">
            <ul class="nav navbar-nav navbar-right">
                <?php if (isset($_SESSION['usr_id'])) { ?>
                    <li><p class="navbar-text">Вы вошли как <?php echo $_SESSION['usr_name']; ?></p></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<div id="questionForm" class="hide">
    <div class="container">
        <div class="row">
            <div class="col-md-12 well">
                <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="usersform">
                    <fieldset>
                        <legend>Тест «<?php echo($test_info->name) ?>»</legend>
                        <?php

                        if (!$result_select = mysqli_query($con, "SELECT `sessions`.*, `questions_session`.*, `questions`.* FROM `questions` LEFT JOIN `questions_session` ON `questions_session`.`question_id` = `questions`.`id` LEFT JOIN `sessions` ON `questions_session`.`session_id` = `sessions`.`id` WHERE session_id = " . $session_id . " AND answered = 0 AND skipped = 0 ORDER BY order_num")) {
                            echo "Ошибка запроса, попробуйте ещё раз.";
                        } else {
                            $questions = mysqli_fetch_object($result_select);
                            if ($test_info->mode == "TRAINING") {
                                ?>
                                <div style="text-align: center;" class="form-group">
                                    <b>Помощь:</b> <a name="newquestion"
                                                      onclick="showlibrary('<?php echo $questions->id; ?>')"
                                                      class="btn btn-default btn-sm">Библиотека</a>
                                    <?php

                                    if ((mysqli_query($con, "SELECT * FROM tips WHERE question_id = ".$questions->id)->num_rows) > 0) {
                                        ?>
                                        &nbsp;&nbsp;<a
                                                name="newquestion"
                                                onclick="showcomment('<?php echo $questions->id; ?>')"
                                                class="btn btn-default btn-sm">Подсказки</a>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                            <div style="text-align: center;"><span><b>Осталось вопросов:</b>&nbsp;<?php
                                    $questions_before_end = mysqli_query($con, "SELECT * FROM questions_session WHERE answered = false AND session_id=" . $session_id)->num_rows;
                                    echo $questions_before_end;
                                    ?></span>
                                <?php
                                $question_num = $questions->order_num;
                                $question_type = $questions->type;
                                $question_id = $questions->question_id;
                                $answers_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . ($questions->question_id) . " ORDER BY RAND()");

                                if ($test_info->minutes > 0) { ?>
                                    <span>&nbsp;&nbsp;|&nbsp;&nbsp;<b>Осталось времени:</b>&nbsp;</span>
                                    <span class="afss_mins_bv">00</span>:<span class="afss_secs_bv">00</span>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <input type="hidden" value="<?php echo $session_id ?>" name="id" readonly="readonly"
                                       required class="form-control"/>
                                <input type="hidden" value="<?php echo $question_num ?>" name="questionnum"
                                       readonly="readonly" required class="form-control"/>
                                <input type="hidden" value="<?php echo $question_type ?>" name="questiontype"
                                       readonly="readonly" required class="form-control"/>
                                <input type="hidden" value="<?php echo $question_id ?>" name="questionid"
                                       readonly="readonly" required class="form-control"/>
                                <input type="submit" name="submitAnswer" value="Ответить" class="btn btn-success"/>
                                <input type="submit" name="skipAnswer" value="Пропустить" class="btn btn-primary"/>
                                <right><input type="submit" name="surrender" value="Завершить тест"
                                              class="btn btn-danger pull-right"/></right>
                            </div>
                            <table class="table table-bordered">
                                <tr bgcolor=#ffffff>
                                    <td colspan="2">
                                        <b>Вопрос №<?php echo $question_num ?>:</b> <?php echo($questions->text) ?>
                                    </td>
                                </tr>

                                <?php

                                switch ($question_type) {
                                    case "radiobutton":
                                        for ($i = 1; $answers = mysqli_fetch_object($answers_select); $i++) {
                                            $answer_num = $answers->answer_number;
                                            ?>
                                            <tr bgcolor=#fafafa>
                                            <td>
                                            <input type="radio" name="answer" <?php if ($i == 1) {
                                                echo 'checked';
                                            } ?> value="<?php echo $answer_num; ?>">
                                            <?php
                                            echo ($answers->answer_text) . "</td></tr>";
                                        }
                                        break;
                                    case "checkbox":
                                        for ($i = 1; $answers = mysqli_fetch_object($answers_select); $i++) {
                                            $answer_num = $answers->answer_number;
                                            ?>
                                            <tr bgcolor=#fafafa>
                                            <td>
                                            <input type="checkbox" name="<?php echo 'checkbox' . $answer_num; ?>">
                                            <?php
                                            echo ($answers->answer_text) . "</td></tr>";
                                        }
                                        break;
                                    case "input":
                                        $answers = mysqli_fetch_object($answers_select)
                                        ?>
                                        <tr bgcolor=#fafafa>
                                            <td>
                                                <input type="text" autocomplete="off" name="input_answer"
                                                       class="form-control"/>
                                            </td>
                                        </tr>
                                        <?php
                                        break;
                                    case "order":
                                        $answers_select2 = mysqli_query($con, "SELECT * from answers WHERE question_id = " . ($questions->question_id) . " ORDER BY RAND()");
                                        $options = "";
                                        for ($i = 1; $answers = mysqli_fetch_object($answers_select2); $i++) {
                                            $options .= "<option value='" . ($answers->answer_number) . "'>" . ($answers->answer_text2) . "</option>";
                                        }
                                        mysqli_data_seek($answers_select, 0);
                                        for ($i = 1; $answers = mysqli_fetch_object($answers_select); $i++) {
                                            $answer_num = $answers->answer_number;
                                            ?>

                                            <tr bgcolor=#fafafa>
                                                <td>
                                                    <?php
                                                    echo($answers->answer_text);
                                                    ?>
                                                </td>
                                                <td>
                                                    <select name="<?php echo 'order' . md5(($answers->answer_number) * $session_id); ?>"
                                                            style="width:100%;max-width:100%;">
                                                        <?php
                                                        echo $options;
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <?php
                                        }
                                        break;
                                }
                                mysqli_data_seek($answers_select, 0);
                                ?>
                            </table>

                        <?php } ?>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Mark up for Popups -->

<div id="popupcomment" style="display: none; width: 650px; height: 400px; overflow: auto">
    <div rel="title">
        Подсказки
    </div>
    <div rel="body">
        <div style="padding: 10px; font-size: 12px; line-height: 150%;">

            <div id="comment"></div>

        </div>
    </div>
</div>

<div id="popuplibrary" style="display: none; width: 650px; height: 500px; overflow: auto">
    <div rel="title">
        Библиотечный модуль
    </div>
    <div rel="body">
        <div style="padding: 10px; font-size: 12px; line-height: 150%;">

            <div id="library"></div>

        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="css/w2ui-1.5.rc1.min.css"/>
<script type="text/javascript" src="js/w2ui-1.5.rc1.min.js"></script>
<script type="text/javascript" async src="MathJax/MathJax.js?config=default"></script>

<?php if ($test_info->minutes > 0) { ?>
    <script type="text/javascript">//<![CDATA[
        var remain_bv = <?php echo $time_left;  ?>;

        function parseTime_bv(timestamp) {
            if (timestamp < 0) timestamp = 0;

            var day = Math.floor((timestamp / 60 / 60) / 24);
            var hour = Math.floor(timestamp / 60 / 60);
            var mins = Math.floor(timestamp / 60);
            var mins_for_secs = Math.floor((timestamp - hour * 60 * 60) / 60);
            var secs = Math.floor(timestamp - hour * 60 * 60 - mins_for_secs * 60);
            var left_hour = Math.floor((timestamp - day * 24 * 60 * 60) / 60 / 60);

            $('span.afss_day_bv').text(day);
            $('span.afss_hours_bv').text(left_hour);

            $('span.afss_mins_bv').text(mins);

            if (String(secs).length > 1)
                $('span.afss_secs_bv').text(secs);
            else
                $('span.afss_secs_bv').text("0" + secs);

        }

        $(document).ready(function () {

            setInterval(function () {
                remain_bv = remain_bv - 1;
                parseTime_bv(remain_bv);
                if (remain_bv <= 0) {
                    window.location = "http://testing.local/test.php?id=<?php echo $session_id ?>";
                }
            }, 1000);


        });
        //]]>
    </script>
<?php } ?>
</body>
</html>

