<?php
session_start();
include_once 'dbconnect.php';

if (empty($_SESSION['usr_id'])) {
    header("Location: index.php");
}

function line_admin($prefix, $id)
{
    global $con;
    $query = "SELECT * FROM sections_hierarchy, sections WHERE id_master = " . $id . " AND id_slave = id ORDER BY slave_number";
    $result = mysqli_query($con, $query);
    $found = mysqli_num_rows($result);

    if ($found > 0) {
        $number = 1;
        while ($row = mysqli_fetch_array($result)) {
            $localPrefix = $prefix . $number . ".";
            if ($row['type'] == "supersection") {
                echo "<h3><b>$localPrefix</b> <a href=/structure.php?id=$row[id]>$row[name]</a></h3>";
            } else if ($row['type'] == "section") {
                echo "<h4><b>$localPrefix</b> <a href=/structure.php?id=$row[id]>$row[name]</a></h4>";
            } else {
                echo "<b>$localPrefix</b> <a href=/structure.php?id=$row[id]>$row[name]</a>";
                echo "<br>";
            }
            line_admin($localPrefix, $row['id']);
            $number++;
        }
    }
}

function line_user($prefix, $id)
{
    global $con;
    $query = "SELECT * FROM sections_hierarchy, sections WHERE id_master = " . $id . " AND id_slave = id ORDER BY slave_number";
    $result = mysqli_query($con, $query);
    $found = mysqli_num_rows($result);

    if ($found > 0) {
        $number = 1;
        while ($row = mysqli_fetch_array($result)) {
            $localPrefix = $prefix . $number . ".";
            if ($row['type'] == "supersection") {
                echo "<h3><b>$localPrefix</b> <a href=/sectionInfo.php?id=$row[id]>$row[name]</a></h3>";
            } else if ($row['type'] == "section") {
                echo "<h4><b>$localPrefix</b> <a href=/sectionInfo.php?id=$row[id]>$row[name]</a></h4>";
            } else {
                echo "<b>$localPrefix</b> <a href=/sectionInfo.php?id=$row[id]>$row[name]</a>";
                echo "<br>";
            }
            line_user($localPrefix, $row['id']);
            $number++;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Оглавление
        — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>

    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js"></script>
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
            <legend>Оглавление</legend>
            <?php
            $query = "SELECT * FROM sections WHERE type = 'discipline' ORDER BY id";
            $result = mysqli_query($con, $query);
            $found = mysqli_num_rows($result);

            if ($found > 0) {
                $number = 1;
                while ($row = mysqli_fetch_array($result)) {
                    $localPrefix = "";

                    if (($_SESSION['usr_role'] != 'teacher' and $_SESSION['usr_role'] != 'admin')) {
                        echo "<h2><b>Дисциплина:</b> <a href=/sectionInfo.php?id=$row[id]>$row[name]</a></h2>";
                        line_user($localPrefix, $row['id']);
                    } else {
                        echo "<h2><b>Дисциплина:</b> <a href=/structure.php?id=$row[id]>$row[name]</a></h2>";
                        line_admin($localPrefix, $row['id']);
                    }
                    $number++;
                }
            }
            ?>
        </div>
        <div class="col-md-2">
            <?php
            if (($_SESSION['usr_role'] != 'teacher' and $_SESSION['usr_role'] != 'admin')) {
                ?>
                <a href="student.php" class="btn btn-info btn-block">Тесты</a>
                <a href="resultsUser.php" class="btn btn-primary btn-block">Результаты тестов</a>
                <a href="library" class="btn btn-primary btn-block">Библиотека</a>
                <a href="editUser.php" class="btn btn-primary btn-block">Ред. данные</a>
                <a href="password.php" class="btn btn-primary btn-block">Сменить пароль</a>


                <?php
            } else if ($_SESSION['usr_role'] == 'teacher') {
                ?>
                <a href="structure.php" class="btn btn-primary btn-block">Редактор</a>
                <a href="teacher.php" class="btn btn-info btn-block">Тесты</a>
                <a href="groups.php" class="btn btn-primary btn-block">Группы</a>
                <a href="library" class="btn btn-primary btn-block">Библиотека</a>
                <a href="editUser.php" class="btn btn-primary btn-block">Ред. данные</a>
                <a href="password.php" class="btn btn-primary btn-block">Сменить пароль</a>
                <?php
            } else if ($_SESSION['usr_role'] == 'admin') {
                ?>

                <a href="/" class="btn btn-info btn-block">На главную</a>
                <a href="structure.php" class="btn btn-primary btn-block">Редактор</a>
                <?php
            }
            ?>

        </div>
    </div>
</div>

</body>
</html>

