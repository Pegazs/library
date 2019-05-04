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


$result_select = mysqli_query($con, "SELECT * FROM sessions WHERE id = " . $session_id . " AND status != 'started'");

if (mysqli_num_rows($result_select) == 0) {
    header("Location: index.php");
}

$session_info = mysqli_fetch_object($result_select);

if ((($session_info->user_id) != $_SESSION['usr_id']) AND $_SESSION['usr_role'] != "teacher") {
    header("Location: index.php");
}

$test_info = mysqli_fetch_object(mysqli_query($con, "SELECT * FROM tests WHERE id =" . ($session_info->test_id)));
$user_info = mysqli_fetch_object(mysqli_query($con, "SELECT * FROM users WHERE id =" . ($session_info->user_id)));
$error = false;
$error_msg = "";


?>
<!DOCTYPE html>
<html>
<head>
    <title>Результаты прохождения теста «<?php echo($test_info->name) ?>»
        — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>
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
            <a class="navbar-brand"
               href="/"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
        </div>
        <div class="collapse navbar-collapse" id="navbar1">
            <ul class="nav navbar-nav navbar-right">
                <?php if (isset($_SESSION['usr_id'])) { ?>
                    <li><p class="navbar-text">Вы вошли как <?php echo $_SESSION['usr_name']; ?></p></li>
                    <li><a href="logout.php">Выйти</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 text-center">
            <a href="/">Вернуться на главную</a>
            <hr/>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 well">
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="usersform">
                <fieldset>
                    <legend>Результаты прохождения теста «<?php echo($test_info->name) ?>»</legend>
                    <?php

                    if (!$result_select = mysqli_query($con, "SELECT `sessions`.*, `questions_session`.*, `questions`.* FROM `questions` LEFT JOIN `questions_session` ON `questions_session`.`question_id` = `questions`.`id` LEFT JOIN `sessions` ON `questions_session`.`session_id` = `sessions`.`id` WHERE session_id = " . $session_id . " ORDER BY `questions_session`.`order_num`")) {
                        echo "Ошибка запроса, попробуйте ещё раз.";
                    } else { ?>
                        <div class="col-md-6">
                            <b>Логин пользователя:</b> <?php echo $user_info->login; ?>
                        </div>
                        <div class="col-md-6">
                            <b>Время начала
                                прохождения:</b> <?php echo date('H:i:s (d.m.Y)', strtotime($session_info->start_time)); ?>
                        </div>
                        <div class="col-md-6">
                            <b>Имя пользователя:</b> <?php echo $user_info->name; ?>
                        </div>
                        <div class="col-md-6">
                            <b>Время окончания
                                прохождения:</b> <?php echo date('H:i:s (d.m.Y)', strtotime($session_info->finish_time)); ?>
                        </div>
                        <div class="col-md-6">
                            <b>Группа:</b> <?php if ($user_info->user_group == "") {
                                echo "Отсутствует";
                            } else {
                                echo $user_info->user_group;
                            } ?>
                        </div>
                        <div class="col-md-6">
                            <b>Результат:</b> <?php echo $session_info->result;
                            if ($_SESSION['usr_role'] == "teacher") {
                                echo " [с учётом сложности и подсказок: $session_info->result_with_difficulty]";
                            }

                            if (($session_info->result) >= ($test_info->necessary)) {
                                echo " <font color=#006600>(пройден)</font>";
                            } else {
                                echo " <font color=#CC0033>(не пройден)</font>";
                            } ?>
                        </div>
                        <br><br>

                        <?php
                        if ($test_info->mode == "TRAINING") {
                            mysqli_query($con, "SET sql_mode = ''");
                            $query = "SELECT sum(qs.correct)/count(qs.question_id) as result, q.subtheme, s2.name, s2.type FROM questions q JOIN questions_session qs ON qs.question_id = q.id JOIN sessions s ON qs.session_id = s.id JOIN tests t ON q.test_id = t.id JOIN sections s2 ON t.theme_id = s2.id WHERE s.test_id = " . $test_info->id . " AND user_id = " . $user_info->id . " group by q.subtheme";
                            $result = mysqli_query($con, $query);
                            ?>
                            <hr>
                            <b>Рекомендации:</b><br>
                            <?php
                            $bad_counter = 0;
                            while ($row = mysqli_fetch_array($result)) {
                                if ($row['result'] < 0.75) {
                                    $bad_counter++;
                                    echo "• ";
                                    if ($row['subtheme'] == "") {
                                        echo "Дополнительно изучить данную тему в целом";
                                        echo "<br>";
                                    } else {
                                        echo "Дополнительно изучить подтему «" . $row['subtheme'] . "»";
                                        echo "<br>";
                                    }
                                }
                            }
                            if ($bad_counter == 0) {
                                echo "• Тема усвоена, можно переходить к следующей";
                            }
                            ?>
                        <?php } ?>

                        <?php while ($questions = mysqli_fetch_object($result_select)) {
                            $answers_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . ($questions->question_id)); ?>
                            <hr/>
                            <table class="table table-bordered">
                                <tr bgcolor=#ffffff>
                                    <td>
                                        <b>Вопрос №<?php echo($questions->order_num) ?>
                                            :</b> <?php echo($questions->text) ?>
                                    </td>
                                </tr>
                                <?php if (($questions->type) == "checkbox" || ($questions->type) == "radiobutton") { ?>
                                    <tr bgcolor=#fafafa>
                                        <td>
                                            <?php
                                            for ($i = 1; $answers = mysqli_fetch_object($answers_select); $i++) {
                                                if ($questions->type != "order") {
                                                    echo "<b>" . $i . ".</b> " . ($answers->answer_text) . "<br>";
                                                } else {
                                                    echo "<b>" . $answers->answer_text . "</b> = " . ($answers->answer_text2) . "<br>";
                                                }
                                            }
                                            mysqli_data_seek($answers_select, 0);
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr <?php if (($test_info->disable_show) != 1) {
                                    if ($questions->correct) {
                                        echo "class='success'";
                                    } else {
                                        echo "class='danger'";
                                    }
                                } ?>>
                                    <td>
                                        <b>Ваш ответ:</b> <?php echo($questions->answer) ?>
                                    </td>
                                </tr>
                                <?php if (!$questions->correct && ($test_info->disable_show) != 1) {
                                    $real_answer = "";
                                    switch ($questions->type) {
                                        case "radiobutton":
                                        case "checkbox":
                                            while ($answers = mysqli_fetch_object($answers_select)) {
                                                if ($answers->answer_true) {
                                                    $real_answer .= ($answers->answer_number) . "; ";
                                                }
                                            }
                                            break;
                                        case "input":
                                            $answers = mysqli_fetch_object($answers_select);
                                            $real_answer .= ($answers->answer_text);
                                            break;
                                        case "order":
                                            while ($answers = mysqli_fetch_object($answers_select)) {
                                                $real_answer .= "<br>" . ($answers->answer_text) . " = " . ($answers->answer_text2) . ";";
                                            }
                                            break;
                                    }

                                    ?>
                                    <tr class="danger">
                                        <td>
                                            <b>Правильный ответ:</b> <?php echo($real_answer) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <?php if ($questions->comment) { ?>
                                    <tr class="info">
                                        <td>
                                            <b>Коментарий к вопросу:</b> <?php echo($questions->comment) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                <tr class="info">
                                    <td>
                                        <b>Затрачено на вопрос:</b> <?php echo($questions->time_on_question) ?> с.
                                    </td>
                                </tr>
                                <?php if ($questions->tips_used > 0) { ?>
                                    <tr class="info">
                                        <td>
                                            <b>Использовано подсказок:</b> <?php echo($questions->tips_used) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>

                        <?php }
                    } ?>
                    </span>
                </fieldset>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 text-center">
            <hr/>
            <a href="/">Вернуться на главную</a>
            <hr/>
        </div>
    </div>
</div>


<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript" async src="/MathJax/MathJax.js?config=default"></script>
</body>
</html>

