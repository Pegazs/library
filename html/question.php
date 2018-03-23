<?php
session_start();
include_once 'dbconnect.php';

if (empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}

$question_id = mysqli_real_escape_string($con, $_POST["question_id"]);

?>
<script type="text/javascript">
    $(document).ready(function () {
        $('a[name="preview-button"]').click(function () {

            var question_id = this.id;

            if (question_id != "") {
                $("#preview").html("<img src='library/ajax-loader.gif'/>");
                $.ajax({
                    type: "post",
                    url: "preview.php",
                    "data": {
                        "question_id": question_id,
                    },
                    success: function (data) {
                        $("#preview").html(data);
                        MathJax.Hub.Queue(["Typeset", MathJax.Hub, 'preview']);
                    }
                });
            }
        });
    });
</script>

<?php
if (($result_select = mysqli_query($con, "SELECT * FROM questions WHERE id = " . $question_id))->num_rows > 0) {
    while ($questions_list = mysqli_fetch_object($result_select)) {
        echo "<a name='" . $question_id . "'></a>";
        ?>
        <hr/>
        <hr/>
        <hr/>
        <table class="table table-bordered">
            <tr>
                <td>
                    <fieldset>

                        <div class="form-group">
                            <label for="question_text">Текст вопроса</label>
                            <textarea name="question_text" id="<?php echo $questions_list->id ?>question_text" rows="2"
                                      placeholder="Введите текст вопроса"
                                      oninput="oldquestion('<?php echo $questions_list->id; ?>')"
                                      class="form-control"><?php echo $questions_list->text; ?></textarea>
                            <span class="text-danger"><?php if (isset($question_text_error)) echo $question_text_error; ?></span>
                        </div>

                        <div class="form-group">
                            <label for="comment_text">Комментарий к вопросу (не обязательно)</label>
                            <textarea name="comment_text" id="<?php echo $questions_list->id ?>comment_text" rows="1"
                                      oninput="oldquestion('<?php echo $questions_list->id; ?>')"
                                      placeholder="Будет показываться на странице результатов"
                                      class="form-control"><?php echo $questions_list->comment; ?></textarea>
                            <span class="text-danger"><?php if (isset($comment_text_error)) echo $comment_text_error; ?></span>
                        </div>

                        <div class="form-group">
                            <label for="question_type">Тип вопроса:</label> <?php
                            switch ($questions_list->type) {
                                case "radiobutton":
                                    echo "Один ответ";
                                    break;
                                case "checkbox":
                                    echo "Несколько ответов";
                                    break;
                                case "input":
                                    echo "Свободный ввод";
                                    break;
                                case "order":
                                    echo "Указание соответствия";
                                    break;
                            }
                            ?>
                        </div>

                        <div class="form-group">
                            <a name="preview-button" id="<?php echo $questions_list->id ?>"
                               onclick="$('#popup1').w2popup()" class="btn btn-info">Предпросмотр</a>
                            <a name="oldquestion_delete"
                               onclick="oldquestion_delete('<?php echo $questions_list->id; ?>')"
                               class="btn btn-danger">Удалить</a>
                        </div>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <td>


                    <?php
                    switch ($questions_list->type) {
                        case "radiobutton":
                            echo "<b>Ответы:</b>";
                            $result_select_answer = mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $questions_list->id . " ORDER BY answer_number");
                            while ($answer_object = mysqli_fetch_object($result_select_answer)) {
                                ?>
                                <fieldset>
                                    <hr/>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <?php if ($answer_object->answer_true == 0) { ?>
                                                <span class="input-group-addon danger"><span
                                                            class="glyphicon glyphicon-remove"></span></span>
                                            <?php } else { ?>
                                                <span class="input-group-addon success"><span
                                                            class="glyphicon glyphicon-ok"></span></span>
                                            <?php } ?>
                                            <input type="text" id="<?php echo $answer_object->id ?>answer_text"
                                                   name="answer_text"
                                                   oninput="editanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                                   placeholder="Введите текст ответа"
                                                   value="<?php echo $answer_object->answer_text; ?>"
                                                   class="form-control form-control-success"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 input-group">
                                        <?php if ($answer_object->answer_true == 0) { ?>
                                            <a name="correctanswer"
                                               onclick="correctanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                               class="btn btn-sm btn-info">Правил.</a>
                                        <?php } ?>
                                        <?php if ($answer_object->answer_true == 0) { ?>
                                            <a name="deleteanswer"
                                               onclick="deleteanswer('<?php echo $questions_list->id; ?>', '<?php echo $answer_object->id; ?>', '<?php echo $answer_object->answer_number; ?>')"
                                               class="btn btn-sm btn-danger">Уд.</a>
                                        <?php } ?>
                                    </div>
                                </fieldset>

                                <?php
                            }
                            ?>
                            <fieldset>
                                <hr/>
                                <div class="col-sm-9">
                                    <input type="text" id="<?php echo $questions_list->id ?>answer_text_new"
                                           name="answer_text_new" placeholder="Введите текст ответа"
                                           class="form-control form-control-success"/>
                                </div>

                                <div class="col-sm-3 input-group">
                                    <a name="addanswer"
                                       onclick="addanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>')"
                                       class="btn btn-sm btn-success">Добавить</a>
                                </div>
                            </fieldset>

                            <?php
                            break;
                        case "checkbox":
                            echo "<b>Ответы:</b>";
                            $result_select_answer = mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $questions_list->id . " ORDER BY answer_number");
                            while ($answer_object = mysqli_fetch_object($result_select_answer)) {
                                ?>
                                <fieldset>
                                    <hr/>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <?php if ($answer_object->answer_true == 0) { ?>
                                                <span class="input-group-addon danger"><span
                                                            class="glyphicon glyphicon-remove"></span></span>
                                            <?php } else { ?>
                                                <span class="input-group-addon success"><span
                                                            class="glyphicon glyphicon-ok"></span></span>
                                            <?php } ?>
                                            <input type="text" id="<?php echo $answer_object->id ?>answer_text"
                                                   name="answer_text"
                                                   oninput="editanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                                   placeholder="Введите текст ответа"
                                                   value="<?php echo $answer_object->answer_text; ?>"
                                                   class="form-control form-control-success"/>
                                        </div>
                                    </div>

                                    <div class="col-sm-3 input-group">
                                        <a name="correctanswer"
                                           onclick="correctanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                           class="btn btn-sm btn-info">Правил.</a>
                                        <?php if ($result_select_answer->num_rows > 1) { ?>
                                            <a name="deleteanswer"
                                               onclick="deleteanswer('<?php echo $questions_list->id; ?>', '<?php echo $answer_object->id; ?>', '<?php echo $answer_object->answer_number; ?>')"
                                               class="btn btn-sm btn-danger">Уд.</a>
                                        <?php } ?>
                                    </div>
                                </fieldset>

                                <?php
                            }
                            ?>
                            <fieldset>
                                <hr/>
                                <div class="col-sm-9">
                                    <input type="text" id="<?php echo $answer_object->id ?>answer_text_new"
                                           name="answer_text_new" placeholder="Введите текст ответа"
                                           class="form-control form-control-success"/>
                                </div>

                                <div class="col-sm-3 input-group">
                                    <a name="addanswer"
                                       onclick="addanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>')"
                                       class="btn btn-sm btn-success">Добавить</a>
                                </div>
                            </fieldset>

                            <?php
                            break;
                        case "input":
                            $result_select_answer = mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $questions_list->id . " ORDER BY answer_number");
                            $input_answer = mysqli_fetch_object($result_select_answer);
                            ?>
                            <b>Ответ:</b>
                            <fieldset>
                                <hr/>
                                <div class="col-sm-12">
                                    <input type="text" id="<?php echo $questions_list->id ?>answer_text_new"
                                           name="answer_text_new" placeholder="Введите текст ответа"
                                           oninput="editanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                           value="<?php echo $input_answer->answer_text; ?>"
                                           class="form-control form-control-success"/>
                                </div>
                            </fieldset>
                            <?php

                            break;
                        case "order":
                            echo "<b>Пары ответов:</b>";
                            $result_select_answer = mysqli_query($con, "SELECT * FROM answers WHERE question_id = " . $questions_list->id . " ORDER BY answer_number");
                            while ($answer_object = mysqli_fetch_object($result_select_answer)) {
                                ?>
                                <fieldset>
                                    <hr/>
                                    <div class="col-sm-5">
                                        <input type="text" id="<?php echo $answer_object->id ?>answer_text"
                                               name="answer_text"
                                               oninput="editanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                               placeholder="Введите текст ответа"
                                               required value="<?php echo $answer_object->answer_text; ?>"
                                               class="form-control form-control-success"/>
                                    </div>

                                    <div class="col-sm-5">
                                        <input type="text" id="<?php echo $answer_object->id ?>answer_text2"
                                               name="answer_text2"
                                               oninput="editanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>', '<?php echo $answer_object->id; ?>')"
                                               placeholder="Введите текст соответствия"
                                               required value="<?php echo $answer_object->answer_text2; ?>"
                                               class="form-control form-control-success"/>
                                    </div>
                                    <div class="col-sm-2 input-group">
                                        <?php if ($result_select_answer->num_rows > 1) { ?>
                                            <a name="deleteanswer"
                                               onclick="deleteanswer('<?php echo $questions_list->id; ?>', '<?php echo $answer_object->id; ?>', '<?php echo $answer_object->answer_number; ?>')"
                                               class="btn btn-sm btn-danger">"Уд."</a>
                                        <?php } ?>
                                    </div>
                                </fieldset>

                                <?php
                            }
                            ?>
                            <fieldset>
                                <hr/>
                                <div class="col-sm-5">
                                    <input type="text" id="<?php echo $questions_list->id ?>answer_text_new"
                                           name="answer_text_new" placeholder="Введите текст ответа"
                                           class="form-control form-control-success"/>
                                </div>

                                <div class="col-sm-5">
                                    <input type="text" id="<?php echo $questions_list->id ?>answer_text2_new"
                                           name="answer_text2_new"
                                           placeholder="Введите текст соответствия"
                                           required class="form-control form-control-success"/>
                                </div>
                                <div class="col-sm-2 input-group">
                                    <a name="addanswer"
                                       onclick="addanswer('<?php echo $questions_list->id; ?>', '<?php echo $questions_list->type; ?>')"
                                       class="btn btn-sm btn-success">Добавить</a>
                                </div>
                            </fieldset>

                            <?php
                            break;
                    }


                    ?>
                    <hr/>
                    <?php


                    echo "<b>Подсказки:</b>";
                    $result_select_tip = mysqli_query($con, "SELECT * FROM tips WHERE question_id = " . $questions_list->id . " ORDER BY tip_number");
                    while ($tip_object = mysqli_fetch_object($result_select_tip)) {
                        ?>
                        <fieldset>
                            <hr/>
                            <div class="col-sm-10 ">
                                <input type="text" id="<?php echo $tip_object->id ?>tip_text" name="tip_text"
                                       oninput="edittip('<?php echo $questions_list->id; ?>', '<?php echo $tip_object->id; ?>')"
                                       placeholder="Введите текст подсказки"
                                       value="<?php echo $tip_object->tip_text; ?>"
                                       class="form-control form-control-success"/>
                            </div>
                            <div class="col-sm-2 input-group">
                                <a name="deletetip"
                                   onclick="deletetip('<?php echo $questions_list->id; ?>', '<?php echo $tip_object->id; ?>', '<?php echo $tip_object->tip_number; ?>')"
                                   class="btn btn-sm btn-danger">Уд.</a>
                            </div>
                        </fieldset>

                        <?php
                    }
                    ?>
                    <fieldset>
                        <hr/>
                        <div class="col-sm-10">
                            <input type="text" id="<?php echo $questions_list->id ?>tip_text_new" name="tip_text_new"
                                   placeholder="Введите текст подсказки"
                                   class="form-control form-control-success"/>
                        </div>

                        <div class="col-sm-2 input-group">
                            <a name="addtip" onclick="addtip('<?php echo $questions_list->id; ?>')"
                               class="btn btn-sm btn-success">Добавить</a>
                        </div>
                    </fieldset>
                </td>
            </tr>
        </table>

        <?php
    }
}
?>
