<?php
session_start();
if (empty($_SESSION['usr_id'])) {
    header("Location: ../index.php");
}
include_once 'dbconnectLib.php';
include_once '../dbconnect.php';
header('X-Frame-Options: GOFORIT');
$keywords = $_POST["title"];
$filter = $_POST["filter"];
//$keywords=str_replace(" ", "+", $keywords);
//
//$baseurl="https://duckduckgo.com/?q=";
//
//$appendix_before_percent = "+\"Загрузить+полный+текст\"";
//$appendix_percent = "";
//for ($i = 1; $i <= $percent; $i++) {
//    $appendix_percent.= (("+\"рейтинге+по+направлению%3A+".$i."\""));
//}
//$appendix_after_percent = "+site%3Aelibrary.ru";
//$appendix_after_percent0 = "\"";
//
//
//
//$fullurl = $baseurl.$keywords.$appendix_before_percent.$appendix_percent.$appendix_after_percent;

//echo "<iframe src=$fullurl id=\"iframe\" target=\"_blank\" frameborder=\"0\" style=\"position: relative; top:-200px; overflow:hidden;height:800px;width:102.5%\"></iframe>";


$keywords = mysqli_real_escape_string($conLib, $keywords);

$keywords = str_replace('-', ' ', $keywords); //TODO: "-word" case fix


switch ($filter) {
    case "all":
        $query = "SELECT * FROM pages, books WHERE books.id=pages.book_id AND MATCH(pages.text) AGAINST ('$keywords' IN BOOLEAN MODE) AND books.id IN (71, 72, 88, 73, 89, 86, 87)";
        break;
    case "index-double-check":
        $query = "SELECT DISTINCT pages.*, books.* FROM pages, books, books_index WHERE books_index.book_id=books.id AND books.id=pages.book_id AND MATCH(pages.text) AGAINST ('$keywords' IN BOOLEAN MODE) AND books.id IN (71, 72, 88, 73, 89, 86, 87) AND pages.page_number IN (SELECT books_index.page_number FROM books_index WHERE books_index.book_id=books.id AND MATCH(books_index.text) AGAINST ('$keywords' IN BOOLEAN MODE))";
        break;
    case "index-simple":
        $keywords = str_replace('[^0-9a-zA-Z_\s]', '', $keywords);
        $keywords = trim($keywords);
        $keywords = str_replace('~ +~', ' ', $keywords);
        $keywords = str_replace(' ', ' +', $keywords);
        $keywords = "+" . $keywords;
        $query = "SELECT DISTINCT pages.*, books.* FROM pages, books, books_index WHERE books_index.book_id=books.id AND books.id=pages.book_id AND books.id IN (71, 72, 88, 73, 89, 86, 87) AND pages.page_number IN (SELECT books_index.page_number FROM books_index WHERE books_index.book_id=books.id AND MATCH(books_index.text) AGAINST ('$keywords' IN BOOLEAN MODE))";
        break;
    case "combined":
        $keywordsIndex = str_replace('[^0-9a-zA-Z_\s]', '', $keywords);
        $keywordsIndex = trim($keywordsIndex);
        $keywordsIndex = str_replace('~ +~', ' ', $keywordsIndex);
        $keywordsIndex = str_replace(' ', ' +', $keywordsIndex);
        $keywordsIndex = "+" . $keywordsIndex;
        $query = "SELECT DISTINCT pages.*, books.* FROM pages, books, books_index WHERE books_index.book_id=books.id AND books.id=pages.book_id AND books.id IN (71, 72, 88, 73, 89, 86, 87) AND pages.page_number IN (SELECT books_index.page_number FROM books_index WHERE books_index.book_id=books.id AND MATCH(books_index.text) AGAINST ('$keywordsIndex' IN BOOLEAN MODE))";
        break;
    case "best-one":
    default:
        $query = "SELECT * FROM books, (SELECT *, max(score) FROM (SELECT *, MATCH(pages.text) AGAINST ('$keywords' IN BOOLEAN MODE) score FROM pages WHERE MATCH(pages.text) AGAINST ('$keywords' IN BOOLEAN MODE)) AS dataScore group by book_id) as dataFull WHERE books.id=book_id AND books.id IN (71, 72, 88, 73, 89, 86, 87) ORDER BY score desc";
        break;
}
//$query = "SELECT * FROM pages, books WHERE books.id=pages.book_id AND MATCH(text) AGAINST ('$keywords')";
//$query = "SELECT *, FROM pages, books WHERE books.id=pages.book_id AND MATCH(text) AGAINST ('$keywords' IN BOOLEAN MODE)";
mysqli_query($conLib, "SET sql_mode = ''");
$result = mysqli_query($conLib, $query);

if ($result) {

//    echo $keywords . "     ";

    $found = mysqli_num_rows($result);

    if ($found > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $entryRu = mb_convert_encoding($row['file_name'], "UTF8", "Windows-1251");
            echo "<li class='link'><a target='_blank' href=/library/books/$entryRu#page=$row[page_number]>$row[name]</a>";
            if (strlen($row['authors'])) {
                echo "  ($row[authors])";
            }
            echo ", страница $row[page_number]</li>";
        }
    } else {
        if ($filter == "combined") {
            $resultAlt = mysqli_query($conLib, "SELECT * FROM books, (SELECT *, max(score) FROM (SELECT *, MATCH(pages.text) AGAINST ('$keywords' IN BOOLEAN MODE) score FROM pages WHERE MATCH(pages.text) AGAINST ('$keywords' IN BOOLEAN MODE)) AS dataScore group by book_id) as dataFull WHERE books.id=book_id AND books.id IN (71, 72, 88, 73, 89, 86, 87) ORDER BY score desc");
            if ($resultAlt) {
                $foundAlt = mysqli_num_rows($resultAlt);
                if ($foundAlt > 0) {
                    while ($row = mysqli_fetch_array($resultAlt)) {
                        $entryRu = mb_convert_encoding($row['file_name'], "UTF8", "Windows-1251");
                        echo "<li class='link'><a target='_blank' href=/library/books/$entryRu#page=$row[page_number]>$row[name]</a>";
                        if (strlen($row['authors'])) {
                            echo "  ($row[authors])";
                        }
                        echo ", страница $row[page_number]</li>";
                    }
                } else {
                    echo "<li>Соответствий не найдено</li>";
                }
            } else {
                echo "<li>Ошибка при обработке запроса</li>";
            }
        } else {
            echo "<li>Соответствий не найдено</li>";
        }
    }
} else {
    echo "<li>Ошибка при обработке запроса</li>";
}
// ajax search
?>