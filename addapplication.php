<?php
	print_r($_POST);
	include "lib/class.app.php";
	$app = new appClass();
	$appListArray = $app->appadd();
	if($appListArray == '1')
	{
		header('Location: applistview.php'); 
	}
?>