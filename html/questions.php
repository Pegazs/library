<?php
session_start();
include_once 'dbconnect.php';

if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$site_address = mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_address'"))->settings_value;

//получаем id из адресной строки
if (!empty($_GET['id']) && isset($_GET['id'])) {
    $test_id = mysqli_real_escape_string($con, $_GET['id']);
} else {
    $test_id = mysqli_real_escape_string($con, $_POST['id']);
}

if (empty($test_id)) {
    header("Location: index.php");
}

//данные, хранящиеся в базе
$result = mysqli_query($con, "SELECT * FROM tests WHERE id='" . $test_id . "'");
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result);
    $test_name = $row['name'];
} else {
    header("Location: index.php");
}

$error = false;

if (isset($_POST['deletetest'])) {
    mysqli_query($con, "DELETE FROM tests WHERE id = '" . $test_id . "'");
    header("Location: index.php");
}

if (isset($_POST['importexcel'])) {
    $uploadfile = $_FILES["upload_file"]["tmp_name"];
    $tmp = explode(".", $_FILES['upload_file']['name']);
    $extension = end($tmp);
    if ($extension == "xls" || $extension == "xlsx") {
        // подключаем библиотеку
        require_once dirname(__FILE__) . '/excel/PHPExcel-1.8/Classes/PHPExcel.php';
        $result = array();
        // получаем тип файла, чтобы правильно его обработать
        $file_type = PHPExcel_IOFactory::identify($uploadfile);
        // создаем объект для чтения
        $objReader = PHPExcel_IOFactory::createReader($file_type);
        $objPHPExcel = $objReader->load($uploadfile); // загружаем данные файла в объект
        $matrix = $objPHPExcel->getActiveSheet()->toArray(); // выгружаем данные из объекта в массив
        $rows_count = count($matrix);
        $columns_count = count($matrix[0]);
        $max_answers = ($columns_count - 3) / 2;
        $array_types = array('radiobutton', 'checkbox', 'input', 'order');
        $add_counter = 0;
//		if($max_answers>0 && is_int($max_answers)) {
        if ($max_answers > 0) {
            for ($i = 0; $i < $rows_count; $i++) {
                $question_text = mysqli_real_escape_string($con, $matrix[$i][0]);
                $question_comment = mysqli_real_escape_string($con, $matrix[$i][1]);
                $question_type = mysqli_real_escape_string($con, $matrix[$i][2]);
                if (strlen($question_comment) > 0) {
                    $sql_question = "INSERT INTO questions(test_id,text,type,comment) VALUES(" . $test_id . ", '" . $question_text . "', '" . $question_type . "', '" . $question_comment . "')";
                } else {
                    $sql_question = "INSERT INTO questions(test_id,text,type) VALUES(" . $test_id . ", '" . $question_text . "', '" . $question_type . "')";
                }
                if (in_array($question_type, $array_types) || strlen($question_text) > 0) {
                    if (mysqli_query($con, $sql_question)) {
                        $add_counter++;
                        $question_id = mysqli_insert_id($con);
                        switch ($question_type) {
                            case "radiobutton":
                                for ($j = 0; $j < $max_answers; $j++) {
                                    $answer_text1 = mysqli_real_escape_string($con, $matrix[$i][3 + (2 * $j)]);
                                    $true = mysqli_real_escape_string($con, $matrix[$i][4 + (2 * $j)]);
                                    if (strlen($true) > 0 && strlen($answer_text1) > 0) {
                                        if ($true != 0) {
                                            $true = 1;
                                        }
                                        mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_true) VALUES(" . $question_id . ", " . ($j + 1) . ", '" . $answer_text1 . "', " . $true . ")");
                                    } else {
                                        break;
                                    }
                                }
                                if (mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $question_id . " AND answer_true = 1")->num_rows != 1) {
                                    mysqli_query($con, "DELETE FROM questions WHERE id = '" . $question_id . "'");
                                    $add_counter--;
                                }
                                break;
                            case "checkbox":
                                for ($j = 0; $j < $max_answers; $j++) {
                                    $answer_text1 = mysqli_real_escape_string($con, $matrix[$i][3 + (2 * $j)]);
                                    $true = mysqli_real_escape_string($con, $matrix[$i][4 + (2 * $j)]);
                                    if (strlen($true) > 0 && strlen($answer_text1) > 0) {
                                        if ($true != 0) {
                                            $true = 1;
                                        }
                                        mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_true) VALUES(" . $question_id . ", " . ($j + 1) . ", '" . $answer_text1 . "', " . $true . ")");
                                    } else {
                                        break;
                                    }
                                }
                                if (mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $question_id . " AND answer_true = 1")->num_rows == 0) {
                                    mysqli_query($con, "DELETE FROM questions WHERE id = '" . $question_id . "'");
                                    $add_counter--;
                                }
                                break;
                            case "input":
                                $answer_text1 = mysqli_real_escape_string($con, $matrix[$i][3]);
                                $answer_text2 = mysqli_real_escape_string($con, $matrix[$i][4]);
                                if (strlen($answer_text1) > 0 && strlen($answer_text2) > 0) {
                                    mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text, answer_true) VALUES(" . $question_id . ", 1, '" . $answer_text1 . "',1)");
                                } else {
                                    mysqli_query($con, "DELETE FROM questions WHERE id = '" . $question_id . "'");
                                    $add_counter--;
                                }
                                break;
                            case "order":
                                for ($j = 0; $j < $max_answers; $j++) {
                                    $answer_text1 = mysqli_real_escape_string($con, $matrix[$i][3 + (2 * $j)]);
                                    $answer_text2 = mysqli_real_escape_string($con, $matrix[$i][4 + (2 * $j)]);
                                    if (strlen($answer_text1) > 0 && strlen($answer_text2) > 0) {
                                        mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_text2, answer_true) VALUES(" . $question_id . ", " . ($j + 1) . ", '" . $answer_text1 . "', '" . $answer_text2 . "',1)");
                                    } else {
                                        break;
                                    }
                                }
                                if (mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $question_id . "")->num_rows == 0) {
                                    mysqli_query($con, "DELETE FROM questions WHERE id = '" . $question_id . "'");
                                    $add_counter--;
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }

            $excel_success = "<div class='alert alert-success' role='alert'>Успешно импортированных вопросов: " . $add_counter . "</div>";
        } else {
            $excel_error = "<div class='alert alert-danger' role='alert'>В таблице не хватает данных</div>";
        }
    } else {
        $excel_error = "<div class='alert alert-danger' role='alert'>Расширение файла должно быть .xls или .xlsx</div>";
    }
}

