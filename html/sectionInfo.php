<?php
session_start();
include_once 'dbconnect.php';

if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'student') {
    header("Location: index.php");
}

if (!empty($_GET['id']) && isset($_GET['id'])) {
    $section_id = mysqli_real_escape_string($con, $_GET['id']);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Просмотр темы
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
    </style>

    <script type="text/javascript">

        $(document).ready(function () {
            <?php
            if ($section_id != null && $section_id != "") {
                echo "section_url($section_id)";
            }
            ?>
        });

        function section_url(id) {
            $.get("section_operations/section-id.php", {id: id}).done(function (data) {
                var dataArray = data.split("|");
                show_library(id);
                var element = document.getElementById("slaves-box");
                if (dataArray[1] !== "theme") {
                    $(element).html("<br>В текущей версии доступны курсы только по темам")
                } else {
                    if (dataArray[2].length > 0) {
                        $(element).html("<br><a href=\"test.php?id=" + dataArray[2] + "\" class=\"btn btn-success btn-block\">Пройти курс</a>");
                    } else {
                        $(element).html("<br>Курс по выбранной теме отсутствует")
                    }
                }
            });
        }

        function show_library(id) {
            $.get("section_operations/show-library.php", {id: id}).done(function (data) {
                // Display the returned data in browser
                var element = document.getElementById("library-box");
                $(element).html(data);
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
            <a href="structure_list.php">Вернуться к оглавлению</a>
            <hr/>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 well">
            <legend>Просмотр темы</legend>
            <div id="library-box">
            </div>
            <div id="slaves-box">
            </div>
        </div>
    </div>
</div>

</body>
</html>

