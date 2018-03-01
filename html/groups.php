<?php
session_start();
include_once 'dbconnect.php';
if(empty($_SESSION['usr_id']) or $_SESSION['usr_role'] != 'teacher') {
	header("Location: index.php");
}
$error = false;
//кнопка «Сохранить»
if (isset($_POST['change'])) {
    $new_group = mysqli_real_escape_string($con, $_POST['group_name']);
    $old_group = mysqli_real_escape_string($con, $_POST['old_group']);

    if ($new_group == "") {
	    $error = true;
		$errormsg = "Название не может быть пустым";
	}
	if (!$error) {
		if(mysqli_query($con, "UPDATE groups SET name = '" . $new_group . "'WHERE name = '".$old_group."'"))
			{
				$successmsg = "Данные успешно обновлены";
			} else {
				$errormsg = "Ошибка обновления данных";
			}
	}
}

//кнопка «Удалить»
if (isset($_POST['delete'])) {
    $old_group = mysqli_real_escape_string($con, $_POST['old_group']);
		if(mysqli_query($con, "DELETE FROM groups WHERE name = '".$old_group."'"))
			{
				$successmsg = "Группа успешно удалена";
			} else {
				$errormsg = "Ошибка при удалении";
			}
}

//кнопка «Добавить»
if (isset($_POST['add'])) {
    $new_group = mysqli_real_escape_string($con, $_POST['group_name']);

    if ($new_group == "") {
	    $error = true;
		$errormsg = "Название не может быть пустым";
	}
	if (!$error) {
		if(mysqli_query($con, "INSERT INTO groups(name) VALUES('" . $new_group . "')"))
			{
				$successmsg = "Группа успешно добавлена";
			} else {
				$errormsg = "Ошибка добавления группы";
			}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Список групп — <?php echo mysqli_fetch_object(mysqli_query($con, "SELECT settings_value FROM settings WHERE settings_name ='site_name'"))->settings_value ?></title>
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
			<div class="col-md-10 well">
           		<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="settingsform">
					<fieldset>
                    	<legend>Список групп</legend>
                    	<?php if (isset($successmsg)) { echo "<div class='alert alert-success' role='alert'>".$successmsg."</div>"; } ?>
						<?php if (isset($errormsg)) { echo "<div class='alert alert-danger' role='alert'>".$errormsg."</div>"; } ?>

						<table class="table table-striped table-hover">
							    <tr>
	                    			<td width="200" <?php
						if (($result_select = mysqli_query($con, "SELECT * FROM groups ORDER BY name"))->num_rows > 0) { echo "colspan='2'"; } ?>  >
									<?php
										echo "<form action='".$_SERVER['PHP_SELF']."' method='post' name='groupform'>";
										echo "<input type='submit' name='add' value='Добавить' class='btn btn-success btn-s btn-block'>";
									?>
									</td>

									<td><input type="text" name="group_name" placeholder="Название новой группы" required class="form-control" /></td>
								</tr>
								</form>
														<?php
						if (($result_select = mysqli_query($con, "SELECT * FROM groups ORDER BY name"))->num_rows > 0) {
		                    while($group_list = mysqli_fetch_object($result_select))
		                    {
		                    	echo "<form action='".$_SERVER['PHP_SELF']."' method='post' name='groupform'>";
		                    	echo "<input type = 'hidden' name = 'old_group' value = '$group_list->name'>";
	                    ?>
	                    		<tr>
	                    			<td width="100">
	                    			<?php
										echo "<input type='submit' name='delete' value='Удалить' class='btn btn-danger btn-s btn-block'>";
									?>
									</td>
									<td width="100">
									<?php
										echo "<input type='submit' name='change' value='Сохранить' class='btn btn-success btn-s btn-block'>";
									?>
									</td>

									<td><input type="text" name="group_name" placeholder="Введите название группы" required value="<?php echo $group_list->name; ?>" class="form-control" /></td>
								</tr>
								</form>
						<?php
		                    }
	                    ?>

						<?php } ?>
						</table>
					</fieldset>
				</form>
			</div>
			<div class="col-md-2">
				<a href="index.php" class="btn btn-info btn-block">Вернуться</a>
			</div>
		</div>
	</div>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