if (isset($_POST['newquestion'])) {
    $question_text = mysqli_real_escape_string($con, $_POST['question_text']);
    $question_comment = mysqli_real_escape_string($con, $_POST['comment_text']);
    $question_type = mysqli_real_escape_string($con, $_POST['question_type']);
    $test_id = mysqli_real_escape_string($con, $_POST['id']);
    if ($question_text == "") {
        $error = true;
        $errormsg = "Вопрос не может быть пустым";
    }
    if (!$error) {
        if ($question_comment == "") {
            if (mysqli_query($con, "INSERT INTO questions(test_id,type,text) VALUES(" . $test_id . ",'" . $question_type . "','" . $question_text . "')")) {
                $successmsg = "Вопрос добавлен";
            } else {
                $errormsg = "Ошибка добавления вопроса";
            }
        } else {
            if (mysqli_query($con, "INSERT INTO questions(test_id,type,text,comment) VALUES(" . $test_id . ",'" . $question_type . "','" . $question_text . "','" . $question_comment . "')")) {
                $successmsg = "Вопрос добавлен";
            } else {
                $errormsg = "Ошибка добавления вопроса";
            }
        }
        $question_id = mysqli_insert_id($con);
        if ($question_type == "order") {
            mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_text2,answer_true) VALUES(" . $question_id . ",1,'Текст ответа 1','Текст ответа 2',1)");
        } else {
            mysqli_query($con, "INSERT INTO answers(question_id,answer_number,answer_text,answer_true) VALUES(" . $question_id . ",1,'Текст ответа',1)");
        }
    }

}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Редактирование вопросов к тесту
        — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css"/>

    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/bootstrap.min.js"></script>
    <style>
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
    </style>
    <script type="text/javascript">

        function question(id) {

            var question_id = id;

            if (question_id !== "") {
                $("#question_id").html("<img src='library/ajax-loader.gif'/>");
                $.ajax({
                    type: "post",
                    url: "question.php",
                    "data": {
                        "question_id": question_id
                    },
                    success: function (data) {
                        var element = document.getElementById(id);
                        $(element).html(data);
                    }
                });
            }
        }

        function newquestion(t_id) {
            var q_text = document.getElementById("question_text_new");
            var q_comment = document.getElementById("comment_text_new");
            var q_type = document.getElementById("question_type_new");
            var q_comment_val = "";
            if (q_comment != null) {
                q_comment_val = q_comment.value;
            }
            $.post("question_operations/newquestion.php", {
                "id": t_id,
                "question_text": q_text.value,
                "question_type": q_type.value,
                "comment_text": q_comment_val
            }).done(function (data) {
                $('#new_questions').prepend('<div name="test_question" id="' + data + '"></div>');
                question(data);
            });
        }

        function oldquestion_delete(id) {
            $.post("question_operations/oldquestion_delete.php", {
                "question_id": id
            }).done(function (data) {
                var element = document.getElementById(id);
                $(element).html("");
            });
        }

        function oldquestion(id) {
            var text = document.getElementById(id + "question_text");
            var comment = document.getElementById(id + "comment_text");
            $.post("question_operations/oldquestion.php", {
                "question_id": id,
                "question_text": text.value,
                "comment_text": comment.value
            });
        }

        function addanswer(id, type) {
            var a_text = document.getElementById(id + "answer_text_new");
            var a_text2 = document.getElementById(id + "answer_text2_new");
            var a_text2_val = "";
            if (a_text2 != null) {
                a_text2_val = a_text2.value;
            }
            $.post("question_operations/addanswer.php", {
                "question_id": id,
                "question_type": type,
                "answer_text": a_text.value,
                "answer_text2": a_text2_val
            }).done(function (data) {
                question(id);
            });
        }

        function editanswer(q_id, q_type, a_id) {
            var a_text = document.getElementById(a_id + "answer_text");
            var a_text2 = document.getElementById(a_id + "answer_text2");
            var a_text2_val = "";
            if (a_text2 != null) {
                a_text2_val = a_text2.value;
            }
            $.post("question_operations/editanswer.php", {
                "question_type": q_type,
                "answer_id": a_id,
                "answer_text": a_text.value,
                "answer_text2": a_text2_val
            });
        }

        function deleteanswer(q_id, a_id, a_number) {
            $.post("question_operations/deleteanswer.php", {
                "question_id": q_id,
                "answer_id": a_id,
                "answer_number": a_number
            }).done(function (data) {
                question(q_id);
            });
        }

        function correctanswer(q_id, q_type, a_id) {
            $.post("question_operations/correctanswer.php", {
                "question_id": q_id,
                "question_type": q_type,
                "answer_id": a_id
            }).done(function (data) {
                question(q_id);
            });
        }

        function addtip(id) {
            var t_text = document.getElementById(id + "tip_text_new");
            $.post("question_operations/addtip.php", {
                "question_id": id,
                "tip_text": t_text.value
            }).done(function (data) {
                question(id);
            });
        }

        function edittip(q_id, t_id) {
            var t_text = document.getElementById(t_id + "tip_text");
            $.post("question_operations/edittip.php", {
                "tip_id": t_id,
                "tip_text": t_text.value
            });
        }

        function deletetip(q_id, t_id, t_number) {
            $.post("question_operations/deletetip.php", {
                "question_id": q_id,
                "tip_id": t_id,
                "tip_number": t_number
            }).done(function (data) {
                question(q_id);
            });
        }

        function editsubtheme(q_id) {
            var s_text = document.getElementById(q_id + "subtheme");
            $.post("question_operations/setsubtheme.php", {
                "question_id": q_id,
                "subtheme": s_text.value
            });
        }
        function editdifficulty(q_id) {
            var s_text = document.getElementById(q_id + "difficulty");
            $.post("question_operations/setdifficulty.php", {
                "question_id": q_id,
                "difficulty": s_text.value
            });
        }

        $(document).ready(function () {
            var x = document.getElementsByName("test_question");
            var i;
            for (i = 0; i < x.length; i++) {
                question(x[i].id)
            }
            $('a[name="preview-button"]').click(function () {

                var question_id = this.id;

                if (question_id != "") {
                    $("#preview").html("<img src='library/ajax-loader.gif'/>");
                    $.ajax({
                        type: "post",
                        url: "preview.php",
                        "data": {
                            "question_id": question_id
                        },
                        success: function (data) {
                            $("#preview").html(data);
                            MathJax.Hub.Queue(["Typeset", MathJax.Hub, 'preview']);
                        }
                    });
                }
            });


            $('head').append("<script async src=\"//e-tutor.ru/images/sdk/pup.js\"></sc" + "ript>");
        });

    </script>
