<?php
session_start();
include_once 'dbconnectLib.php';
include_once '../dbconnect.php';

if(empty($_SESSION['usr_id'])) {
    header("Location: ../index.php");
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Список доступных книг — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
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
           		<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
					<fieldset>
                    	<legend>Список доступных книг</legend>
                    	<span><?php if (isset($test_error)) { echo $test_error; } ?></span>
						<?php
						if (($result_select = mysqli_query($conLib, "SELECT * FROM books ORDER BY name"))->num_rows > 0) {
						?>
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th>Название</th>
								<th>Авторы</th>
							</tr>
							</thead>
							<tbody>
						<?php
		                    while($book = mysqli_fetch_object($result_select))
		                    {
//		                    	echo "<form action='".$_SERVER['PHP_SELF']."' method='post' name='userform'>";
//		                    	echo "<input type = 'hidden' name = 'test_id' value = '$test->id'>";
	                    ?>
	                    		<tr>
									<td><?php echo "<a target='_blank' href=books/$book->file_name>$book->name</a>"; ?></td>
									<td><?php echo "$book->authors"; ?></td>
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
<!--				</form>-->
			</div>
			<div class="col-md-2">
				<a href="index.php" class="btn btn-primary btn-block">Поиск по книгам</a>
<?php if($_SESSION['usr_role'] == 'teacher' or $_SESSION['usr_role'] == 'admin') { ?>
                    <a href="books-admin.php" class="btn btn-danger btn-block">Администрирование</a>
<?php   }  ?>
                <a href="../index.php" class="btn btn-warning btn-block">Вернуться на главную</a>
			</div>
		</div>
	</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
