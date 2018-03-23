<?php
//connect to mysql database
$conLib = mysqli_connect("localhost", "LOGIN", "PASSWORD", "testingDB") or die("Error " . mysqli_error($con));
mysqli_query($conLib , "SET NAMES utf8");
?>