<!--    <script async src="//e-tutor.ru/images/sdk/pup.js" data-url="//e-tutor.ru/images/upload" data-auto-insert="html-embed" data-palette="yellow"></script>-->
    <!--    <script async src=--><?php //echo "\"//" . $site_address . "/images/sdk/pup.js\"" ?><!-- data-url=--><?php //echo "\"https://" . $site_address . "/images/upload\"" ?><!-- data-auto-insert="html-embed"></script>-->
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

<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform"
      enctype="multipart/form-data">
    <div class="container">
        <div class="row">
            <div class="col-md-10 well">
                <legend>Редактирование вопросов к «<?php echo $test_name; ?>»</legend>
                <input type="hidden" value="<?php echo $test_id ?>" name="id" readonly="readonly" required
                       value="<?php if ($error) echo $test_id; ?>" class="form-control"/>
                <h4>Импорт вопросов из Excel-файла <a href="/template.xlsx" target="_blank">(образец)</a>:</h4>
                <div class="form-group">
                    <span><?php if (isset($excel_success)) {
                            echo $excel_success;
                        } ?></span>
                    <span><?php if (isset($excel_error)) {
                            echo $excel_error;
                        } ?></span>
                    <div class="row">
                        <div class="col-md-3">
                            <input type="file" id="upload_file" name="upload_file"/>
                        </div>
                        <div class="col-md-2">
                            <input type="submit" value="Импортировать" name='importexcel'
                                   class="btn btn-info btn-block">
                        </div>
                    </div>
                </div>
