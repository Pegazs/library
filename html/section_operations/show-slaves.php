<?php
include_once '../dbconnect.php';

// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Escape user inputs for security
$id = mysqli_real_escape_string($con, $_REQUEST['id']);
if (isset($id)) {

    $queryM = "SELECT * FROM sections WHERE id = " . $id;
    $resultM = mysqli_query($con, $queryM);
    $rowM = mysqli_fetch_array($resultM);
    $type = $rowM['type'];
    $name = $rowM['name'];

    if ($type == "discipline") {
        $typeNameRod = "дисциплины";
    } else if ($type == "section") {
        $typeNameRod = "раздела";
    } else if ($type == "theme") {
        $typeNameRod = "темы";
    }

    echo "<hr/>";
    echo "<h4>Список зависимостей для $typeNameRod «";
    echo $name;
    echo "»:</h4>";


    $query = "SELECT * FROM sections_hierarchy, sections WHERE id_master = " . $id . " AND id_slave = id ORDER BY slave_number";
    $result = mysqli_query($con, $query);
    $found = mysqli_num_rows($result);

    if ($found > 0) {
        echo "<ol>";
        while ($row = mysqli_fetch_array($result)) {
            if ($row['type'] == "discipline") {
                $typeName = "Дисциплина";
                $typeNameRod = "дисциплины";
            } else if ($row['type'] == "section") {
                $typeName = "Раздел";
                $typeNameRod = "раздела";
            } else if ($row['type'] == "theme") {
                $typeName = "Тема";
                $typeNameRod = "темы";
            }
            echo "<li>";
            if ($row['slave_number'] > 1) {
                echo "<a nohref style=\"cursor:pointer;color:#003399;\" onclick='slave_up($id, $row[id_slave])'>↑</a>&nbsp;";
            } else {
                echo "&nbsp;&nbsp;&nbsp;";
            }
            if ($row['slave_number'] != $found) {
                echo "<a nohref style=\"cursor:pointer;color:#003399;\" onclick='slave_down($id, $row[id_slave])'>↓</a>&nbsp;";
            } else {
                echo "&nbsp;&nbsp;&nbsp;";
            }
            echo "<b>$typeName:</b> <a nohref style=\"cursor:pointer;color:#6699CC\" onclick=\"go_to($row[id_slave], ";
            echo "'";
            echo $row['name'];
            echo "', '" ;
            echo $row['type'];
            echo "')\">$row[name]</a>";
            echo " <a nohref style=\"cursor:pointer;color:#CC0000;\" onclick='delete_slave($id, $row[id_slave])'>[открепить]</a></li>";
        }
        echo "</ol>";
        echo "<br>";
    }

    if ($type != "theme") {
        echo "<b>Добавить зависимость:</b><br>";
        echo "<div class=\"row\">
                <div class=\"col-sm-2\">
                    <select id=\"slave-type\" class=\"form-control\">";
        if ($type == "discipline") {
            echo "<option value=\"discipline\">Дисциплина</option>
                    <option value=\"section\">Раздел</option>";
        }
        echo "<option value=\"theme\" selected>Тема</option>
                    </select>
                </div>
                <div class=\"col-sm-10\">
                    <div class=\"search-box\" id=\"search-box-slave\">
                        <input width=\"100%\" type=\"text\" id=\"slave-name\" autocomplete=\"off\"
                               placeholder=\"Введите название для поиска или добавления\"/>
                        <div class=\"result\" id=\"result-slave\"></div>
                    </div>
                </div>
            </div>";
    }
}

// close connection
mysqli_close($con);
?>

<script type="text/javascript">
    $(document).ready(function () {

        $("#slave-name").on("keyup input", function () {
            /* Get input value on change */
            var inputVal = $(this).val();
            var type = document.getElementById("slave-type").value;
            var resultDropdown = $(this).siblings(".result");
            if (inputVal.length) {
                $.get("section_operations/slave-search.php", {term: inputVal, type: type}).done(function (data) {
                    // Display the returned data in browser
                    resultDropdown.html(data);
                });
            } else {
                resultDropdown.empty();
            }
        });

        // Set search input value on click of result item
        $(document).on("click", "#result-slave p", function () {
            $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
            add_slave($(this).text(), document.getElementById("slave-type").value);
            $(this).parent(".result").empty();
        });
    });

</script>
