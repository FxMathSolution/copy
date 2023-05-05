<?php
	require_once("db.php");
	if(!$db->CheckLogin())
	{
		header('Location: login.php');
		exit;
	}
	if (isset($_GET['action'])) $action = $_GET['action'];
	else header('Location: '.$_SERVER['REFERER']);
	if (isset($_POST['submit']))
	{
		$err = '';
		switch ($action)
		{
			case 'user_add':
				$id = $db->addUser($_POST['username'], $_POST['password'], $_POST['full_name'], $_POST['email']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showUsers');
				break;
			case 'user_edit':
				$id = $db->editUser($_GET['id'], $_POST['username'], $_POST['password'], $_POST['full_name'], $_POST['email']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showUsers');
				break;
			case 'provider_add':
				$id = $db->addProvider($_POST['name'], $_POST['password'], $_POST['full_name'], $_POST['email']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showProviders');
				break;
			case 'provider_edit':
				$id = $db->editProvider($_GET['id'], $_POST['name'], $_POST['password'], $_POST['full_name'], $_POST['email']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showProviders');
				break;
			case 'client_add':
				$id = $db->addClient($_POST['name'], $_POST['password'], $_POST['full_name'], $_POST['email']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showClients');
				break;
			case 'client_edit':
				$id = $db->editClient($_GET['id'], $_POST['name'], $_POST['password'], $_POST['full_name'], $_POST['email']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showClients');
				break;
			case 'mapping_add':
				$id = $db->addMapping($_POST['pname'], $_POST['cname'], $_POST['account_no'], $_POST['fdate'], $_POST['tdate']);
				if ($id < 0)
					echo 'error';
				else
					header('Location: main.php?action=showMappings');
				break;
			case 'mapping_edit':
				$id = $db->editMapping($_GET['id'], $_POST['pname'], $_POST['cname'], $_POST['account_no'], $_POST['fdate'], $_POST['tdate'], $err);
				if ($id < 0)
					echo $err;
				else
					header('Location: main.php?action=showMappings');
				break;
			case 'logout':
				$db->LogOut();
				header('Location: login.php');
				break;
		}
	}
	else if ($action == 'logout')
	{
		$db->LogOut();
		header('Location: login.php');
	}
	else header('Location: '.$_SERVER['REFERER']);
?>
