<?php
session_start();

if(isset($_SESSION['usr_id'])) {
	header("Location: index.php");
}

include_once 'dbconnect.php';

$error = false;
$noAdmin = false;

if (isset($_POST['signup'])) {
	$login = mysqli_real_escape_string($con, $_POST['login']);
	$name = mysqli_real_escape_string($con, $_POST['name']);
	$password = mysqli_real_escape_string($con, $_POST['password']);
	$cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
	$user_group = mysqli_real_escape_string($con, $_POST['user_group']);

	if (mysqli_query($con, "SELECT * FROM users WHERE login = '".$login."'")->num_rows > 0) {
		$error = true;
		$login_error = "Такой логин уже есть";
	}
	if (!preg_match("/^[а-яА-ЯёЁa-zA-Z ]+$/u",$name)) {
		$error = true;
		$name_error = "Имя должно содержать только буквы и пробелы";
	}
	if(strlen($password) < 8) {
		$error = true;
		$password_error = "Пароль должен содержать минимум 8 символов";
	}
	if($password != $cpassword) {
		$error = true;
		$cpassword_error = "Пароли не совпадают";
	}
	if (mysqli_query($con, "SELECT * FROM users WHERE role = 'admin'")->num_rows == 0) {
		$noAdmin = true;
	}

	if (!$error && $noAdmin) {
		if(mysqli_query($con, "INSERT INTO users(role,name,login,password) VALUES('admin','" . $name . "', '" . $login . "', '" . md5($password) . "')")) {
			$successmsg = "Вы зарегистрированы в качестве администратора. <a href='login.php'>Нажмите сюда, чтобы войти</a>";
		} else {
			$errormsg = "Ошибка при регистрации. Пожалуйста, попробуйте ещё раз";
		}
	}

	if (!$error && !$noAdmin) {
		if ($user_group == "NULL")
		{
			if(mysqli_query($con, "INSERT INTO users(name,login,password) VALUES('" . $name . "', '" . $login . "', '" . md5($password) . "')")) {
				$successmsg = "Теперь вы зарегестрированы! <a href='login.php'>Нажмите сюда, чтобы войти</a>";
			} else {
				$errormsg = "Ошибка при регистрации. Пожалуйста, попробуйте ещё раз";
			}
		} else {
			if(mysqli_query($con, "INSERT INTO users(name,login,password,user_group) VALUES('" . $name . "', '" . $login . "', '" . md5($password) . "','".$user_group."')")) {
				$successmsg = "Теперь вы зарегестрированы! <a href='login.php'>Нажмите сюда, чтобы войти</a>";
			} else {
				$errormsg = "Ошибка при регистрации. Пожалуйста, попробуйте ещё раз";
			}
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Регистрация — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
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
			<a class="navbar-brand" href="index.php"><?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></a>
		</div>
		<!-- menu items -->
		<div class="collapse navbar-collapse" id="navbar1">
			<ul class="nav navbar-nav navbar-right">
				<li><a href="login.php">Вход</a></li>
				<li class="active"><a href="register.php">Регистрация</a></li>
			</ul>
		</div>
	</div>
</nav>

<div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4 well">
			<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="signupform">
				<fieldset>
					<legend>Регистрация</legend>

					<div class="form-group">
						<label for="name">Логин</label>
						<input type="text" name="login" placeholder="Введите логин" required value="<?php if($error) echo $login; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($login_error)) echo $login_error; ?></span>
					</div>

					<div class="form-group">
						<label for="name">Имя</label>
						<input type="text" name="name" placeholder="Введите ваше имя" required value="<?php if($error) echo $name; ?>" class="form-control" />
						<span class="text-danger"><?php if (isset($name_error)) echo $name_error; ?></span>
					</div>

					<div class="form-group">
						<label for="user_group">Группа</label><br>
						<select name="user_group" style="width:100%;max-width:100%;">
							<option value="NULL">Нет</option>
							<?php
							$groups_select = mysqli_query($con, "SELECT * from groups ORDER BY name");
							for ($i = 1; $groups = mysqli_fetch_object($groups_select) ; $i++) {
							    echo "<option value='".($groups->name)."'>".($groups->name)."</option>";
							}
							?>
						</select>
						<span class="text-danger"><?php if (isset($user_group_error)) echo $user_group_error; ?></span>
					</div>

					<div class="form-group">
						<label for="name">Пароль</label>
						<input type="password" name="password" placeholder="Введите пароль" required class="form-control" />
						<span class="text-danger"><?php if (isset($password_error)) echo $password_error; ?></span>
					</div>

					<div class="form-group">
						<label for="name">Подтверждение пароля</label>
						<input type="password" name="cpassword" placeholder="Введите пароль ещё раз" required class="form-control" />
						<span class="text-danger"><?php if (isset($cpassword_error)) echo $cpassword_error; ?></span>
					</div>

					<div class="form-group">
						<input type="submit" name="signup" value="Зарегистрироваться" class="btn btn-primary" />
					</div>
				</fieldset>
			</form>
			<span class="text-success"><?php if (isset($successmsg)) { echo $successmsg; } ?></span>
			<span class="text-danger"><?php if (isset($errormsg)) { echo $errormsg; } ?></span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-md-offset-4 text-center">
		Уже зарегистрированы? <a href="login.php">Войдите здесь</a>
		</div>
	</div>
</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>



