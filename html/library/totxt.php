<?php

$conLib = mysqli_connect("localhost", "libraryUser", "L1BR@ry", "library") or die("Error " . mysqli_error($conLib));
mysqli_query($conLib , "SET NAMES utf8");

mysqli_query($conLib , "delete from books");
mysqli_query($conLib , "delete from pages");

// Make a function for convenience
function getPDFPages($document)
{
    $cmd = "/var/www/html/library/pdfinfo";

    // Parse entire output
    // Surround with double quotes if file name has spaces
    exec("$cmd \"pdf/$document\"", $output);

    // Iterate through lines
    $pagecount = 0;
    foreach($output as $op)
    {
        // Extract the number
        if(preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1)
        {
            $pagecount = intval($matches[1]);
            break;
        }
    }

    return $pagecount;
}

if ($handle = opendir('books')) {

    while (false !== ($entry = readdir($handle))) {

        if ($entry != "." && $entry != "..") {
            $entryRu = mb_convert_encoding($entry, "UTF8", "Windows-1251");
            $name = stristr($entryRu, ".", true);
            $query = "INSERT INTO books(file_name, name) VALUES('" .$entryRu. "', '" .$name. "')";
            mysqli_query($conLib , $query);

            $pages = getPDFPages($entry);
            $book_id = mysqli_insert_id($conLib);
            for($i=1;$i<=$pages;$i++) {
                $output = `./pdftotext -f {$i} -l {$i} -cfg /var/www/html/library/xpdfrc -enc KOI8-R pdf/{$entry} temp.txt`;
                $text = file_get_contents("temp.txt");
                $textUTF8 = mb_convert_encoding($text, "UTF8", "KOI8-R");
                $textUTF8 = mysqli_real_escape_string($conLib , $textUTF8);
                $query = "INSERT INTO pages(book_id, page_number, text) VALUES(" .$book_id. ", " .$i. ", '" .$textUTF8. "')";
                mysqli_query($conLib , $query);

            }

        }
    }

    closedir($handle);
}

?>