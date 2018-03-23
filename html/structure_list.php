<?php
session_start();
include_once 'dbconnect.php';

if (empty($_SESSION['usr_id']) or ($_SESSION['usr_role'] != 'teacher' and $_SESSION['usr_role'] != 'admin')) {
    header("Location: index.php");
}

function line($prefix, $id)
{
    global $con;
    $query = "SELECT * FROM sections_hierarchy, sections WHERE id_master = " . $id . " AND id_slave = id ORDER BY slave_number";
    $result = mysqli_query($con, $query);
    $found = mysqli_num_rows($result);

    if ($found > 0) {
        $number = 1;
        while ($row = mysqli_fetch_array($result)) {
            $localPrefix = $prefix . $number . ".";
            echo "<b>$localPrefix</b> <a href=/structure.php?id=$row[id]>$row[name]</a>";
            echo "<br>";
            line($localPrefix, $row['id']);
            $number++;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Просмотр структуры разделов
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
            <legend>Просмотр структуры разделов</legend>
            <?php
            $query = "SELECT * FROM sections WHERE type = 'discipline' ORDER BY id";
            $result = mysqli_query($con, $query);
            $found = mysqli_num_rows($result);

            if ($found > 0) {
                $number = 1;
                while ($row = mysqli_fetch_array($result)) {
                    $localPrefix = "";
                    echo "<b>Дисциплина:</b> <a href=/structure.php?id=$row[id]>$row[name]</a>";
                    echo "<br>";
                    line($localPrefix, $row['id']);
                    $number++;
                }
            }
            ?>
        </div>
        <div class="col-md-2">
            <a href="index.php" class="btn btn-info btn-block">На главную</a>
            <a href="structure.php" class="btn btn-primary btn-block">Редактор</a>
        </div>
    </div>
</div>

</body>
</html>

