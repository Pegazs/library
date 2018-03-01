<?php
session_start();

if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'admin') {
	header("Location: index.php");
}

include_once 'dbconnect.php';

$error = false;
//данные, хранящиеся в базе
$name = mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value;

//check if form is submitted
if (isset($_POST['savename'])) {
	$name = mysqli_real_escape_string($con, $_POST['name']);

	if (!$error) {
		if(mysqli_query($con, "UPDATE settings SET settings_value = '" . $name . "' WHERE settings_name ='site_name'"))
		{
			$successmsg = "Данные успешно обновлены <a href='/'>Нажмите здесь, чтобы вернуться на главную</a>";
		} else {
			$errormsg = "Ошибка обновления данных. Пожалуйста, попробуйте ещё раз";
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Редактирование названия сайта — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
	<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
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
				<li><a href="logout.php">Выйти</a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>

 <div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4 well">
			<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
				<fieldset>
					<legend>Название сайта</legend>

					<div class="form-group">
						<label for="name">Текст в шапке сайта</label>
						<input type="text" name="name" placeholder="Введите текст" required value="<?php echo $name; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($name_error)) echo $name_error; ?></span>
					</div>

					<div class="form-group">
						<input type="submit" name="savename" value="Сохранить" class="btn btn-primary" />
					</div>
				</fieldset>
			</form>
			<span class="text-success"><?php if (isset($successmsg)) { echo $successmsg; } ?></span>
			<span class="text-danger"><?php if (isset($errormsg)) { echo $errormsg; } ?></span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-md-offset-4 text-center">
		<a href="/">Вернуться без сохранения</a>
		</div>
	</div>
</div>

<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

