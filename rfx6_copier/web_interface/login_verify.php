<?php
	require_once("db.php");
	if (!isset($_SESSION)) session_start();
	if (isset($_POST['submit']))
	{
		if ($db->Login()) header('Location: main.php');
		else header('Location: login.php?username='.$_POST['username']);
	}
	else
		header('Location: login.php');
?>
