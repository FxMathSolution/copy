<?php
	require_once("db.php");
	if(!$db->CheckLogin())
	{
		header('Location: login.php');
		exit;
	}
	if (isset($_SESSION['name_of_user']))
		$full_name = $_SESSION['name_of_user'];
	else
		$full_name = 'no name!';
	$rows = $db->getProviders();
	$providers = '';
	for ($i = 0; $i < count($rows); $i++)
		$providers = $providers.'<li><a href="main.php?action=showProviderDeals&name='.$rows[$i]['name'].'">'.$rows[$i]['name'].'</a></li>';
	$rows = $db->getClients();
	$clients = '';
	for ($i = 0; $i < count($rows); $i++)
		$clients = $clients.'<li><a href="main.php?action=showClientDeals&name='.$rows[$i]['name'].'">'.$rows[$i]['name'].'</a></li>';
	if (isset($_GET['action']))
		$action = $_GET['action'];
	else
		$action='showUsers';
	switch ($action)
	{
		case 'showUsers':
			$users = $db->getUsers();
			$out = $db->export_html_table($users, 'Users', array('username', 'full_name', 'email', 'id'), array('username', 'full_name', 'email', 'id'), array(50, 200, 150, 0), 'id', array('add', 'edit'), 'handler.php?form=users');
			break;
		case 'showProviders':
			$a_providers = $db->getProviders();
			$out = $db->export_html_table($a_providers, 'Providers', array('name', 'full_name', 'email', 'id'), array('name', 'full_name', 'email', 'id'), array(50, 200, 150, 0), 'id', array('add', 'edit'), 'handler.php?form=providers');
			break;
		case 'showClients':
			$a_clients = $db->getClients();
			$out = $db->export_html_table($a_clients, 'Clients', array('name', 'full_name', 'email', 'id'), array('name', 'full_name', 'email', 'id'), array(50, 200, 150, 0), 'id', array('add', 'edit'), 'handler.php?form=clients');
			break;
		case 'showMappings':
			$a_mappings = $db->getMappings();
			$out = $db->export_html_table($a_mappings, 'Provider & CLient Mappings', array('pname', 'cname', 'account_no', 'fdate', 'tdate', 'id'), array('Provider', 'Client', 'Account No', 'from date', 'to date', 'id'), array(50, 200, 70, 150, 150, 0), 'id', array('add', 'edit'), 'handler.php?form=mappings');
			break;
		case 'showProviderDeals':
			$provider = $_GET['name'];
			$rows = $db->getProviderAccounts($provider);
			$accounts = '<datalist id="accounts">';
			for ($i = 0; $i < count($rows); $i++)
				$accounts = $accounts.'<option value="'.$rows[$i]['account_no'].'">';
			$accounts = $accounts.'</datalist>';
			if (isset($_GET['account_no'])) $account_no = $_GET['account_no'];
			else $account_no = '';
			if (isset($_GET['from'])) $fdate = $_GET['from'];
			else
			{
				$date = new DateTime();
				$date->sub(new DateInterval('P10D'));
				$fdate = $date->format('Y-m-d');
			}
			if (isset($_GET['to'])) $tdate = $_GET['to'];
			else
			{
				$date = new DateTime();
				$tdate = $date->format('Y-m-d');
			}
			$deals = $db->getProviderDeals($provider, $fdate, $tdate, $account_no);
			$out = '<input type="hidden" name="provider" id="provider" value="'.$provider.'" />';
			$out = $out.$accounts;
			$out = $out."<table cellpadding='8px' width='100%'>";
			$out = $out."<tr><td width='100%'>";
			$out = $out.'<input type="text" title="From Date" placeholder="YYYY-MM-DD" name="fdate" id="fdate" value="'.$fdate.'" />&nbsp&nbsp';
			$out = $out.'<input type="text" title="To Date" placeholder="YYYY-MM-DD" name="tdate" id="tdate" value="'.$tdate.'" />&nbsp&nbsp';
			$out = $out.'<input type="text" list="accounts" title="Account No" placeholder="account_no" name="account_no" id="account_no" value="'.$account_no.'" />&nbsp&nbsp';
			$out = $out.'<input type="button" name="refresh" id="refresh" value="refresh" onclick="refresh_provider_deals();" />';
			$out = $out."</td></tr>";
			$out = $out."<tr><td width='100%'>";
			$out = $out.$db->export_html_table($deals, 'Provider Deals ('.$provider.')', array('account_no', 'catch_time', 'ticket', 'symbol', 'status', 'desc', 'id'), array(), array(50, 120, 50, 50, 30, 250, 0), 'id', array(), '');
			$out = $out."</td></tr></tabletr>";
			break;
		case 'showClientDeals':
			$client = $_GET['name'];
			$rows = $db->getClientAccounts($client);
			$accounts = '<datalist id="accounts">';
			for ($i = 0; $i < count($rows); $i++)
				$accounts = $accounts.'<option value="'.$rows[$i]['account_no'].'">';
			$accounts = $accounts.'</datalist>';
			if (isset($_GET['account_no'])) $account_no = $_GET['account_no'];
			else $account_no = '';
			if (isset($_GET['from'])) $fdate = $_GET['from'];
			else
			{
				$date = new DateTime();
				$date->sub(new DateInterval('P10D'));
				$fdate = $date->format('Y-m-d');
			}
			if (isset($_GET['to'])) $tdate = $_GET['to'];
			else
			{
				$date = new DateTime();
				$tdate = $date->format('Y-m-d');
			}
			$deals = $db->getClientDeals($client, $fdate, $tdate, $account_no);
			$out = '<input type="hidden" name="client" id="client" value="'.$client.'" />';
			$out = $out.$accounts;
			$out = $out."<table cellpadding='8px' width='100%'>";
			$out = $out."<tr><td width='100%'>";
			$out = $out.'<input type="text" title="From Date" placeholder="YYYY-MM-DD" name="fdate" id="fdate" value="'.$fdate.'" />&nbsp&nbsp';
			$out = $out.'<input type="text" title="To Date" placeholder="YYYY-MM-DD" name="tdate" id="tdate" value="'.$tdate.'" />&nbsp&nbsp';
			$out = $out.'<input type="text" list="accounts" title="Account No" placeholder="account_no" name="account_no" id="account_no" value="'.$account_no.'" />&nbsp&nbsp';
			$out = $out.'<input type="button" name="refresh" id="refresh" value="refresh" onclick="refresh_client_deals();" />';
			$out = $out."</td></tr>";
			$out = $out."<tr><td width='100%'>";
			$out = $out.$db->export_html_table($deals, 'Client Deals ('.$client.')', array('account_no', 'catch_time', 'ticket', 'symbol', 'status', 'desc', 'id'), array(), array(50, 120, 50, 50, 30, 250, 0), 'id', array(), '');
			$out = $out."</td></tr></tabletr>";
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
<title>Web Copier Solution</title>
<link rel="stylesheet" type="text/css" href="resources/css/treeview.css" />
<link rel="stylesheet" type="text/css" href="resources/css/table.css" />
<script type="text/javascript">
function refresh_provider_deals()
{
	var fdate = document.getElementById('fdate').value;
	if (fdate == '') { alert('please fill from date'); return; }
	var tdate = document.getElementById('tdate').value;
	if (tdate == '') { alert('please fill to date'); return; }
	var provider = document.getElementById('provider').value;
	var account_no = document.getElementById('account_no').value;
	var url = 'main.php?action=showProviderDeals&name='+provider+'&from='+fdate+'&to='+tdate;
	if (account_no != '') url = url+'&account_no='+account_no;
	document.location.href = url;
}

function refresh_client_deals()
{
	var fdate = document.getElementById('fdate').value;
	if (fdate == '') { alert('please fill from date'); return; }
	var tdate = document.getElementById('tdate').value;
	if (tdate == '') { alert('please fill to date'); return; }
	var client = document.getElementById('client').value;
	var account_no = document.getElementById('account_no').value;
	var url = 'main.php?action=showClientDeals&name='+client+'&from='+fdate+'&to='+tdate;
	if (account_no != '') url = url+'&account_no='+account_no;
	document.location.href = url;
}
</script>
</head>
<body>
	<table class="css-table-main" cellspacing='0' cellpadding='4px'>
		<tr height='70px'><th colspan='3'>Web Copier Solution</th></tr>
		<tr><th colspan='3' align='right'>Welcome <?php echo $full_name; ?>&nbsp&nbsp&nbsp <a href="doit.php?action=logout">Log out</a></th></tr>
		<tr>
			<td width='180' style="vertical-align:top;">
				<div class="css-treeview">
				<ul>
					<li><input type="checkbox" checked="checked" id="users" /><label for="users"><a href="main.php?action=showUsers">Users</a></label>
					</li>
					<li><input type="checkbox" checked="checked" id="customers" /><label for="customers">Customers</label>
						<ul>
							<li><a href="main.php?action=showProviders">Providers</a></li>
							<li><a href="main.php?action=showClients">Clients</a></li>
							<li><a href="main.php?action=showMappings">Providers & Clients Mapping</a></li>
						</ul>
					</li>
					<li><input type="checkbox" checked="checked" id="providers_deals" /><label for="providers_deals">Providers Deals</label>
						<ul>
							<?php echo $providers; ?>
						</ul>
					</li>
					<li><input type="checkbox" checked="checked" id="clients_deals" /><label for="clients_deals">Clients Deals</label>
						<ul>
							<?php echo $clients; ?>
						</ul>
					</li>
				</ul>
			</div>
			</td>
			<td colspan='2' style="vertical-align:top;">
				<?php echo $out; ?>
			</td>
		</tr>
		<tr><th colspan='3' style="height:30px; font-size:11px; color: #b1b1b1;">Copyright © 2014 ---------. All rights reserved.</a></th></tr>
	</table>
</body>
</html>
