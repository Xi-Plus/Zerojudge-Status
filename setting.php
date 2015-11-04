<?php
if(isset($_POST["prob"])){
	$content=$_POST["prob"];
	file_put_contents("prob.txt",$content);
}
if(isset($_POST["user"])){
	$content=$_POST["user"];
	$content=str_replace(" ","\t",$content);
	file_put_contents("user.txt",$content);
}
?>
<html>
<head>
	<title>Zerojudge-Status</title>
	<meta charset="UTF-8">
</head>
<body>
<form method="POST">
<textarea name="prob" cols="20" rows="30">
<?=htmlentities(@file_get_contents("prob.txt"))?>
</textarea>
<textarea name="user" cols="40" rows="30">
<?=htmlentities(@file_get_contents("user.txt"))?>
</textarea>
<br>
<input type="submit" value="Submit">
</form>
</body>
</html>