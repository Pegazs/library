<?php
session_start();

if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}
include_once 'dbconnect.php';

//получаем id из адресной строки
if (!empty($_GET['id']) && isset($_GET['id'])) {
    $test_id = mysqli_real_escape_string($con, $_GET['id']);
} else {
    $test_id = mysqli_real_escape_string($con, $_POST['id']);
}

if (empty($test_id)) {
    header("Location: index.php");
}

//данные, хранящиеся в базе
$result = mysqli_query($con, "SELECT * FROM tests WHERE id='" . $test_id . "'");
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    $test_name = $row['name'];
    $minimal_correct = $row['necessary'];
    $tips_penalty = $row['tips_penalty'];
    $show_tips = $row['show_tips'];
    $in_session = $row['in_session'];
    $min_difficulty = $row['min_difficulty'];
    $max_difficulty = $row['max_difficulty'];
    $timer = $row['minutes'];
    $user_group = $row['user_group'];
    $disable_show = $row['disable_show'];
    $archive = $row['archive'];
    $mode = $row['mode'];
    $theme_id = $row['theme_id'];
    $theme_name = "";
    if ($theme_id != null) {
        $theme_name = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM sections WHERE id='" . $theme_id . "'"))['name'];
    }
} else {
    header("Location: index.php");
}

$error = false;

