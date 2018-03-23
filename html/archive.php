<?php
session_start();
include_once 'dbconnect.php';
if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

//кнопка «Редактировать»
if (isset($_POST['change'])) {

    $test_id = mysqli_real_escape_string($con, $_POST['test_id']);

    header("Location: questions.php?id=$test_id");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Архив тестов — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
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
            <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
                <fieldset>
                    <legend>Архив тестов</legend>
                    <div class='alert alert-info' role='alert'><i>Тесты в архиве не видны учащимся.</i></div>
                    <?php
                    //if (($result = mysqli_query($con, "SELECT * FROM workers WHERE worker_company = '".$_SESSION['usr_id']."'"))->num_rows != 0) {
                    if (($result_select = mysqli_query($con, "SELECT * FROM tests WHERE mode = 'DEFAULT' AND archive=1 ORDER BY id DESC"))->num_rows > 0) {
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
                        while($test = mysqli_fetch_object($result_select))
                        {
                        echo "<form action='".$_SERVER['PHP_SELF']."' method='post' name='userform'>";
                        echo "<input type = 'hidden' name = 'test_id' value = '$test->id'>";
                        ?>
                        <tr>
                            <th scope="row"><?php echo "$test->name"; ?></th>
                            <td>
                                <?php
                                echo "<input type='submit' name='change' value='Просмотреть' class='btn btn-info btn-xs btn-block'>";
                                ?>
                            </td>
                        </tr>
            </form>
            <?php
            }
            ?>
            </tbody>
            </table>
            <?php } else {
                ?>Нет тестов в архиве. Тесты в архиве не будут показываться учащимся.<?php } ?>
            </fieldset>
            </form>
        </div>
        <div class="col-md-2">
            <a href="/" class="btn btn-info btn-block">На главную</a>
            <a href="teacher.php" class="btn btn-info btn-block">Выйти из архива</a>
            <a href="addTest.php" class="btn btn-success btn-block">Добавить тест</a>
            <a href="resultsTeacher.php" class="btn btn-primary btn-block">Результаты тестов</a>
            <a href="groups.php" class="btn btn-primary btn-block">Группы</a>
            <a href="library" class="btn btn-primary btn-block">Библиотека</a>
            <a href="structure_list.php" class="btn btn-primary btn-block">Структура</a>
            <a href="editUser.php" class="btn btn-primary btn-block">Ред. данные</a>
            <a href="password.php" class="btn btn-primary btn-block">Сменить пароль</a>
        </div>
    </div>
</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
