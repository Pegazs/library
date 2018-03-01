<?php
session_start();
include_once 'dbconnectLib.php';
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id']) or ($_SESSION['usr_role'] != 'teacher' and $_SESSION['usr_role'] != 'admin')) {
    header("Location: ../index.php");
}

function getPDFPages($document)
{
    $cmd = "/var/www/html/library/pdfinfo";

    // Parse entire output
    // Surround with double quotes if file name has spaces
    exec("$cmd \"books/$document\"", $output);

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

if (isset($_POST['deletebook'])) {
    $book_id = mysqli_real_escape_string($conLib, $_POST['book_id']);
    $book_file_name = mysqli_real_escape_string($conLib, $_POST['book_file_name']);
    mysqli_query($conLib, "DELETE FROM books WHERE id = '".$book_id."'");
    unlink('/var/www/html/library/books/'.$book_file_name);
}

if (isset($_POST['editbook'])) {
    $book_id = mysqli_real_escape_string($conLib, $_POST['book_id']);
    $book_name = mysqli_real_escape_string($conLib, $_POST['book_name']);
    $book_authors = mysqli_real_escape_string($conLib, $_POST['book_authors']);
    mysqli_query($conLib, "UPDATE books SET name = '".$book_name."', authors = '".$book_authors."' WHERE id = '".$book_id."'");
}

if(isset($_POST['newbook'])) {
    $name = mysqli_real_escape_string($conLib, $_POST['book_name']);
    $book_authors = mysqli_real_escape_string($conLib, $_POST['book_authors']);
    $uploadfile=$_FILES["upload_file"]["tmp_name"];
    $tmp = explode(".",$_FILES['upload_file']['name']);
    $extension = end($tmp);
    if ($extension == "pdf") {
        $newfilename = round(microtime(true)) . '.' . end($tmp);
        move_uploaded_file($_FILES["upload_file"]["tmp_name"], "/var/www/html/library/books/" . $newfilename);
//        $entryRu = mb_convert_encoding($newfilename, "UTF8", "Windows-1251");
//        $name = stristr($entryRu, ".", true);
        $query = "INSERT INTO books(file_name, name, authors) VALUES('" .$newfilename. "', '" .$name. "', '".$book_authors."')";
        mysqli_query($conLib , $query);

        $pages = getPDFPages($newfilename);
        $book_id = mysqli_insert_id($conLib);
        for($i=1;$i<=$pages;$i++) {
            $output = `./pdftotext -f {$i} -l {$i} -cfg /var/www/html/library/xpdfrc -enc KOI8-R books/{$newfilename} temp.txt`;
            $text = file_get_contents("temp.txt");
            $textUTF8 = mb_convert_encoding($text, "UTF8", "KOI8-R");
            $textUTF8 = mysqli_real_escape_string($conLib , $textUTF8);
            $query = "INSERT INTO pages(book_id, page_number, text) VALUES(" .$book_id. ", " .$i. ", '" .$textUTF8. "')";
            mysqli_query($conLib , $query);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Редактирование книг — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
	<link href="../favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" type="text/css" />

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
			<a class="navbar-brand" href="/"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
		</div>
		<div class="collapse navbar-collapse" id="navbar1">
			<ul class="nav navbar-nav navbar-right">
				<?php if (isset($_SESSION['usr_id'])) { ?>
				<li><p class="navbar-text">Вы вошли как <?php echo $_SESSION['usr_name']; ?></p></li>
				<li><a href="../logout.php">Выйти</a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>

	<div class="container">
		<div class="row">
			<div class="col-md-10 well">
					<fieldset>
                    	<legend>Редактирование информации о книгах</legend>
                        <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="new_book" enctype="multipart/form-data">
                        <h4>Добавление новой книги:</h4>
                        <div class="form-group">
                            <span><?php if (isset($load_success)) { echo $load_success; } ?></span>
                            <span><?php if (isset($load_error)) { echo $load_error; } ?></span>
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th width = "20%">Файл</th>
                                    <th width = "35%">Название</th>
                                    <th width = "35%">Авторы</th>
                                    <th width = "10%"></th>
                                </tr>
                                </thead>
                                <tbody>

                                <tr>
                                    <td><input type="file" id="upload_file" name="upload_file" /></td>
                                    <td><input type="text" name="book_name" placeholder="Укажите название" required" class="form-control form-control-success" /></td>
                                    <td><input type="text" name="book_authors" placeholder="Укажите авторов" class="form-control form-control-success" /></td>
                                    <td><input type="submit" value="Загрузить" name='newbook' class="btn btn-info btn-block"></td>
                                </tbody>
                            </table>
                        </div>
                        </form>

                    	<span><?php if (isset($test_error)) { echo $test_error; } ?></span>
						<?php
						if (($result_select = mysqli_query($conLib, "SELECT * FROM books ORDER BY name"))->num_rows > 0) {
						?>
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th width = "40%">Название</th>
								<th width = "30%">Авторы</th>
                                <th width = "30%"></th>
							</tr>
							</thead>
							<tbody>
						<?php
		                    while($book = mysqli_fetch_object($result_select))
		                    {
		                    	echo "<form action='".$_SERVER['PHP_SELF']."' method='post' name='userform'>";
		                    	echo "<input type = 'hidden' name = 'book_id' value = '$book->id'>";
		                    	echo "<input type = 'hidden' name = 'book_file_name' value = '$book->file_name'>";
	                    ?>
	                    		<tr>
                                    <td><input type="text" name="book_name" placeholder="Укажите название" required value="<?php echo $book->name; ?>" class="form-control form-control-success" /></td>
                                    <td><input type="text" name="book_authors" placeholder="Укажите авторов" value="<?php echo $book->authors; ?>" class="form-control form-control-success" /></td>

                                    <td><a target='_blank' class="btn btn-sm btn-primary" href="books/<?php echo $book->file_name; ?>">Открыть</a>
                                        <input type="submit" name="editbook" value="Сохранить" class="btn btn-sm btn-success" />
                                        <input type="submit" name="deletebook" value="Удалить" class="btn btn-sm btn-danger" /></td>
								</tr>
								</form>
						<?php
		                    }
	                    ?>
							</tbody>
						</table>
						<?php } else {
							?>Книги не найдены.<?php } ?>
					</fieldset>
				</form>
			</div>
			<div class="col-md-2">
				<a href="index.php" class="btn btn-primary btn-block">Поиск по книгам</a>
                <a href="books.php" class="btn btn-info btn-block">К списку книг</a>
                <a href="../index.php" class="btn btn-warning btn-block">Вернуться на главную</a>
			</div>
		</div>
	</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
