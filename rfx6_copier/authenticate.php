<?php
	//==================================================================================
	require_once("config.php");
	include_once 'db.php';
	//==================================================================================
	if (isset($_GET['provider'])) $provider = $_GET['provider'];
	else if (isset($_GET['client'])) $client = $_GET['client'];
	else die('-1<br>invalid parameter format');
	if (isset($_GET['password'])) $password = $_GET['password']; else die('-1<br>invalid parameter format');
	$db = new db();
	$error = '';
	$result = $authentication_failed;
	if (isset($_GET['provider']))
		$result = $db->authenticate_provider($provider, $password, $error);
	else if (isset($_GET['client']))
		$result = $db->authenticate_client($client, $password, $error);
	if ($result < 0) echo $result.'<br>'.$error;
	else echo $result.'<br>'.'succeeded';
	//==================================================================================
?>