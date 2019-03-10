<?php
session_start();
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id'])) {
    header("Location: ../index.php");
}

?>

<!DOCTYPE html>
<html>
<head>
    <base target='_blank' />
    <title>Поиск по библиотеке — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="../favicon.ico" rel="shortcut icon" type="image/x-icon" />

    <link rel="stylesheet" href="../css/search.css" type="text/css" />



    <script src="../js/jquery.min.js" type="text/javascript"></script>
    <script src="../js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="../css/bootstrap.min.css" type="text/css" />





    <script type="text/javascript">

        function pageClicked(pageId) {
            $.ajax({
                type:"post",
                url:"pageClicked.php",
                "data": {
                    "page_id": pageId,
                    "user_id": <?php echo $_SESSION['usr_id'] ?>
                },
                success:function(data){
                    console.log(data);
                }
            });
        }

        $(document).ready(function(){

            function search(){
                var title=$("#search").val();
                var filter = $("#filter").val();

                if(title!==""){
                    $("#result").html("<img src='ajax-loader.gif'/>");
                    $.ajax({
                        type:"post",
                        url:"search.php",
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
                <a class="navbar-brand" target='_self' href="/"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
            </div>
            <div class="collapse navbar-collapse" id="navbar1">
                <ul class="nav navbar-nav navbar-right">
                    <?php if (isset($_SESSION['usr_id'])) { ?>
                        <li><p class="navbar-text">Вы вошли как <?php echo $_SESSION['usr_name']; ?></p></li>
                        <li><a href="../logout.php" target='_self'>Выйти</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div id="search-side-text">
                    <a class='kinda-link' onclick="$('#popup1').w2popup()">Инструкция</a>
                    &nbsp; | &nbsp;
                    <a target="_self" href="books.php">Все книги</a>
                    &nbsp; | &nbsp;
                    <a target="_self" href="../index.php">На главную</a>
                </div>
            </div>
            <div class="col-md-12">
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
                                                <option value="combined" selected>Комбинированый поиск</option>
                                                <option value="index-simple">Поиск по предметному указателю</option>
<!--                                                <option value="index-double-check">Поиск по предметному указателю И наличию на странице</option>-->
                                                <option value="best-one">Только лучшее совпадение для каждой книги</option>
                                                <option value="all">Все страницы</option>
                                            </select>
                                        </div>
<!--                                        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>-->
                                    </form>
                                </div>
                            </div>
                            <button type="button" id="button" class="btn btn-primary"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                        </div>
                    </div>
                </div>
                <br>
                <ul class='link' id="result"></ul>
            </div>
        </div>
    </div>


    <!-- Mark up for Popups -->

    <div id="popup1" style="display: none; width: 650px; height: 500px; overflow: auto">
        <div rel="title">
            Поддерживаемые поисковые операторы
        </div>
        <div rel="body">
            <div style="padding: 10px; font-size: 11px; line-height: 150%;">
                В поисковом запросе поддерживаются следующие операторы:
                <br>

                <table width="100%" cellspacing="2" cellpadding="5" border="1" class="frame">
                    <tr>
                        <th class="light">Оператор</th>
                        <th class="light">Действие</th>
                    </tr>
                    <tr>
                        <td class="light" align="center">+</td>
                        <td class="light">Предшествующий слову знак &#171;плюс&#187; показывает, что это слово должно присутствовать в каждой возвращенной строке.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">-</td>
                        <td class="light">Предшествующий слову знак &#171;минус&#187; означает, что это слово не должно присутствовать в какой-либо возвращенной строке.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">&nbsp;</td>
                        <td class="light">По умолчанию (если ни &#171;плюс&#187;, ни &#171;минус&#187; не указаны) данное слово является не обязательным, но содержащие его строки будут оцениваться более высоко.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">< ></td>
                        <td class="light">Эти два оператора используются для того, чтобы изменить вклад слова в величину релевантности, которое приписывается строке. Оператор < уменьшает этот вклад, а оператор > - увеличивает его. См. пример ниже.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">( )</td>
                        <td class="light">Круглые скобки группируют слова в подвыражения.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">~</td>
                        <td class="light">Предшествующий слову знак &#171;тильда&#187; воздействует как оператор отрицания, обуславливая негативный вклад данного слова в релевантность строки. Им отмечают нежелательные слова. Строка, содержащая такое слово, будет оценена ниже других, но не будет исключена совершенно, как в случае оператора - &#171;минус&#187;.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">*</td>
                        <td class="light">Звездочка является оператором усечения. В отличие от остальных операторов, она должна добавляться в конце слова, а не в начале.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center">" "</td>
                        <td class="light">Фраза, заключенная в двойные кавычки, соответствует только строкам, содержащим эту фразу, написанную буквально.</td>
                    </tr>
                </table>

                <br>

                Ниже приведен ряд примеров:
                <br>

                <table width="100%" cellspacing="2" cellpadding="5" border="1" class="frame">
                    <tr>
                        <th class="light">Пример</th>
                        <th class="light">Описание</th>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>связь&nbsp;информатика</td>
                        <td class="light">Находит строки, содержащие по меньшей мере одно из этих слов.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>+связь&nbsp;+информатика</td>
                        <td class="light">Находит строки, содержащие оба этих слова.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>+связь&nbsp;информатика</td>
                        <td class="light">Находит строки, содержащие слово &#171;связь&#187;, но ранг строки выше, если она также содержит слово &#171;информатика&#187;.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>+связь&nbsp;-информатика</td>
                        <td class="light">Находит строки, содержащие слово &#171;связь&#187;, но не &#171;информатика&#187;.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>+связь&nbsp;+(>информатика&nbsp;<современная)</td>
                        <td class="light">Находит строки, содержащие слова &#171;связь&#187; и &#171;информатика&#187;, или &#171;связь&#187; и &#171;современная&#187; (в любом порядке), но ранг &#171;связь&#187; и &#171;информатика&#187; выше чем &#171;связь&#187; и &#171;современная&#187;.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>информа*</td>
                        <td class="light">Находит строки, содержащие слова &#171;информатика&#187;, &#171;информация&#187;, &#171;информационный&#187; и др.</td>
                    </tr>
                    <tr>
                        <td class="light" align="center" nowrap>"университет связи и информатики"</td>
                        <td class="light">Находит строки, содержащие фразу &#171;университет связи и информатики&#187;, но не &#171;университет информатики и связи&#187;.</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="../css/w2ui-1.5.rc1.min.css" />
    <script type="text/javascript" src="../js/w2ui-1.5.rc1.min.js"></script>
</body>
</html>