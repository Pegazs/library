<?php
//connect to mysql database
$conLib = mysqli_connect("localhost", "libraryUser", "L1BR@ry", "library") or die("Error " . mysqli_error($con));
mysqli_query($conLib , "SET NAMES utf8");
?>