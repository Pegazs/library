<?php
//connect to mysql database
$con = mysqli_connect("localhost", "LOGIN", "PASSWORD", "testingDB") or die("Error " . mysqli_error($con));
mysqli_query($con , "SET NAMES utf8");
?>