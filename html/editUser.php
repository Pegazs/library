<?php
session_start();

if (empty($_SESSION['usr_id'])) {
    header("Location: index.php");
}

include_once 'dbconnect.php';

$error = false;

//данные, хранящиеся в базе
$result = mysqli_query($con, "SELECT * FROM users WHERE id =" . $_SESSION['usr_id'] . "");
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    $name = $row['name'];
    $user_group = $row['user_group'];
} else {
    header("Location: index.php");
}

//check if form is submitted
if (isset($_POST['saveuser'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $user_group = mysqli_real_escape_string($con, $_POST['user_group']);

    if (!preg_match("/^[а-яА-ЯёЁa-zA-Z ]+$/u", $name)) {
        $error = true;
        $name_error = "Имя должно содержать только буквы и пробелы";
    }

    if (!$error) {
        if (mysqli_query($con, "UPDATE users SET name = '" . $name . "' WHERE id =" . $_SESSION['usr_id'] . "")) {
            $successmsg = "Данные успешно обновлены <a href='/'>Нажмите здесь, чтобы вернуться на главную</a>";
            $_SESSION['usr_name'] = $name;
        } else {
            $errormsg = "Ошибка обновления данных. Пожалуйста, попробуйте ещё раз";
        }
    }

    if (!$error) {
        if ($user_group == "NULL") {
            if (mysqli_query($con, "UPDATE users SET name = '" . $name . "', user_group = NULL WHERE id =" . $_SESSION['usr_id'] . "")) {
                $successmsg = "Данные успешно обновлены <a href='/'>Нажмите здесь, чтобы вернуться на главную</a>";
                $_SESSION['usr_name'] = $name;
            } else {
                $errormsg = "Ошибка обновления данных. Пожалуйста, попробуйте ещё раз";
            }
        } else {
            if (mysqli_query($con, "UPDATE users SET name = '" . $name . "', user_group = '" . $user_group . "' WHERE id =" . $_SESSION['usr_id'] . "")) {
                $successmsg = "Данные успешно обновлены <a href='/'>Нажмите здесь, чтобы вернуться на главную</a>";
                $_SESSION['usr_name'] = $name;
            } else {
                $errormsg = "Ошибка обновления данных. Пожалуйста, попробуйте ещё раз";
            }
        }
    }

}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактирование информации о пользователе
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
        <div class="col-md-4 col-md-offset-4 well">
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
                <fieldset>
                    <legend>Информация о пользователе</legend>

                    <div class="form-group">
                        <label for="name">Имя</label>
                        <input type="text" name="name" placeholder="Введите имя" required value="<?php echo $name; ?>"
                               class="form-control"/>
                        <span class="text-danger"><?php if (isset($name_error)) echo $name_error; ?></span>
                    </div>

                    <?php
                    if ($_SESSION['usr_role'] == 'student') {
                        ?>
                        <div class="form-group">
                            <label for="user_group">Группа</label><br>
                            <select name="user_group" style="width:100%;max-width:100%;">
                                <?php
                                if ($user_group != "") {
                                    echo "<option value='" . $user_group . "'>" . $user_group . "</option>";
                                }
                                ?>
                                <option value="NULL">Нет</option>
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
                        <?php
                    }
                    ?>

                    <div class="form-group">
                        <input type="submit" name="saveuser" value="Сохранить" class="btn btn-primary"/>
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
            <a href="/">Вернуться без сохранения</a>
        </div>
    </div>
</div>

<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

