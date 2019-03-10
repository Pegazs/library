<?php
include_once '../dbconnect.php';
session_start();
// Check connection
if ($con === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Escape user inputs for security
$id = mysqli_real_escape_string($con, $_REQUEST['id']);


if (!empty($_REQUEST['question_id'])) {
    $question_id = mysqli_real_escape_string($con, $_REQUEST['question_id']);
}

if (isset($id)) {

    $queryM = "SELECT * FROM sections WHERE id = " . $id;
    $resultM = mysqli_query($con, $queryM);
    $rowM = mysqli_fetch_array($resultM);
    $type = $rowM['type'];
    $name = $rowM['name'];

    if ($type == "discipline") {
        $typeName = "дисциплина";
        $typeNameRod = "дисциплины";
    } else if ($type == "supersection") {
        $typeName = "раздел";
        $typeNameRod = "раздела";
    } else if ($type == "section") {
        $typeName = "подраздел";
        $typeNameRod = "подраздела";
    } else if ($type == "theme") {
        $typeName = "тема";
        $typeNameRod = "темы";
    }

    if (isset($question_id)) {
        $query = "SELECT DISTINCT z.*
FROM (SELECT
        p.id,
        p.page_number,
        p.book_id,
        b.name,
        b.authors,
        b.file_name,
        (
          (SELECT (avg(qs1.correct))
           FROM questions_session qs1
             JOIN sessions s1 ON qs1.session_id = s1.id
           WHERE qs1.question_id = " . $question_id . " AND exists(SELECT DISTINCT
                                                             pc1.user_id,
                                                             pc1.question_id,
                                                             pc1.session_id,
                                                             pc1.page_id
                                                           FROM page_clicked pc1
                                                           WHERE pc1.question_id = " . $question_id . " and pc1.session_id=s1.id and p.id = pc1.page_id))
          +
          (SELECT avg(s2.result_percent)
           FROM sessions s2
           WHERE exists(SELECT DISTINCT
                          pc2.user_id,
                          pc2.question_id,
                          pc2.session_id,
                          pc2.page_id
                        FROM page_clicked pc2
                        WHERE pc2.question_id = " . $question_id . " AND pc2.session_id = s2.id and p.id = pc2.page_id))
          +
          (SELECT avg(s3.result_percent) AS avg_users
           FROM sessions s3
             JOIN users u3 ON s3.user_id = u3.id
           WHERE s3.result_percent IS NOT NULL AND exists(SELECT DISTINCT
                                                            pc3.user_id,
                                                            pc3.question_id,
                                                            pc3.session_id,
                                                            pc3.page_id
                                                          FROM page_clicked pc3
                                                          WHERE
                                                            pc3.question_id = " . $question_id . " AND pc3.user_id = u3.id AND
                                                            p.id = pc3.page_id
           )
          )
        ) AS good
      FROM books b
        JOIN pages p ON b.id = p.book_id
        JOIN page_clicked pc ON pc.page_id = p.id
      WHERE pc.question_id = " . $question_id . ") z
WHERE z.good IS NOT NULL
ORDER BY z.good DESC
LIMIT 3
";
        $result = mysqli_query($con, $query);
        $found = mysqli_num_rows($result);
        if ($found > 0) {
            echo "<h4>Рекомендованные материалы:</h4>";
            while ($row = mysqli_fetch_array($result)) {
                $entryRu = mb_convert_encoding($row['file_name'], "UTF8", "Windows-1251");
                echo "<li class='link'><a target='_blank' onclick='pageClicked($row[id])' href=/library/books/$entryRu>$row[name]</a>";
                if (strlen($row['authors'])) {
                    echo " ($row[authors])";
                }
                echo ", страница $row[page_number]</li>";
            }
            echo "<br>";
        }

    }

    $query = "SELECT * FROM sections_books s, books b WHERE s.section_id = " . $id . " AND s.book_id = b.id ORDER BY b.name";
    $result = mysqli_query($con, $query);
    $found = mysqli_num_rows($result);

    if ($found > 0) {
        echo "<h4>Список книг для $typeNameRod «";
        echo $name;
        echo "» из <a href='/library/books.php' target='_blank'>библиотеки</a>:</h4>";
        while ($row = mysqli_fetch_array($result)) {
            $entryRu = mb_convert_encoding($row['file_name'], "UTF8", "Windows-1251");
            echo "<li class='link'><a target='_blank' href=/library/books/$entryRu>$row[name]</a>";
            if (strlen($row['authors'])) {
                echo " ($row[authors])";
            }
            if ($_SESSION['usr_role'] == 'teacher' OR $_SESSION['usr_role'] == 'admin') {
                echo " <a nohref style=\"cursor:pointer;color:#CC0000\" onclick='delete_book($id, $row[id])'>[открепить]</a>";
                echo " (точные страницы: ";
                echo "<input type=\"text\" style=\"width: 40px\" id=\"start_page_";
                echo $row['book_id'];
                echo "\" oninput=\"start_page('";
                echo $row['book_id'];
                echo "');\" value='";
                echo $row['start_page'];
                echo "'>";
                echo " – ";
                echo "<input type=\"text\" style=\"width: 40px\" id=\"end_page_";
                echo $row['book_id'];
                echo "\" oninput=\"end_page('";
                echo $row['book_id'];
                echo "');\" value='";
                echo $row['end_page'];
                echo "'>";
                echo ")";
            }
        }
        echo "<br>";
    }
    if ($_SESSION['usr_role'] == 'teacher' OR $_SESSION['usr_role'] == 'admin') {
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