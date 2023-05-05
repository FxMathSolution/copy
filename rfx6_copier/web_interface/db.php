<?php
	//==================================================================================
	require_once("../config.php");
	$db = new db();
	//==================================================================================
	class db
	{
		private $host;
		private $user;
		private $pass;
		private $db;
		private $new_order = 1;
		private $close_order = 2;
		private $delete_order = 3;
		private $modify_order = 4;
		private $sub_order = 5;
		var $rand_key;
		//------------------------------------------------------------------------------
		function db()
		{
			global $host;
			global $user;
			global $pass;
			global $db;
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->db = $db;
			$this->rand_key = '0iQx5oBk66oVZep';
			date_default_timezone_set('UTC');
		}
		//------------------------------------------------------------------------------
		private function connect()
		{
			//connecting to DB
			$mysqli = new mysqli($this->host, $this->user, $this->pass, $this->db);
			if ($mysqli->connect_errno)
				die("Failed to connect to MySQL: (".$mysqli->connect_errno .") ".$mysqli->connect_error);
			//set characters to utf8 for persian support
			$result = $mysqli->query('set names utf8');
			if (!$result) die($mysqli->error);
			$mysqli->set_charset("utf8");	
			return ($mysqli);
		}
		//------------------------------------------------------------------------------
		function getSymbol($source, $id, $ticket)
		{
			$mysqli = $this->connect();
			if ($source == 'provider')
				$query = "select symbol from provider_deals where pro_id = $id and ticket = $ticket && not symbol is null";
			else
				$query = "select symbol from client_deals where cli_id = $id and ticket = $ticket && not symbol is null";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			if ($result->num_rows <= 0) return ('');
			$row = $result->fetch_assoc();
			return ($row['symbol']);
		}
		//------------------------------------------------------------------------------
		function getStatusTitle($status)
		{
			switch ($status)
			{
				case $this->new_order:    return ('new');
				case $this->close_order:  return ('close');
				case $this->delete_order: return ('delete');
				case $this->modify_order: return ('modify');
				case $this->sub_order:    return ('partial close');
			}
		}
		//------------------------------------------------------------------------------
		function getTypeTitle($type)
		{
			switch ($type)
			{
				case 0: return ('buy');
				case 1: return ('sell');
				case 2: return ('buy limit');
				case 3: return ('sell limit');
				case 4: return ('buy stop');
				case 5: return ('sell stop');
			}
		}
		//------------------------------------------------------------------------------
		function getProviderAccounts($provider)
		{
			$pid = $this->getProviderID($provider);
			$mysqli = $this->connect();
			$query = "select distinct account_no from provider_deals where pro_id = $pid";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['account_no'] = $row['account_no'];
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getClientAccounts($client)
		{
			$cid = $this->getClientID($client);
			$mysqli = $this->connect();
			$query = "select distinct account_no from client_deals where cli_id = $cid";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['account_no'] = $row['account_no'];
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getProviderDeals($provider, $from, $to, $account_no)
		{
			$pid = $this->getProviderID($provider);
			$from = $from.' 00:00:00'; $to = $to.' 23:59:59';
			$mysqli = $this->connect();
			if ($account_no == '')
				$query = "select * from provider_deals where pro_id = $pid and catch_time between '$from' and '$to' order by catch_time desc, status desc";
			else
				$query = "select * from provider_deals where pro_id = $pid and account_no = $account_no and catch_time between '$from' and '$to' order by catch_time desc, status desc";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['catch_time'] = $row['catch_time'];
				$rows[$i]['account_no'] = $row['account_no'];
				$rows[$i]['ticket'] = $row['ticket'];
				if ($row['status'] == $this->new_order) $rows[$i]['symbol'] = $row['symbol'];
				else $rows[$i]['symbol'] = $this->getSymbol('provider', $pid, $row['ticket']);
				$rows[$i]['status'] = $this->getStatusTitle($row['status']);
				switch ($row['status'])
				{
					case $this->new_order:
						$rows[$i]['desc'] = $this->getTypeTitle($row['type']).' order opened@'.$row['open_price'];
						$rows[$i]['desc'] = $rows[$i]['desc'].', lot='.$row['lot'].', sl='.$row['sl'].', tp='.$row['tp'];
						break;
					case $this->close_order:
						$rows[$i]['desc'] = ' order closed@'.$row['close_price'].', profit='.$row['order_profit'];
						break;
					case $this->delete_order:
						$rows[$i]['desc'] = ' order deleted';
						break;
					case $this->modify_order:
						$rows[$i]['desc'] = ' order modified, open_price='.$row['open_price'].', sl='.$row['sl'].', tp='.$row['tp'];
						break;
					case $this->sub_order:
						$rows[$i]['ticket'] = $row['old_ticket'];
						$rows[$i]['desc'] = ' order partially closed, new_ticket='.$row['ticket'].', new_lot='.$row['lot'].', old lot='.$row['old_lot'];
						break;
				}
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getClientDeals($client, $from, $to, $account_no)
		{
			$cid = $this->getClientID($client);
			$from = $from.' 00:00:00'; $to = $to.' 23:59:59';
			$mysqli = $this->connect();
			if ($account_no == '')
				$query = "select * from client_deals where cli_id = $cid and catch_time between '$from' and '$to' order by catch_time desc, status desc";
			else
				$query = "select * from client_deals where cli_id = $cid and account_no = $account_no and catch_time between '$from' and '$to' order by catch_time desc, status desc";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['catch_time'] = $row['catch_time'];
				$rows[$i]['account_no'] = $row['account_no'];
				$rows[$i]['ticket'] = $row['ticket'];
				if ($row['status'] == $this->new_order) $rows[$i]['symbol'] = $row['symbol'];
				else $rows[$i]['symbol'] = $this->getSymbol('client', $cid, $row['ticket']);
				$rows[$i]['status'] = $this->getStatusTitle($row['status']);
				switch ($row['status'])
				{
					case $this->new_order:
						$rows[$i]['desc'] = $this->getTypeTitle($row['type']).' order opened@'.$row['open_price'];
						$rows[$i]['desc'] = $rows[$i]['desc'].', lot='.$row['lot'].', sl='.$row['sl'].', tp='.$row['tp'];
						break;
					case $this->close_order:
						$rows[$i]['desc'] = ' order closed@'.$row['close_price'].', profit='.$row['order_profit'];
						break;
					case $this->delete_order:
						$rows[$i]['desc'] = ' order deleted';
						break;
					case $this->modify_order:
						$rows[$i]['desc'] = ' order modified, open_price='.$row['open_price'].', sl='.$row['sl'].', tp='.$row['tp'];
						break;
					case $this->sub_order:
						$rows[$i]['ticket'] = $row['old_ticket'];
						$rows[$i]['desc'] = ' order partially closed, new_ticket='.$row['ticket'].', new_lot='.$row['lot'].', old lot='.$row['old_lot'];
						break;
				}
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getUsers()
		{
			$mysqli = $this->connect();
			$query = "select id, username, full_name, email from users order by id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['username'] = $row['username'];
				$rows[$i]['full_name'] = $row['full_name'];
				$rows[$i]['email'] = $row['email'];
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getUser($id)
		{
			$mysqli = $this->connect();
			$query = "select id, username, password, full_name, email from users where id = $id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row);
		}
		//------------------------------------------------------------------------------
		function getUserByName($username)
		{
			$mysqli = $this->connect();
			$query = "select id, username, full_name, email from users where username = $username";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row);
		}
		//------------------------------------------------------------------------------
		function addUser($username, $password, $full_name, $email)
		{
			$mysqli = $this->connect();
			$md5pwd = md5($password);
			$query = "insert into users (username, password, full_name, email) values ('$username', '$md5pwd', '$full_name', '$email')";
			$result = $mysqli->query($query);
			$id = $mysqli->insert_id;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function editUser($id, $username, $password, $full_name, $email)
		{
			$mysqli = $this->connect();
			if (strlen($password) == 32) $md5pwd = $password;
			else $md5pwd = md5($password);
			$query = "update users set username = '$username', full_name = '$full_name', password = '$md5pwd', email = '$email' where id = '$id'";
			$result = $mysqli->query($query);
			$id = $mysqli->insert_id;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function getProviders()
		{
			$mysqli = $this->connect();
			$query = "select id, name, full_name, email from providers";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['name'] = $row['name'];
				$rows[$i]['full_name'] = $row['full_name'];
				$rows[$i]['email'] = $row['email'];
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getProvider($id)
		{
			$mysqli = $this->connect();
			$query = "select id, name, password, full_name, email from providers where id = $id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row);
		}
		//------------------------------------------------------------------------------
		function getProviderID($name)
		{
			$mysqli = $this->connect();
			$query = "select id from providers where name = '$name'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row['id']);
		}
		//------------------------------------------------------------------------------
		function addProvider($name, $password, $full_name, $email)
		{
			$mysqli = $this->connect();
			$md5pwd = md5($password);
			$query = "insert into providers (name, password, full_name, email) values ('$name', '$md5pwd', '$full_name', '$email')";
			$result = $mysqli->query($query);
			$id = $mysqli->insert_id;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function editProvider($id, $name, $password, $full_name, $email)
		{
			$mysqli = $this->connect();
			if (strlen($password) == 32) $md5pwd = $password;
			else $md5pwd = md5($password);
			$query = "update providers set name = '$name', full_name = '$full_name', password = '$md5pwd', email = '$email' where id = $id";
			$result = $mysqli->query($query);
			$id = $mysqli->affected_rows;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function getClients()
		{
			$mysqli = $this->connect();
			$query = "select id, name, full_name, email from clients";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['name'] = $row['name'];
				$rows[$i]['full_name'] = $row['full_name'];
				$rows[$i]['email'] = $row['email'];
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getClient($id)
		{
			$mysqli = $this->connect();
			$query = "select id, name, full_name, password, email from clients where id = $id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row);
		}
		//------------------------------------------------------------------------------
		function getClientID($name)
		{
			$mysqli = $this->connect();
			$query = "select id from clients where name = '$name'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row['id']);
		}
		//------------------------------------------------------------------------------
		function addClient($name, $password, $full_name, $email)
		{
			$mysqli = $this->connect();
			$md5pwd = md5($password);
			$query = "insert into clients (name, password, full_name, email) values ('$name', '$md5pwd', '$full_name', '$email')";
			$result = $mysqli->query($query);
			$id = $mysqli->insert_id;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function editClient($id, $name, $password, $full_name, $email)
		{
			$mysqli = $this->connect();
			if (strlen($password) == 32) $md5pwd = $password;
			else $md5pwd = md5($password);
			$query = "update clients set name = '$name', full_name = '$full_name', password = '$md5pwd', email = '$email' where id = $id";
			$result = $mysqli->query($query);
			$id = $mysqli->affected_rows;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function getMappings()
		{
			$mysqli = $this->connect();
			$query = "select pc.id, p.name as pname, c.name as cname, pc.account_no, coalesce(pc.from_date, '') as fdate, coalesce(pc.to_date, '') as tdate";
			$query = $query." from provider_clients pc inner join providers p on (pc.pro_id = p.id) inner join clients c on (pc.cli_id = c.id)";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$rows = array(); $i = 0;
			while ($row = $result->fetch_assoc())
			{
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['pname'] = $row['pname'];
				$rows[$i]['cname'] = $row['cname'];
				$rows[$i]['account_no'] = $row['account_no'];
				$rows[$i]['fdate'] = $row['fdate'];
				$rows[$i]['tdate'] = $row['tdate'];
				$i++;
			}
			return ($rows);
		}
		//------------------------------------------------------------------------------
		function getMapping($id)
		{
			$mysqli = $this->connect();
			$query = "select pc.id, p.name as pname, c.name as cname, pc.account_no, coalesce(pc.from_date, '') as fdate, coalesce(pc.to_date, '') as tdate";
			$query = $query." from provider_clients pc inner join providers p on (pc.pro_id = p.id) inner join clients c on (pc.cli_id = c.id)";
			$query = $query." where pc.id = $id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			return ($row);
		}
		//------------------------------------------------------------------------------
		function addMapping($pname, $cname, $account_no, $fdate, $tdate)
		{
			$pid = $this->getProviderID($pname);
			$cid = $this->getClientID($cname);
			if ($fdate == '') $fdate = 'NULL'; else $fdate = "'".$fdate."'";
			if ($tdate == '') $tdate = 'NULL'; else $tdate = "'".$tdate."'";
			if ($account_no == '') $account_no = 'NULL';
			$mysqli = $this->connect();
			$query = "insert into provider_clients (pro_id, cli_id, account_no, from_date, to_date) values ($pid, $cid, $account_no, $fdate, $tdate)";
			$result = $mysqli->query($query);
			$id = $mysqli->insert_id;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function editMapping($id, $pname, $cname, $account_no, $fdate, $tdate, &$err_desc = '')
		{
			$pid = $this->getProviderID($pname);
			$cid = $this->getClientID($cname);
			if ($fdate == '') $fdate = 'NULL'; else $fdate = "'".$fdate."'";
			if ($tdate == '') $tdate = 'NULL'; else $tdate = "'".$tdate."'";
			if ($account_no == '') $account_no = 'NULL';
			$mysqli = $this->connect();
			$query = "update provider_clients set account_no = $account_no, from_date = $fdate, to_date = $tdate where id = $id";
			$result = $mysqli->query($query);
			$id = $mysqli->affected_rows;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		function export_html_table($rows, $header = '', $columns = array(), $headers = array(), $width = array(), $key = 'id', $options = array(), $handler_url='')
		{
			if (count($columns) == 0)
			{
				$columns = array(); $hcolumns = array();
				$i = 0;
				foreach ($rows[$i] as $key=>$value)
				{
					$columns[$i] = $value;
					$width[$i] = 30;
					$i++;
				}
			}
			for ($j = 0; $j < count($columns); $j++) $hcolumns[$j] = $columns[$j];
			if (count($headers) == 0)
				for ($j = 0; $j < count($columns); $j++) $headers[$j] = $columns[$j];
			if (count($options) != 0) $cols = count($columns)+2;
			else $cols = count($columns)+1;
			$out = '<table class="css-table">';
			if ($header != '') $out = $out."<tr><th colspan='".$cols."'>".$header."</th></tr>";
			if (count($columns) != 0)
			{
				$out = $out."<tr><th width='25px'>row</th>";
				for ($i = 0; $i < count($columns); $i++)
				{
					if ($width[$i] == 0)
						$out = $out.'<th style="display:none;"></th>';
					else
						$out = $out."<th width='".$width[$i]."px'>".$headers[$i]."</th>";
				}
				if (count($options) != 0)
				{
					$out = $out."<th width='30px'>Options</th>";
				}
				$out = $out.'</tr>';
			}
			for ($i = 0; $i < count($rows); $i++)
			{
				$out = $out.'<tr><td>'.($i+1).'</td>';
				for ($j = 0; $j < count($columns); $j++)
				{
					if ($width[$j] == 0)
						$out = $out.'<td style="display:none;">'.$rows[$i][$columns[$j]].'</td>';
					else
						$out = $out."<td>".$rows[$i][$columns[$j]]."</td>";
				}
				if (count($options) != 0)
				{
					$out = $out.'<td>';
					for ($j = 0; $j < count($options); $j++)
					{
						switch ($options[$j])
						{
							case 'add':
								$out = $out."<a href='".$handler_url."&action=add'><img src='resources/images/add.png' alt='add' title='add'></a>&nbsp";
								break;
							case 'delete':
								$out = $out."<a href='".$handler_url."&action=delete&".$key."=".$rows[$i][$key]."'><img src='resources/images/delete.png' alt='delete' title='delete'></a>&nbsp";
								break;
							case 'edit':
								$out = $out."<a href='".$handler_url."&action=edit&".$key."=".$rows[$i][$key]."'><img src='resources/images/edit.png' alt='edit' title='edit'></a>&nbsp";
								break;
						}
					}
					$out = $out.'</td>';
				}
				$out = $out.'</tr>';
			}
			$out = $out.'</table>';
			return ($out);
		}
		//------------------------------------------------------------------------------
		function Login()
		{
			if (empty($_POST['username'])) return false;
			if (empty($_POST['password'])) return false;
			$username = trim($_POST['username']);
			$password = trim($_POST['password']);
			if (!$this->CheckLoginInDB($username, $password)) return false;
			$_SESSION[$this->GetLoginSessionVar()] = $username;
			return true;
		}
		//------------------------------------------------------------------------------
		function CheckLogin()
		{
			if (!isset($_SESSION)) session_start();
			$sessionvar = $this->GetLoginSessionVar();
			if (empty($_SESSION[$sessionvar]))	return false;
			return true;
		}
		//------------------------------------------------------------------------------
		function CheckLoginInDB($username, $password)
		{
			$mysqli = $this->connect();
			$pwdmd5 = md5($password);
			$query = "select * from users where username = '$username' and password='$pwdmd5'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return false; }
			else if ($result->num_rows <= 0) return false;
			$row = $result->fetch_assoc();
			$_SESSION['name_of_user']  = $row['full_name'];
			$_SESSION['email_of_user'] = $row['email'];
			return true;
		}
		//------------------------------------------------------------------------------
		function GetLoginSessionVar()
		{
			$retvar = md5($this->rand_key);
			$retvar = 'usr_'.substr($retvar, 0, 10);
			return $retvar;
		}
		//------------------------------------------------------------------------------
		function LogOut()
		{
			session_start();
			$sessionvar = $this->GetLoginSessionVar();
			$_SESSION[$sessionvar] = NULL;
			unset($_SESSION[$sessionvar]);
		}
		//------------------------------------------------------------------------------
	}
?>
