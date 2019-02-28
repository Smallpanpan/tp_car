<!DOCTYPE html>
<html>
<head>
	 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>测试PHP响应</title>

</head>
<body>
	
	<form name="form1" action="" method="post">
		用户名：<input type="text" name="name" value="" size="16">
		<input type="submit" name="submit1" value="确定">
	</form>

<?php
if($_POST["submit1"]=="确定"){
	echo "你输入的名字是：".$_POST["name"];
}
?>
</body>
</html>