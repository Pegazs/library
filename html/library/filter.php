<?php
session_start();
include_once '../dbconnect.php';

if (empty($_SESSION['usr_id'])) {
    header("Location: ../index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);
$qustion = mysqli_query($con, "SELECT * FROM questions WHERE id=" . $question_id);
$qustion_row = mysqli_fetch_object($qustion);
$text = $qustion_row->text;
$test_id = $qustion_row->test_id;
$text = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/", "", $text);
$test = mysqli_query($con, "SELECT * FROM tests WHERE id=" . $test_id);
$theme_id = mysqli_fetch_object($test)->theme_id;
?>
<head>
    <script type="text/javascript">
        $(document).ready(function () {
            //filter('<?php //echo $text; ?>//');
            <?php if ($theme_id) { ?>
            show_library('<?php echo $theme_id; ?>');

            function show_library(id) {
                console.log("TEST");
                $.get("section_operations/show-library.php", {
                    id: id,
                    question_id: <?php echo $question_id; ?>}).done(function (data) {
                    // Display the returned data in browser
                    var element = document.getElementById("library-box");
                    $(element).html(data + "<hr>");
                });
            }
            <?php } ?>

            function filter(text) {
                $("#result").html("<img src='ajax-loader.gif'/>");
                $.ajax({
                    type: "post",
                    url: "library/search.php",
                    "data": {
                        "title": text,
                        "filter": "best-one"
                    },
                    success: function (data) {
                        $("#result").html(data);
//                            $("#search").val("");
                    }
                });
            }

            function search() {
                var title = $("#search").val();
                var filter = $("#filter").val();

                if (title !== "") {
                    $("#result").html("<img src='ajax-loader.gif'/>");
                    $.ajax({
                        type: "post",
                        url: "library/search.php",
                        "data": {
                            "title": title,
                            "filter": filter
                        },
                        success: function (data) {
                            $("#result").html(data);
//                            $("#search").val("");
                        }
                    });
                }
            }

            $("#button").click(function () {
                search();
            });

            $('#search').keyup(function (e) {
                if (e.keyCode === 13) {
                    search();
                }
            });
        });
    </script>
</head>

            <div id="library-box">
            </div>
<div class="input-group" id="adv-search">
    <input type="text" id="search" class="form-control" placeholder="Введите ключевые слова"/>
    <div class="input-group-btn">
        <div class="btn-group" role="group">
            <div class="dropdown dropdown-lg">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false"><span class="caret"></span></button>
                <div class="dropdown-menu dropdown-menu-right" role="menu">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="filter">Выводить в результатах</label>
                            <select id="filter" class="form-control">
                                <option value="best-one" selected>Только лучшее совпадение для каждой книги</option>
                                <option value="all">Все страницы</option>
                            </select>
                        </div>
                        <!--<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>-->
                    </form>
                </div>
                <button type="button" id="button" class="btn btn-primary"><span class="glyphicon glyphicon-search"
                                                                                aria-hidden="true"></span></button>
            </div>

        </div>
    </div>
</div>
<ul class='link' id="result"></ul>