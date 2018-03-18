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
        $typeName = "дисциплина";
        $typeNameRod = "дисциплины";
    } else if ($type == "section") {
        $typeName = "раздел";
        $typeNameRod = "раздела";
    } else if ($type == "theme") {
        $typeName = "тема";
        $typeNameRod = "темы";
    }


    echo "<hr/>";
    echo "<h4>Список книг для $typeNameRod «";
    echo $name;
    echo "» из <a href='/library/books.php' target='_blank'>библиотеки</a>:</h4>";

    $query = "SELECT * FROM sections_books, books WHERE section_id = " . $id . " AND book_id = id";
    $result = mysqli_query($con, $query);
    $found = mysqli_num_rows($result);

    if ($found > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $entryRu = mb_convert_encoding($row['file_name'], "UTF8", "Windows-1251");
            echo "<li class='link'><a target='_blank' href=/library/books/$entryRu>$row[name]</a>";
            if (strlen($row['authors'])) {
                echo " ($row[authors])";
            }
            echo " <a nohref style=\"cursor:pointer;color:#CC0000\" onclick='delete_book($id, $row[id])'>[открепить]</a>";
            echo " (точные страницы: ";
            echo "<input type=\"text\" style=\"width: 40px\" id=\"start_page_";
            echo $row['section_id'];
            echo "\" oninput=\"start_page('";
            echo $row['section_id'];
            echo "');\" value='";
            echo $row['start_page'];
            echo "'>";
            echo " – ";
            echo "<input type=\"text\" style=\"width: 40px\" id=\"end_page_";
            echo $row['section_id'];
            echo "\" oninput=\"end_page('";
            echo $row['section_id'];
            echo "');\" value='";
            echo $row['end_page'];
            echo "'>";
            echo ")";
        }
        echo "<br>";
    }
    echo "<b>Прикрепить книгу:</b><br>";
    echo "<div class=\"row\">
                <div class=\"col-sm-12\">
                    <div class=\"search-box\" id=\"search-box-library\">
                        <input width=\"100%\" type=\"text\" id=\"library-name\" autocomplete=\"off\"
                               placeholder=\"Введите название для поиска\"/>
                        <div class=\"result\" id=\"result-library\"></div>
                    </div>
                </div>
            </div>";
}

// close connection
mysqli_close($con);
?>


<script type="text/javascript">
    $(document).ready(function () {

        $("#library-name").on("keyup input", function () {
            /* Get input value on change */
            var inputVal = $(this).val();
            var resultDropdown = $(this).siblings(".result");
            if (inputVal.length) {
                $.get("section_operations/library-search.php", {term: inputVal}).done(function (data) {
                    // Display the returned data in browser
                    resultDropdown.html(data);
                });
            } else {
                resultDropdown.empty();
            }
        });

        // Set search input value on click of result item
        $(document).on("click", "#result-library p", function () {
            $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
            add_book($(this).text());
            $(this).parent(".result").empty();
        });
    });

</script>