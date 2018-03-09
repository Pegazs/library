<?php
session_start();
include_once 'dbconnect.php';
if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'student') {
    header("Location: index.php");
}

$user_group = mysqli_fetch_object(mysqli_query($con, "SELECT * FROM users WHERE id =" . $_SESSION['usr_id'] . ""))->user_group;
//кнопка «Начать»
if (isset($_POST['start'])) {

    $test_id = mysqli_real_escape_string($con, $_POST['test_id']);
    if ($result_select = mysqli_query($con, "SELECT * FROM sessions WHERE test_id = " . $test_id . " AND user_id = " . $_SESSION['usr_id'] . " AND status = 'started'")) {
        if (mysqli_num_rows($result_select) == 0) {
            $result_select = mysqli_query($con, "SELECT * FROM questions WHERE test_id = " . $test_id . " ORDER BY RAND()");
            mysqli_query($con, "INSERT INTO sessions(test_id, user_id) VALUES (" . $test_id . "," . $_SESSION['usr_id'] . ")");
            $session_id = mysqli_insert_id($con);
            for ($i = 1; $question = mysqli_fetch_object($result_select); $i++) {
                mysqli_query($con, "INSERT INTO questions_session(session_id, question_id, order_num) VALUES (" . $session_id . "," . ($question->id) . "," . $i . ")");
            }
        } else {
            $session_id = mysqli_fetch_object($result_select)->id;
        }
        header("Location: test.php?id=$session_id");
    } else {
        $test_error = "<div class='alert alert-danger' role='alert'>Ошибка при выборе теста. Пожалуйста, попробуйте ещё раз</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Доступные тесты
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
        <div class="col-md-10 well">
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
                <fieldset>
                    <legend>Список контрольных тестов <span style="font-size: 60%; "><i><a href="student_training.php">[перейти к тренировочным]</a></i></span>
                    </legend>
                    <span><?php if (isset($test_error)) {
                            echo $test_error;
                        } ?></span>
                    <?php
                    //if (($result = mysqli_query($con, "SELECT * FROM workers WHERE worker_company = '".$_SESSION['usr_id']."'"))->num_rows != 0) {
                    if (($result_select = mysqli_query($con, "SELECT * FROM tests WHERE archive!=1 AND mode = 'DEFAULT' AND (user_group IS NULL OR user_group = '" . $user_group . "') ORDER BY id DESC"))->num_rows > 0) {
                    ?>
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Название теста</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($test = mysqli_fetch_object($result_select))
                        {
                        echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='post' name='userform'>";
                        echo "<input type = 'hidden' name = 'test_id' value = '$test->id'>";
                        ?>
                        <tr>
                            <th scope="row"><?php echo "$test->name"; ?></th>
                            <td>
                                <?php
                                echo "<input type='submit' name='start' value='Начать' class='btn btn-success btn-xs btn-block'>";
                                ?>
                            </td>
                        </tr></fieldset></form>
            <?php
            }
            ?>
            </tbody>
            </table>
            <?php } else {
                ?>Тесты не найдены.<?php } ?>
        </div>
        <div class="col-md-2">
            <a href="resultsUser.php" class="btn btn-primary btn-block">Результаты тестов</a>
            <a href="library" class="btn btn-primary btn-block">Библиотека</a>
            <a href="editUser.php" class="btn btn-primary btn-block">Ред. данные</a>
            <a href="password.php" class="btn btn-primary btn-block">Сменить пароль</a>
        </div>
    </div>
</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
