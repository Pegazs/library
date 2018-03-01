<?php
session_start();
include_once 'dbconnect.php';
if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'admin') {
    header("Location: index.php");
}

$error = false;
$error_msg = "";

if (isset($_POST['toAdmin'])) {

    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);

    if (!$error) {
        if(mysqli_query($con, "UPDATE users SET role = 'admin' WHERE id = ".$user_id."")) {
            $successmsg = "<div class='alert alert-success' role='alert'>Статус изменён</div>";
        } else {
            $errormsg = "<div class='alert alert-danger' role='alert'>Не удалось обновить статус пользователя. Пожалуйста, попробуйте ещё раз</div>";
        }
    }
    else
    {
        $errormsg = "<div class='alert alert-danger' role='alert'>".$error_msg."</div>";
    }
}

if (isset($_POST['toTeacher'])) {

    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);

    if (!$error) {
        if(mysqli_query($con, "UPDATE users SET role = 'teacher' WHERE id = ".$user_id."")) {
            $successmsg = "<div class='alert alert-success' role='alert'>Статус изменён</div>";
        } else {
            $errormsg = "<div class='alert alert-danger' role='alert'>Не удалось обновить статус пользователя. Пожалуйста, попробуйте ещё раз</div>";
        }
    }
    else
    {
        $errormsg = "<div class='alert alert-danger' role='alert'>".$error_msg."</div>";
    }
}

if (isset($_POST['toStudent'])) {

    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);

    if (!$error) {
        if(mysqli_query($con, "UPDATE users SET role = 'student' WHERE id = ".$user_id."")) {
            $successmsg = "<div class='alert alert-success' role='alert'>Статус изменён</div>";
        } else {
            $errormsg = "<div class='alert alert-danger' role='alert'>Не удалось обновить статус пользователя. Пожалуйста, попробуйте ещё раз</div>";
        }
    }
    else
    {
        $errormsg = "<div class='alert alert-danger' role='alert'>".$error_msg."</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Список пользователей — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
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
        <div class="col-md-10 well">
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="usersform">
                <fieldset>
                    <legend>Список пользователей</legend>
                    <span><?php if (isset($successmsg)) { echo $successmsg; } ?></span>
                    <span><?php if (isset($errormsg)) { echo $errormsg; } ?></span>
                    <?php
                    $result_select = mysqli_query($con, "SELECT * FROM users WHERE id!='".$_SESSION['usr_id']."' ORDER BY id DESC");
                    if (mysqli_num_rows($result_select) == 0) { echo "Кроме вас пользователей нет."; }
                    else
                    {      ?>
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Логин</th>
                            <th>Имя</th>
                            <th>Статус</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while($user = mysqli_fetch_object($result_select))
                        {
                        echo "<form action='";
                        echo $_SERVER['PHP_SELF'];
                        echo "' method='post' name='userform'>";
                        echo "<input type = 'hidden' name = 'user_id' value = '$user->id'>";
                        ?>
                        <tr>
                            <th scope="row"><?php echo "$user->login"; ?></th>
                            <td><?php echo "$user->name"; ?></td>
                            <td><?php echo "$user->role"; ?></td>
                            <td>
                                <?php
                                if ($user->role != 'student') {
                                    echo "<input type='submit' name='toStudent' value='Назначить статус сдающего' class='btn btn-success btn-xs btn-block'>";
                                }
                                if ($user->role != 'teacher') {
                                    echo "<input type='submit' name='toTeacher' value='Назначить статус преподавателя' class='btn btn-info btn-xs btn-block'>";
                                }
                                if ($user->role != 'admin') {
                                    echo "<input type='submit' name='toAdmin' value='Назначить администраторский статус' class='btn btn-danger btn-xs btn-block'>";
                                }
                                ?>
                            </td>
                        </tr>
            </form>
            <?php
            }
            }
            ?>
            </tbody>
            </table>
            </fieldset>
            </form>
        </div>
        <div class="col-md-2">
            <a href="password.php" class="btn btn-primary btn-block">Сменить пароль</a>
            <a href="editTop.php" class="btn btn-primary btn-block">Сменить название</a>
            <a href="library" class="btn btn-primary btn-block">Библиотека</a>
        </div>
    </div>
</div>


<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

