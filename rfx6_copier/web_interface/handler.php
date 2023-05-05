<?php
	require_once("db.php");
	if(!$db->CheckLogin())
	{
		header('Location: login.php');
		exit;
	}
	if (isset($_GET['form'])) $form = $_GET['form'];
	else header('Location: '.$_SERVER['REFERER']);
	if (isset($_GET['action'])) $action = $_GET['action'];
	else header('Location: '.$_SERVER['REFERER']);
	switch ($form)
	{
		case 'users':
			if ($action == 'add')
			{
				$out = "<form action='doit.php?action=user_add' method='post'>";
				$out = $out.'<h1>Add User</h1>';
				$out = $out.'<div><input type="text" required="" title="User Name" placeholder="User Name" name="username" id="username" /></div>';
				$out = $out.'<div><input type="text" required="" title="Full Name" placeholder="Full Name" name="full_name" id="full_name" /></div>';
				$out = $out.'<div><input type="password" required="" title="Password" placeholder="Password" name="password" id="password" /></div>';
				$out = $out.'<div><input type="email" required="" title="EMail" placeholder="EMail" name="email" id="email" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			else if ($action == 'edit')
			{
				$id = $_GET['id'];
				$out = "<form action='doit.php?action=user_edit&id=$id' method='post'>";
				$out = $out.'<h1>Edit User</h1>';
				$row = $db->getUser($id);
				$out = $out.'<div><input type="text" required="" title="User Name" placeholder="User Name" name="username" id="username" value="'.$row['username'].'" /></div>';
				$out = $out.'<div><input type="text" required="" title="Full Name" placeholder="Full Name" name="full_name" id="full_name" value="'.$row['full_name'].'" /></div>';
				$out = $out.'<div><input type="password" required="" title="Password" placeholder="Password" name="password" id="password" value="'.$row['password'].'" /></div>';
				$out = $out.'<div><input type="email" required="" title="EMail" placeholder="EMail" name="email" id="email" value="'.$row['email'].'" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			break;
		case 'providers';
			if ($action == 'add')
			{
				$out = "<form action='doit.php?action=provider_add' method='post'>";
				$out = $out.'<h1>Add Provider</h1>';
				$out = $out.'<div><input type="text" required="" title="Name" placeholder="Name" name="name" id="name" /></div>';
				$out = $out.'<div><input type="text" required="" title="Full Name" placeholder="Full Name" name="full_name" id="full_name" /></div>';
				$out = $out.'<div><input type="password" required="" title="Password" placeholder="Password" name="password" id="password" /></div>';
				$out = $out.'<div><input type="email" required="" title="EMail" placeholder="EMail" name="email" id="email" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			else if ($action == 'edit')
			{
				$id = $_GET['id'];
				$out = "<form action='doit.php?action=provider_edit&id=$id' method='post'>";
				$out = $out.'<h1>Edit Provider</h1>';
				$row = $db->getProvider($id);
				$out = $out.'<div><input type="text" required="" title="Name" placeholder="Name" name="name" id="name" value="'.$row['name'].'" /></div>';
				$out = $out.'<div><input type="text" required="" title="Full Name" placeholder="Full Name" name="full_name" id="full_name" value="'.$row['full_name'].'" /></div>';
				$out = $out.'<div><input type="password" required="" title="Password" placeholder="Password" name="password" id="password" value="'.$row['password'].'" /></div>';
				$out = $out.'<div><input type="email" required="" title="EMail" placeholder="EMail" name="email" id="email" value="'.$row['email'].'" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			break;
		case 'clients';
			if ($action == 'add')
			{
				$out = "<form action='doit.php?action=client_add' method='post'>";
				$out = $out.'<h1>Add Client</h1>';
				$out = $out.'<div><input type="text" required="" title="Name" placeholder="Name" name="name" id="name" /></div>';
				$out = $out.'<div><input type="text" required="" title="Full Name" placeholder="Full Name" name="full_name" id="full_name" /></div>';
				$out = $out.'<div><input type="password" required="" title="Password" placeholder="Password" name="password" id="password" /></div>';
				$out = $out.'<div><input type="email" required="" title="EMail" placeholder="EMail" name="email" id="email" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			else if ($action == 'edit')
			{
				$id = $_GET['id'];
				$out = "<form action='doit.php?action=client_edit&id=$id' method='post'>";
				$out = $out.'<h1>Edit Client</h1>';
				$row = $db->getClient($id);
				$out = $out.'<div><input type="text" required="" title="Name" placeholder="Name" name="name" id="name" value="'.$row['name'].'" /></div>';
				$out = $out.'<div><input type="text" required="" title="Full Name" placeholder="Full Name" name="full_name" id="full_name" value="'.$row['full_name'].'" /></div>';
				$out = $out.'<div><input type="password" required="" title="Password" placeholder="Password" name="password" id="password" value="'.$row['password'].'" /></div>';
				$out = $out.'<div><input type="email" required="" title="EMail" placeholder="EMail" name="email" id="email" value="'.$row['email'].'" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			break;
		case 'mappings';
			$rows = $db->getProviders();
			$providers = '<datalist id="providers">';
			for ($i = 0; $i < count($rows); $i++)
				$providers = $providers.'<option value="'.$rows[$i]['name'].'">';
			$providers = $providers.'</datalist>';
			$rows = $db->getClients();
			$clients = '<datalist id="clients">';
			for ($i = 0; $i < count($rows); $i++)
				$clients = $clients.'<option value="'.$rows[$i]['name'].'">';
			$clients = $clients.'</datalist>';
			if ($action == 'add')
			{
				$out = "<form action='doit.php?action=mapping_add' method='post'>";
				$out = $out.'<h1>Add Mapping</h1>';
				$out = $out.'<div><input type="text" list="providers" required="" title="Provider" name="pname" id="pname" /></div>';
				$out = $out.$providers;
				$out = $out.'<div><input type="text" list="clients" required="" title="Client" name="cname" id="cname" /></div>';
				$out = $out.$clients;
				$out = $out.'<div><input type="text" title="From Date" placeholder="YYYY-MM-DD" name="fdate" id="fdate" /></div>';
				$out = $out.'<div><input type="text" title="To Date" placeholder="YYYY-MM-DD" name="tdate" id="tdate" /></div>';
				$out = $out.'<div><input type="text" title="Client Account No" placeholder="Client Account No" name="account_no" id="account_no" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			else if ($action == 'edit')
			{
				$id = $_GET['id'];
				$out = "<form action='doit.php?action=mapping_edit&id=$id' method='post'>";
				$out = $out.'<h1>Edit Mapping</h1>';
				$row = $db->getMapping($id);
				$out = $out.'<div><input type="text" list="providers" readonly name="pname" id="pname" value="'.$row['pname'].'" /></div>';
				$out = $out.$providers;
				$out = $out.'<div><input type="text" list="clients" readonly name="cname" id="cname" value="'.$row['cname'].'" /></div>';
				$out = $out.$clients;
				$out = $out.'<div><input type="text" title = "From Date" placeholder="YYYY-MM-DD" name="fdate" id="fdate" value="'.$row['fdate'].'" /></div>';
				$out = $out.'<div><input type="text" title = "To Date" placeholder="YYYY-MM-DD" name="tdate" id="tdate" value="'.$row['tdate'].'" /></div>';
				$out = $out.'<div><input type="text" title="Client Account No" placeholder="Client Account No" name="account_no" id="account_no" value="'.$row['account_no'].'" /></div>';
				$out = $out.'<div><input type="submit" name="submit" id="submit" value="Save" /></div>';
				$out = $out.'<div><input type="button" name="cancel" id="cancel" value="Cancel" onclick="history.back();" /></div>';
				$out = $out."</form>";
			}
			break;
	}
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="ie6 ielt8"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="ie7 ielt8"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="ie8"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<title>Signal Provider Copier</title>
<link rel="stylesheet" type="text/css" href="resources/css/handler.css" />
</head>
<body>
<div class="container">
	<section id="content">
		<?php echo $out ?>
	</section><!-- content -->
</div><!-- container -->
</body>
</html>
