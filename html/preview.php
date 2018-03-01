<?php
session_start();
if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
    header("Location: index.php");
}
include_once 'dbconnect.php';
header('X-Frame-Options: GOFORIT');
$question_id=$_POST["question_id"];
$question_id = mysqli_real_escape_string($con , $question_id);

$query = "SELECT * FROM questions WHERE id =" . $question_id;
$result_select = mysqli_query($con , $query);


?>




<?php

    while($questions=mysqli_fetch_object($result_select)){
        $question_type = $questions->type;
        $question_id = $questions->id;
        $answers_select = mysqli_query($con, "SELECT * from answers WHERE question_id = " . ($questions->id)." ORDER BY RAND()");
?>
<table class="table table-bordered">
    <tr bgcolor=#ffffff>
        <td colspan = "2">
            <b>Вопрос:</b> <?php echo ($questions->text) ?>
    </td>
    </tr>
    <?php
									switch ($question_type) {
                                        case "radiobutton":
                                            for ($i = 1; $answers = mysqli_fetch_object($answers_select) ; $i++) {
                                                $answer_num = $answers->answer_number;
                                                ?>
                                                <tr bgcolor=#fafafa>
                                                <td>
                                                <input type="radio" name="answer" <?php if ($i == 1) {echo 'checked';} ?> value="<?php echo $answer_num; ?>">
                                                <?php
                                                echo ($answers->answer_text) . "</td></tr>";
                                            }
                                            break;
                                        case "checkbox":
                                            for ($i = 1; $answers = mysqli_fetch_object($answers_select) ; $i++) {
                                                $answer_num = $answers->answer_number;
                                                ?>
                                                <tr bgcolor=#fafafa>
                                                <td>
                                                <input type="checkbox" name="<?php echo 'checkbox'.$answer_num; ?>">
                                                <?php
                                                echo ($answers->answer_text) . "</td></tr>";
                                            }
                                            break;
                                        case "input":
                                            $answers = mysqli_fetch_object($answers_select)
                                            ?>
                                            <tr bgcolor=#fafafa>
                                                <td>
                                                    <input type="text" autocomplete="off" name="input_answer" class="form-control" />
                                                </td>
                                            </tr>
                                            <?php
                                            break;
                                        case "order":
                                            $answers_select2 = mysqli_query($con, "SELECT * from answers WHERE question_id = " . ($questions->id)." ORDER BY RAND()");
                                            $options = "";
                                            for ($i = 1; $answers = mysqli_fetch_object($answers_select2) ; $i++) {
                                                $options .= "<option value='".($answers->answer_number)."'>".($answers->answer_text2)."</option>";
                                            }
                                            mysqli_data_seek($answers_select, 0);
                                            for ($i = 1; $answers = mysqli_fetch_object($answers_select) ; $i++) {
                                                $answer_num = $answers->answer_number;
                                                ?>

                                                <tr bgcolor=#fafafa>
                                                    <td>
                                                        <?php
                                                        echo ($answers->answer_text);
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <select name="<?php echo 'order'.md5(($answers->answer_number)*$session_id); ?>" style="width:100%;max-width:100%;">
                                                            <?php
                                                            echo $options;
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <?php
                                            }
                                            break;
                                    }
                                mysqli_data_seek($answers_select, 0);



        ?>
        </table>
    <?php

    }


// ajax search
?>