</form>
<hr/>
<h4>Новый вопрос:</h4>

<table class="table table-bordered">
    <tr>
        <td>
            <form role="form" action="<?php echo $_SERVER['PHP_SELF'] . '#place_newquestion'; ?>" method="post"
                  name="newquestionform" enctype="multipart/form-data">
                <fieldset>
                    <input type="hidden" value="<?php echo $test_id ?>" name="id" readonly="readonly" required
                           value="<?php if ($error) echo $test_id; ?>" class="form-control"/>
                    <div class="form-group">
                        <label for="question_text_new">Текст вопроса</label>
                        <textarea id="question_text_new" name="question_text_new" rows="1"
                                  placeholder="Введите текст вопроса" required class="form-control"></textarea>
                        <span class="text-danger"><?php if (isset($question_text_error)) echo $question_text_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="comment_text_new">Комментарий к вопросу (не обязательно)</label>
                        <textarea id="comment_text_new" name="comment_text_new" rows="2"
                                  placeholder="Будет показываться на странице результатов"
                                  class="form-control"></textarea>
                        <span class="text-danger"><?php if (isset($comment_text_error)) echo $comment_text_error; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="question_type_new">Тип вопроса</label><br>
                        <select id="question_type_new" name="question_type_new" style="width:100%;max-width:100%;">
                            <option value="radiobutton">Один ответ</option>
                            <option value="checkbox">Несколько ответов</option>
                            <option value="input">Свободный ввод</option>
                            <option value="order">Указание соответствия</option>
                        </select>
                        <span class="text-danger"><?php if (isset($user_group_error)) echo $user_group_error; ?></span>
                    </div>

                    <div class="form-group">
                        <a name="newquestion" onclick="newquestion('<?php echo $test_id; ?>')" class="btn btn-primary">Добавить</a>
                    </div>
                </fieldset>
            </form>
        </td>
    </tr>

</table>
<?php
$result_select = mysqli_query($con, "SELECT * FROM questions WHERE test_id = " . $test_id . " ORDER BY id DESC");
//if (($result_select = mysqli_query($con, "SELECT * FROM questions WHERE test_id = " . $test_id . " ORDER BY id DESC"))->num_rows > 0) {
    ?>
    <hr/>
    <a name="place_newquestion"></a>
    <h4>Редактирование вопросов:</h4>

    <div name="new_questions" id="new_questions"></div>
    <?php
    while ($questions_list = mysqli_fetch_object($result_select)) {
        echo "<div name='test_question' id='" . $questions_list->id . "'></div>";

    }
//}
?>
</div>
<div class="col-md-2">
    <a href="/" class="btn btn-primary btn-block">Вернуться на главную</a>
    <a href="editTest.php?id=<?php echo $test_id ?>" class="btn btn-info btn-block">Редактировать</a>
    <input type="submit" name="deletetest" value="Удалить тест" class="btn btn-danger btn-block"/>
</div>
</div>


</div>

<!-- Mark up for Popups -->

<div id="popup1" style="display: none; width: 650px; height: 500px; overflow: auto">
    <div rel="title">
        Предпросмотр
    </div>
    <div rel="body">
        <div style="padding: 10px; font-size: 11px; line-height: 150%;">

            <div id="preview"></div>

        </div>
    </div>
</div>


<div id="test"></div>
<link rel="stylesheet" type="text/css" href="../css/w2ui-1.5.rc1.min.css"/>
<script type="text/javascript" src="../js/w2ui-1.5.rc1.min.js"></script>

<script type="text/javascript" src="/MathJax/MathJax.js?config=default"></script>

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

