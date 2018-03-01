<?php
session_start();
include_once 'dbconnect.php';
if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}


$error = false;
if (isset($_POST['addTest'])) {
    $test_name = mysqli_real_escape_string($con, $_POST['test_name']);
    $minimal_correct = mysqli_real_escape_string($con, $_POST['minimal_correct']);
    $timer = mysqli_real_escape_string($con, $_POST['timer']);
    $user_group = mysqli_real_escape_string($con, $_POST['user_group']);
    if(empty($_POST['disable_show_box'])) {
        $disable_show = 0;
    } else {
        $disable_show = 1;
    }
    if(empty($_POST['archive_box'])) {
        $archive = 0;
    } else {
        $archive = 1;
    }
    if ($minimal_correct == "") {
        $minimal_correct = 0;
    }
    if ($timer == "") {
        $timer = 0;
    }
    if(!preg_match("|^[\d]+$|", $minimal_correct)) {
        $error = true;
        $minimal_correct_error = "Это поле должно содержать целое положительное число или быть пустым";
    }
    if(!preg_match("|^[\d]+$|", $timer)) {
        $error = true;
        $timer_error = "Это поле должно содержать целое положительное число или быть пустым";
    }
    if (!$error) {
        if ($user_group == "NULL")
        {
            if(mysqli_query($con, "INSERT INTO tests(name,necessary,minutes,disable_show,archive,user_group) VALUES('" . $test_name . "','" . $minimal_correct . "','" . $timer . "'," . $disable_show . "," . $archive . ",NULL)")) {
                header("Location: questions.php?id=".mysqli_insert_id($con));
            } else {
                $error = true;
                $errormsg = "Ошибка при добавлении. Пожалуйста, попробуйте ещё раз";
            }
        } else {
            if(mysqli_query($con, "INSERT INTO tests(name,necessary,minutes,disable_show,archive,user_group) VALUES('" . $test_name . "','" . $minimal_correct . "','" . $timer . "'," . $disable_show . "," . $archive . ",'" . $user_group . "')")) {
                header("Location: questions.php?id=".mysqli_insert_id($con));
            } else {
                echo $user_group;
                $error = true;
                $errormsg = "Ошибка при добавлении. Пожалуйста, попробуйте ещё раз";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Добавление нового теста — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
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
            <a class="navbar-brand" href="/"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
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
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="addTestform">
                <fieldset>
                    <legend>Добавление нового теста</legend>

                    <div class="form-group">
                        <label for="name">Название теста</label>
                        <input type="text" name="test_name" placeholder="Введите название" required value="<?php if($error) echo $test_name; ?>" class="form-control" />
                        <span class="text-danger"><?php if (isset($test_name_error)) echo $test_name_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="minimal_correct">Минимальное число правильных ответов</label>
                        <input type="text" name="minimal_correct" placeholder="При его достижении тест будет считаться пройденным (по умолчанию 0)" value="<?php if($error && $minimal_correct > 0) echo $minimal_correct; ?>" class="form-control" />
                        <span class="text-danger"><?php if (isset($minimal_correct_error)) echo $minimal_correct_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="timer">Время прохождения в минутах (не ограничено, если не указать)</label>
                        <input type="text" name="timer" placeholder="По истечении этого времени тест будет завершён" value="<?php if($error && $timer > 0) echo $timer; ?>" class="form-control" />
                        <span class="text-danger"><?php if (isset($timer_error)) echo $timer_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="user_group">Группа, которой будет доступен тест</label><br>
                        <select name="user_group" style="width:100%;max-width:100%;">
                            <option value="NULL">Все группы</option>
                            <?php
                            $groups_select = mysqli_query($con, "SELECT * from groups ORDER BY name");
                            for ($i = 1; $groups = mysqli_fetch_object($groups_select) ; $i++) {
                                echo "<option value='".($groups->name)."'>".($groups->name)."</option>";
                            }
                            ?>
                        </select>
                        <span class="text-danger"><?php if (isset($user_group_error)) echo $user_group_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="disable_show_box" <?php if($error) { if ($disable_show == 1) { echo 'checked'; } }; ?> /> Не отображать правильные ответы после прохождения теста
                        <span class="text-danger"><?php if (isset($disable_show_box_error)) echo $disable_show_box_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="checkbox" name="archive_box" <?php if($error) { if ($archive == 1) { echo 'checked'; } }; ?>/> Отправить тест в архив
                        <span class="text-danger"><?php if (isset($archive_box_error)) echo $archive_box_error; ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" name="addTest" value="Добавить" class="btn btn-primary" />
                    </div>
                </fieldset>
            </form>
            <span class="text-success"><?php if (isset($successmsg)) { echo $successmsg; } ?></span>
            <span class="text-danger"><?php if (isset($errormsg)) { echo $errormsg; } ?></span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-md-offset-4 text-center">
            <a href="index.php">Вернуться без сохранения</a>
        </div>
    </div>
</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