if (isset($_POST['savetest'])) {
    $test_name = mysqli_real_escape_string($con, $_POST['test_name']);
    $test_id = mysqli_real_escape_string($con, $_POST['id']);
    $minimal_correct = mysqli_real_escape_string($con, $_POST['minimal_correct']);
    $in_session = mysqli_real_escape_string($con, $_POST['in_session']);
    $min_difficulty = mysqli_real_escape_string($con, $_POST['min_difficulty']);
    $max_difficulty = mysqli_real_escape_string($con, $_POST['max_difficulty']);
    $timer = mysqli_real_escape_string($con, $_POST['timer']);
    $user_group = mysqli_real_escape_string($con, $_POST['user_group']);
    if (empty($_POST['disable_show_box'])) {
        $disable_show = 0;
    } else {
        $disable_show = 1;
    }
    if (empty($_POST['tips_penalty'])) {
        $tips_penalty = 0;
    } else {
        $tips_penalty = 1;
    }
    if (empty($_POST['show_tips'])) {
        $show_tips = 0;
    } else {
        $show_tips = 1;
    }
    if (empty($_POST['archive_box'])) {
        $archive = 0;
    } else {
        $archive = 1;
    }
    if ($minimal_correct == "") {
        $minimal_correct = 0;
    }
    if ($in_session == "") {
        $in_session = "NULL";
    } else {
        $in_session = "'" . $in_session . "'";
    }
      if ($min_difficulty == "") {
        $min_difficulty = "NULL";
    } else {
        $min_difficulty = "'" . $min_difficulty . "'";
    }
    if ($max_difficulty == "") {
        $max_difficulty = "NULL";
    } else {
        $max_difficulty = "'" . $max_difficulty . "'";
    }
    if ($timer == "") {
        $timer = 0;
    }
    if (!preg_match("|^[\d]+$|", $minimal_correct)) {
        $error = true;
        $minimal_correct_error = "Это поле должно содержать целое положительное число или быть пустым";
    }
    if (!preg_match("|^[\d]+$|", $timer)) {
        $error = true;
        $timer_error = "Это поле должно содержать целое положительное число или быть пустым";
    }
    if (!$error) {
        if ($user_group == "NULL") {
            if (mysqli_query($con, "UPDATE tests SET name = '" . $test_name . "', min_difficulty = ".$min_difficulty.", max_difficulty = ".$max_difficulty.", in_session = ".$in_session.", necessary = '" . $minimal_correct . "', minutes = '" . $timer . "', disable_show = '" . $disable_show . "', tips_penalty = '" . $tips_penalty . "', show_tips = '" . $show_tips . "', archive = '" . $archive . "', user_group = NULL  WHERE id = '" . $test_id . "'")) {
                header("Location: questions.php?id=" . $test_id);
            } else {
                $errormsg = "Ошибка при обновлении названия. Пожалуйста, попробуйте ещё раз";
            }
        } else {
            if (mysqli_query($con, "UPDATE tests SET name = '" . $test_name . "', min_difficulty = ".$min_difficulty.", max_difficulty = ".$max_difficulty.", in_session = ".$in_session.",  necessary = '" . $minimal_correct . "', minutes = '" . $timer . "', disable_show = '" . $disable_show . "', tips_penalty = '" . $tips_penalty . "', show_tips = '" . $show_tips . "', archive = '" . $archive . "', user_group = '" . $user_group . "'  WHERE id = '" . $test_id . "'")) {
                header("Location: questions.php?id=" . $test_id);
            } else {
                $errormsg = "Ошибка при обновлении названия. Пожалуйста, попробуйте ещё раз";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактирование параметров теста
        — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>

    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js"></script>

    <style type="text/css">
        body {
            font-family: Arail, sans-serif;
        }

        /* Formatting search box */
        .search-box {
            width: 100%;
            position: relative;
            display: inline-block;
            font-size: 14px;
        }

        .search-box input[type="text"] {
            height: 32px;
            width: 100%;
            padding: 5px 10px;
            border: 1px solid #CCCCCC;
            font-size: 14px;
        }

        .result {
            position: absolute;
            z-index: 999;
            top: 100%;
            left: 0;
        }

        .search-box input[type="text"], .result {
            width: 100%;
            box-sizing: border-box;
        }

        /* Formatting result items */
        .result p {
            margin: 0;
            padding: 7px 10px;
            border: 1px solid #CCCCCC;
            border-top: none;
            cursor: pointer;
            background: #ffffff;
        }

        .result a {
            margin: 0;
            padding: 7px 10px;
            border: 1px solid #CCCCCC;
            border-top: none;
            cursor: pointer;
            background: #ffffff;
        }

        .result p:hover {
            background: #f2f2f2;
        }

        .result a:hover {
            background: #f2f2f2;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function () {

            var global_id;
            $("#global-name").on("keyup input", function () {
                /* Get input value on change */
                var inputVal = $(this).val();
                var resultDropdown = $(this).siblings(".result");
                if (inputVal.length) {
                    $.get("section_operations/section-theme-search.php", {term: inputVal}).done(function (data) {
                        // Display the returned data in browser
                        resultDropdown.html(data);
                    });
                } else {
                    resultDropdown.empty();
                }
            });

            // Set search input value on click of result item
            $(document).on("click", "#result-global p", function () {
                $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
                $(this).parent(".result").empty();
            });
        });
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
        <div class="col-md-6 col-md-offset-3 well">
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
                <fieldset>
                    <legend>Редактирование параметров теста</legend>

                    <div class="form-group">
                        <label for="name">Название теста</label>
                        <input type="text" name="test_name" placeholder="Введите название" required
                               value="<?php echo $test_name; ?>" class="form-control"/>
                        <span class="text-danger"><?php if (isset($test_name_error)) echo $test_name_error; ?></span>
                        <input type="hidden" value="<?php echo $test_id ?>" name="id" readonly="readonly" required
                               value="<?php if ($error) echo $test_id; ?>" class="form-control"/>
                    </div>

                    <div class="form-group">
                        <label for="min_difficulty">Минимальная сложность вопросов</label>
                        <input type="text" name="min_difficulty"
                               placeholder="Вопросы не менее данной сложности будут в сессиях"
                               value="<?php if ($min_difficulty > 0) {
                                   echo $min_difficulty;
                               } ?>" class="form-control"/>
                        <span class="text-danger"><?php if (isset($min_difficulty_error)) echo $min_difficulty_error; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="max_difficulty">Максимальная сложность вопросов</label>
                        <input type="text" name="max_difficulty"
                               placeholder="Вопросы не более данной сложности будут в сессиях"
                               value="<?php if ($max_difficulty > 0) {
                                   echo $max_difficulty;
                               } ?>" class="form-control"/>
                        <span class="text-danger"><?php if (isset($max_difficulty_error)) echo $max_difficulty_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="in_session">Число вопросов в сессии</label>
                        <input type="text" name="in_session"
                               placeholder="Используемых вопросов в сессии (если пустое, то все)"
                               value="<?php if ($in_session > 0) {
                                   echo $in_session;
                               } ?>" class="form-control"/>
                        <span class="text-danger"><?php if (isset($in_session_error)) echo $in_session_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="minimal_correct">Минимальное число правильных ответов</label>
                        <input type="text" name="minimal_correct"
                               placeholder="При его достижении тест будет считаться пройденным (по умолчанию 0)"
                               value="<?php if ($minimal_correct > 0) {
                                   echo $minimal_correct;
                               } ?>" class="form-control"/>
                        <span class="text-danger"><?php if (isset($minimal_correct_error)) echo $minimal_correct_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="timer">Время прохождения в минутах (не ограничено, если не указать)</label>
                        <input type="text" name="timer" placeholder="По истечении этого времени тест будет завершён"
                               value="<?php if ($timer > 0) {
                                   echo $timer;
                               } ?>" class="form-control"/>
                        <span class="text-danger"><?php if (isset($timer_error)) echo $timer_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="user_group">Группа, которой будет доступен тест</label><br>
                        <select name="user_group" style="width:100%;max-width:100%;">
                            <?php
                            if ($user_group != null && $user_group != "" && $user_group != "NULL") {
                                echo "<option value='" . $user_group . "'>" . $user_group . "</option>";
                            }
                            ?>
                            <option value="NULL">Все группы</option>
                            <?php
                            if ($user_group != "") {
                                $groups_select = mysqli_query($con, "SELECT * from groups WHERE name<>'" . $user_group . "' ORDER BY name");
                            } else {
                                $groups_select = mysqli_query($con, "SELECT * from groups ORDER BY name");
                            }
                            for ($i = 1; $groups = mysqli_fetch_object($groups_select); $i++) {
                                echo "<option value='" . ($groups->name) . "'>" . ($groups->name) . "</option>";
                            }
                            ?>
                        </select>
                        <span class="text-danger"><?php if (isset($user_group_error)) echo $user_group_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="disable_show_box" <?php if ($disable_show == 1) {
                            echo 'checked';
                        } ?> /> Не отображать правильные ответы после прохождения теста
                        <span class="text-danger"><?php if (isset($disable_show_box_error)) echo $disable_show_box_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="show_tips" <?php if ($show_tips == 1) {
                            echo 'checked';
                        } ?> /> Показывать подсказки и библиотеку
                        <span class="text-danger"><?php if (isset($show_tips_error)) echo $show_tips_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="tips_penalty" <?php if ($tips_penalty == 1) {
                            echo 'checked';
                        } ?> /> Уменьшать баллы за использование подсказок и библиотеки
                        <span class="text-danger"><?php if (isset($tips_penalty_error)) echo $tips_penalty_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="archive_box" <?php if ($archive == 1) {
                            echo 'checked';
                        } ?>/> Отправить тест в архив
                        <span class="text-danger"><?php if (isset($archive_box_error)) echo $archive_box_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" name="savetest" value="Сохранить" class="btn btn-primary"/>
                    </div>
                </fieldset>
            </form>
            <span class="text-success"><?php if (isset($successmsg)) {
                    echo $successmsg;
                } ?></span>
            <span class="text-danger"><?php if (isset($errormsg)) {
                    echo $errormsg;
                } ?></span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 text-center">
            <?php echo "<a href='/questions.php?id=" . $test_id . "'>Вернуться без сохранения</a>"; ?>
        </div>
    </div>
</div>
</body>
</html>

