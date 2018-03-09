<?php
session_start();
include_once 'dbconnect.php';

if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактирование структуры разделов
        — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>

    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js"></script>
    <style type="text/css">
        .input-group-addon.primary {
            color: rgb(255, 255, 255);
            background-color: rgb(50, 118, 177);
            border-color: rgb(40, 94, 142);
        }

        .input-group-addon.success {
            color: rgb(255, 255, 255);
            background-color: rgb(92, 184, 92);
            border-color: rgb(76, 174, 76);
        }

        .input-group-addon.info {
            color: rgb(255, 255, 255);
            background-color: rgb(57, 179, 215);
            border-color: rgb(38, 154, 188);
        }

        .input-group-addon.warning {
            color: rgb(255, 255, 255);
            background-color: rgb(240, 173, 78);
            border-color: rgb(238, 162, 54);
        }

        .input-group-addon.danger {
            color: rgb(255, 255, 255);
            background-color: rgb(217, 83, 79);
            border-color: rgb(212, 63, 58);
        }

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
                var type = document.getElementById("global-type").value;
                var resultDropdown = $(this).siblings(".result");
                if (inputVal.length) {
                    $.get("section_operations/section-search.php", {term: inputVal, type: type}).done(function (data) {
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
                section_id($(this).text(), document.getElementById("global-type").value);
                $(this).parent(".result").empty();
            });
        });

        function new_section() {
            var searchinput = document.getElementById("global-name");
            var inputVal = $(searchinput).val();
            var type = document.getElementById("global-type").value;
            var resultDropdown = $(searchinput).siblings(".result");
            resultDropdown.empty();
            if (inputVal.length) {
                $.get("section_operations/section-add.php", {term: inputVal, type: type}).done(function (data) {
                    // Display the returned data in browser
                    create_delete(data);
                    show_library(data);
                    if (type != "theme") {
                        show_slaves(data);
                    } else {
                        var element = document.getElementById("slaves-box");
                        $(element).html("");
                    }
                });
            }
        }

        function new_slave() {
            var searchinput = document.getElementById("slave-name");
            var inputVal = $(searchinput).val();
            var type = document.getElementById("slave-type").value;
            var resultDropdown = $(searchinput).siblings(".result");
            resultDropdown.empty();
            if (inputVal.length) {
                $.get("section_operations/new-slave.php", {
                    global_id: global_id,
                    name: inputVal,
                    type: type
                }).done(function (data) {
                    show_slaves(global_id);
                });
            }
        }

        function add_book(name) {
            $.get("section_operations/add-book.php", {section_id: global_id, name: name}).done(function (data) {
                show_library(global_id);
            });
        }

        function add_slave(name, type) {
            $.get("section_operations/add-slave.php", {
                global_id: global_id,
                name: name,
                type: type
            }).done(function (data) {
                show_slaves(global_id);
            });
        }

        function section_id(name, type) {
            $.get("section_operations/section-id.php", {term: name, type: type}).done(function (data) {
                create_delete(data);
                show_library(data);
                if (type != "theme") {
                    show_slaves(data);
                } else {
                    var element = document.getElementById("slaves-box");
                    $(element).html("");
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

        function show_slaves(id) {
            $.get("section_operations/show-slaves.php", {id: id}).done(function (data) {
                // Display the returned data in browser
                var element = document.getElementById("slaves-box");
                $(element).html(data);
            });
        }

        function delete_book(section_id, book_id) {
            $.get("section_operations/delete-book.php", {
                section_id: section_id,
                book_id: book_id
            }).done(function (data) {
                show_library(section_id);
            });
        }

        function delete_slave(master_id, slave_id) {
            $.get("section_operations/delete-slave.php", {
                master_id: master_id,
                slave_id: slave_id
            }).done(function (data) {
                show_slaves(master_id);
            });
        }

        function create_delete(id) {
            global_id = id;
            element = document.getElementById("delete-box");
            $(element).html("<hr/><a name=\"delete\" onclick=\"delete_section('" + id + "')\" class=\"btn btn-sm btn-danger\">Удалить</a>");
        }

        function delete_section(id) {
            $.get("section_operations/delete-section.php", {
                id: id
            }).done(function (data) {
                var element = document.getElementById("library-box");
                $(element).html("");
                element = document.getElementById("slaves-box");
                $(element).html("");
                element = document.getElementById("delete-box");
                $(element).html("");
                var searchinput = document.getElementById("global-name");
                searchinput.value = "";
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
            <a href="/">Вернуться на главную</a>
            <hr/>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 well">
            <legend>Редактирование структуры разделов</legend>
            <div class="row">
                <div class="col-sm-2">
                    <select id="global-type" class="form-control">
                        <option value="discipline" selected>Дисциплина</option>
                        <option value="section">Раздел</option>
                        <option value="theme">Тема</option>
                    </select>
                </div>
                <div class="col-sm-10">
                    <div class="search-box" id="search-box-global">
                        <input width="100%" type="text" id="global-name" autocomplete="off"
                               placeholder="Введите название для поиска или добавления"/>
                        <div class="result" id="result-global"></div>
                    </div>
                </div>
            </div>
            <div id="slaves-box">
            </div>
            <div id="library-box">
            </div>
            <div id="delete-box" style="text-align: center;">
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(function () {
        var focusedElement;
        $(document).on('focus', 'input', function () {
            if (focusedElement == this) return; //already focused, return so user can now place cursor at specific point in input.
            focusedElement = this;
            setTimeout(function () {
                focusedElement.select();
            }, 50); //select all text in any field on focus for easy re-entry. Delay sightly to allow focus to "stick" before selecting.
        });
    });
</script>
</body>
</html>
