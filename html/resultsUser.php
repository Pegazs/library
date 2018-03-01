<?php
session_start();
include_once 'dbconnect.php';
if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'student') {
	header("Location: index.php");
}

//кнопка «Начать»
if (isset($_POST['look'])) {
	$session_id = mysqli_real_escape_string($con, $_POST['session_id']);
	header("Location: result.php?id=".$session_id);
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Результаты тестов — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
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
			<div class="col-md-4 col-md-offset-4 text-center">
				<a href="/">Вернуться на главную</a>
				<hr />
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 well">
           		<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
					<fieldset>
                    	<legend>Результаты тестов</legend>
                    	<span><?php if (isset($test_error)) { echo $test_error; } ?></span>
						<?php
						//if (($result = mysqli_query($con, "SELECT * FROM workers WHERE worker_company = '".$_SESSION['usr_id']."'"))->num_rows != 0) {
						if (($result_select = mysqli_query($con, "SELECT * FROM sessions WHERE user_id = ".$_SESSION['usr_id']." AND status != 'started' ORDER BY id DESC LIMIT 1000")) && ($result_select->num_rows) > 0) {
						?>
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th>Название теста</th>
								<th>Время начала</th>
								<th></th>
							</tr>
							</thead>
							<tbody>
						<?php
		                    while($session = mysqli_fetch_object($result_select))
		                    {
		                    	$test_info = mysqli_fetch_object(mysqli_query($con, "SELECT * FROM tests WHERE id =" . ($session->test_id)));
		                    	echo "<form action='".$_SERVER['PHP_SELF']."' method='post' name='userform'>";
		                    	echo "<input type = 'hidden' name = 'session_id' value = '$session->id'>";
	                    ?>
	                    		<tr>
									<th scope="row"><?php echo $test_info->name; ?></th>
									<td>
										<?php echo date('H:i:s (d.m.Y)', strtotime($session->start_time)); ?>
									</td>
									<td>
									<?php
										echo "<input type='submit' name='look' value='Посмотреть' class='btn btn-success btn-xs btn-block'>";
									?>
									</td>
								</tr>
								</form>
						<?php
		                    }
	                    ?>
							</tbody>
						</table>
						<?php } else {
							?>Тесты не найдены.<?php } ?>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
