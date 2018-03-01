<?php
session_start();
include_once 'dbconnect.php';

if(empty($_SESSION['usr_id'])) {
	header("Location: index.php");
}

//check if form is submitted
if (isset($_POST['passwordbutton'])) {

	$error = false;
	$my_id = $_SESSION['usr_id'];
	$old_password = mysqli_real_escape_string($con, $_POST['old_password']);
	$password = mysqli_real_escape_string($con, $_POST['password']);
	$cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
	$result = mysqli_query($con, "SELECT * FROM users WHERE id = '" . $my_id. "' and password = '" . md5($old_password) . "'");
	if (!$row = mysqli_fetch_array($result)) {
		$old_password_error = "Неправильный текущий пароль";
		$error = true;
	}

	if($password != $cpassword) {
		$password_error = "Пароли не совпадают";
		$error = true;
	}

	if (!$error) {
  		if(mysqli_query($con, "UPDATE users SET password = '".md5($password)."' WHERE id =".$my_id."")) {
			$successmsg = "Пароль успешно изменён!";
		} else {
			$errormsg = "Ошибка смены пароля. Пожалуйста, попробуйте ещё раз";
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Смена пароля — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" charset="UTF-8">
	<link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
</head>
<body>

<nav class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<!-- add header -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar1">
				<span class="sr-only">Навигация</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="/"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
		</div>
		<!-- menu items -->
		<div class="collapse navbar-collapse" id="navbar1">
			<ul class="nav navbar-nav navbar-right">
				<?php if (isset($_SESSION['usr_id'])) { ?>
				<li><p class="navbar-text">Вы вошли как <?php echo $_SESSION['usr_name']; ?></p></li>
				<li><a href="logout.php">Выйти</a></li>
				<?php } else { ?>
				<li><a href="login.php">Вход</a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>

<div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4 well">
			<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
				<fieldset>
					<legend>Смена пароля</legend>

					<div class="form-group">
						<label for="name">Текущий пароль</label>
						<input type="password" name="old_password" placeholder="Ваш текущий пароль" required class="form-control" />
						<span class="text-danger"><?php if (isset($old_password_error)) echo $old_password_error; ?></span>
					</div>

					<div class="form-group">
						<label for="name">Новый пароль</label>
						<input type="password" name="password" placeholder="Ваш новый пароль" required class="form-control" />
					</div>

					<div class="form-group">
						<label for="name">Подтвердите пароль</label>
						<input type="password" name="cpassword" placeholder="Подтвердите новый пароль" required class="form-control" />
						<span class="text-danger"><?php if (isset($password_error)) echo $password_error; ?></span>
					</div>

					<div class="form-group">
						<input type="submit" name="passwordbutton" value="Сменить" class="btn btn-primary" />
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
