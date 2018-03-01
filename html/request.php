<?php
if(isset($_POST['submit_image']))
{
 $uploadfile=$_FILES["upload_file"]["tmp_name"];
 $folder="/var/www/html/excel/";
 move_uploaded_file($_FILES["upload_file"]["tmp_name"], "$folder".$_FILES["upload_file"]["name"]);
}
?>

<html>
<head>
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.min.js"></script>

</head>
<body>
<div id="wrapper">
 <form action="" method="post" enctype="multipart/form-data">
  <input type="file" id="upload_file" name="upload_file" />
  <input type="submit" name='submit_image' value="Upload Image"/>
 </form>
</div>
</body>
</html>