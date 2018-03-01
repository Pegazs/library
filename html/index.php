<?php
session_start();
//header("Location: /library/index.php");
include_once 'dbconnect.php';
if(empty($_SESSION['usr_id']))
{
	header("Location: login.php");
}
else
{
	if($_SESSION['usr_role'] == 'admin')
	{
		header("Location: admin.php");
	}
	else
	{
		if($_SESSION['usr_role'] == 'teacher')
		{
	  		header("Location: teacher.php");
	  	}
	  	else
	  	{
	  		header("Location: student.php");
	  	}

	}
}

?>
