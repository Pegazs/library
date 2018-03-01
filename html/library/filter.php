<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id'])) {
    header("Location: ../index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);
$qustion = mysqli_query($con, "SELECT * FROM questions WHERE id=".$question_id);
$text = mysqli_real_escape_string($con, mysqli_fetch_object($qustion)->text);
$text = preg_replace ("/[^a-zA-ZА-Яа-я0-9\s]/","", $text);

?>
<head>
    <script type="text/javascript">
        $(document).ready(function(){
            filter('<?php echo $text; ?>');
            function filter(text){
                $("#result").html("<img src='ajax-loader.gif'/>");
                $.ajax({
                    type:"post",
                    url:"library/search.php",
                    "data": {
                        "title": text,
                        "filter": "best-one"
                    },
                    success:function(data){
                        $("#result").html(data);
//                            $("#search").val("");
                    }
                });
            }

            function search(){
                var title=$("#search").val();
                var filter = $("#filter").val();

                if(title!=""){
                    $("#result").html("<img src='ajax-loader.gif'/>");
                    $.ajax({
                        type:"post",
                        url:"library/search.php",
                        "data": {
                            "title": title,
                            "filter": filter
                        },
                        success:function(data){
                            $("#result").html(data);
//                            $("#search").val("");
                        }
                    });
                }
            }

            $("#button").click(function(){
                search();
            });

            $('#search').keyup(function(e) {
                if(e.keyCode == 13) {
                    search();
                }
            });
        });
    </script>
</head>
                <div class="input-group" id="adv-search">
                    <input type="text" id="search" class="form-control" placeholder="Введите ключевые слова" />
                    <div class="input-group-btn">
                        <div class="btn-group" role="group">
                            <div class="dropdown dropdown-lg">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span class="caret"></span></button>
                                <div class="dropdown-menu dropdown-menu-right" role="menu">
                                    <form class="form-horizontal" role="form">
                                        <div class="form-group">
                                            <label for="filter">Выводить в результатах</label>
                                            <select id="filter" class="form-control">
                                                <option value="best-one" selected>Только лучшее совпадение для каждой книги</option>
                                                <option value="all">Все страницы</option>
                                            </select>
                                        </div>
<!--                                        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>-->
                                    </form>
                                </div>
                                <button type="button" id="button" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                            </div>

                        </div>
                    </div>
                </div>
                <ul class='link' id="result"></ul>