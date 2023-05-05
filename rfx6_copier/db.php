<?php
	//==================================================================================
	require_once("config.php");
	//==================================================================================
	class db
	{
		private $host;
		private $user;
		private $pass;
		private $db;
		private $provider_not_found = -10;
		private $client_not_found = -11;
		private $ticket_already_exist = -20;
		private $authentication_failed = -30;
		private $parent_ticket_not_found = -40;
		private $deal_is_repeated = -50;
		private $client_no_provider_found = -60;
		private $no_new_deal = -70;
		private $provider_deal_not_found = -80;
		private $new_order = 1;
		private $close_order = 2;
		private $delete_order = 3;
		private $modify_order = 4;
		private $sub_order = 5;
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
		public function add_provider($name, $pass, $full_name, $email)
		{
			$mysqli = $this->connect();
			$query = "insert into providers (name, full_name, password, email)";
			$password = md5($pass);
			$query = $query."values ('$name', '$password', '$full_name', '$email')";
			$result = $mysqli->query($query);
			$id = $mysqli->insert_id;
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			return ($id);
		}
		//------------------------------------------------------------------------------
		public function authenticate_provider($name, $pass, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$md5pass = md5($pass);
			$query = "select coalesce(max(id), 0) as id from providers ";
			$query = $query."where name = '$name' and password = '$md5pass'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'authentication failed'; return ($this->authentication_failed); }
			return ($row['id']);
		}
		//------------------------------------------------------------------------------
		public function authenticate_client($name, $pass, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$md5pass = md5($pass);
			$query = "select coalesce(max(id), 0) as id from clients ";
			$query = $query."where name = '$name' and password = '$md5pass'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'authentication failed'; return ($this->authentication_failed); }
			return ($row['id']);
		}
		//------------------------------------------------------------------------------
		public function add_provider_deal($provider, $broker, $account_no, $ticket, $symbol, $type
			, $lot, $balance, $equity, $open_time, $open_price, $sl, $tp, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from providers where name = '$provider'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'provider not found'; return ($this->provider_not_found); }
			$pro_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->new_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'ticket already exists'; return ($this->ticket_already_exist); }
			else
			{
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into provider_deals (pro_id, status, account_no, broker, ticket, type, ";
				$query = $query."symbol, lot, equity, balance, open_time, open_price, sl, tp, ";
				$query = $query."catch_time, register_time, register_ip, computer_name) ";
				$query = $query."values ($pro_id, ".$this->new_order.", $account_no, '$broker', $ticket, $type, ";
				$query = $query."'$symbol', $lot, $equity, $balance, $open_time, $open_price, $sl, $tp, ";
				$query = $query."'$msd2', '$msd', '$ip', '$computer')";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function modify_provider_deal($provider, $broker, $account_no, $ticket, $open_price, $sl, $tp, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from providers where name = '$provider'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'provider not found'; return ($this->provider_not_found); }
			$pro_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->modify_order;
			$query = $query." and open_price = $open_price and sl = $sl and tp = $tp";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and (status = ".$this->new_order;
			$query = $query." or status=".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$prd_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into provider_deals (pro_id, status, account_no, broker, ticket, open_price, sl, tp, ";
				$query = $query."catch_time, register_time, register_ip, computer_name, prd_id) ";
				$query = $query."values ($pro_id, ".$this->modify_order.", $account_no, '$broker', $ticket, ";
				$query = $query."$open_price, $sl, $tp, '$msd2', '$msd', '$ip', '$computer', $prd_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function close_provider_deal($provider, $broker, $account_no, $ticket, $close_price, $profit, $commission, $swap, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from providers where name = '$provider'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'provider not found'; return ($this->provider_not_found); }
			$pro_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->close_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and (status=".$this->new_order;
			$query = $query." or status=".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$prd_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into provider_deals (pro_id, status, account_no, broker, ticket, close_price, order_profit, order_commission, ";
				$query = $query."order_swap, catch_time, register_time, register_ip, computer_name, prd_id) ";
				$query = $query."values ($pro_id, ".$this->close_order.", $account_no, '$broker', $ticket, ";
				$query = $query."$close_price, $profit, $commission, $swap, '$msd2', '$msd', '$ip', '$computer', $prd_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function delete_provider_deal($provider, $broker, $account_no, $ticket, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from providers where name = '$provider'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'provider not found'; return ($this->provider_not_found); }
			$pro_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->delete_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->new_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$prd_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into provider_deals (pro_id, status, account_no, broker, ticket, ";
				$query = $query."catch_time, register_time, register_ip, computer_name, prd_id) ";
				$query = $query."values ($pro_id, ".$this->delete_order.", $account_no, '$broker', $ticket, ";
				$query = $query."'$msd2', '$msd', '$ip', '$computer', $prd_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function suborder_provider_deal($provider, $broker, $account_no, $ticket, $new_ticket, $old_lot, $new_lot, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from providers where name = '$provider'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'provider not found'; return ($this->provider_not_found); }
			$pro_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $new_ticket and status = ".$this->sub_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from provider_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and (status = ".$this->new_order;
			$query = $query." or status=".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$prd_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into provider_deals (pro_id, status, account_no, broker, ticket, old_ticket, ";
				$query = $query."lot, old_lot, catch_time, register_time, register_ip, computer_name, prd_id) ";
				$query = $query."values ($pro_id, ".$this->sub_order.", $account_no, '$broker', $new_ticket, ";
				$query = $query."$ticket, $new_lot, $old_lot, '$msd2', '$msd', '$ip', '$computer', $prd_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function add_client_deal($client, $broker, $account_no, $prd_id, $ticket, $symbol, $type
			, $lot, $balance, $equity, $open_time, $open_price, $sl, $tp, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from clients where name = '$client'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'client not found'; return ($this->client_not_found); }
			$cli_id = $row['id'];
			$query = "select coalesce(max(ticket), 0) as ticket from provider_deals where id = $prd_id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['ticket'] == 0) { $err_desc = 'provider deal not found'; return ($this->provider_deal_not_found); }
			$prd_ticket = $row['ticket'];
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->new_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'ticket already exists'; return ($this->ticket_already_exist); }
			else
			{
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into client_deals (prd_id, cli_id, prd_ticket, status, account_no, broker, ticket, ";
				$query = $query."type, symbol, lot, equity, balance, open_time, open_price, sl, tp, ";
				$query = $query."catch_time, register_time, register_ip, computer_name) ";
				$query = $query."values ($prd_id, $cli_id, $prd_ticket, ".$this->new_order.", $account_no, '$broker', ";
				$query = $query."$ticket, $type, '$symbol', $lot, $equity, $balance, $open_time, $open_price, $sl, $tp, ";
				$query = $query."'$msd2', '$msd', '$ip', '$computer')";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function modify_client_deal($client, $broker, $account_no, $prd_id, $ticket, $open_price, $sl, $tp, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from clients where name = '$client'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'client not found'; return ($this->client_not_found); }
			$cli_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->modify_order;
			$query = $query." and open_price = $open_price and sl = $sl and tp = $tp";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and (status = ".$this->new_order;
			$query = $query." or status=".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$cld_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into client_deals (prd_id, cli_id, status, account_no, broker, ticket, open_price, sl, tp, ";
				$query = $query."catch_time, register_time, register_ip, computer_name, cld_id) ";
				$query = $query."values ($prd_id, $cli_id, ".$this->modify_order.", $account_no, '$broker', $ticket, ";
				$query = $query."$open_price, $sl, $tp, '$msd2', '$msd', '$ip', '$computer', $cld_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function close_client_deal($client, $broker, $account_no, $prd_id, $ticket, $close_price, $profit, $commission, $swap, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from clients where name = '$client'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'client not found'; return ($this->client_not_found); }
			$cli_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->close_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and (status=".$this->new_order;
			$query = $query." or status=".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$cld_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into client_deals (prd_id, cli_id, status, account_no, broker, ticket, close_price, order_profit, order_commission, ";
				$query = $query."order_swap, catch_time, register_time, register_ip, computer_name, cld_id) ";
				$query = $query."values ($prd_id, $cli_id, ".$this->close_order.", $account_no, '$broker', $ticket, ";
				$query = $query."$close_price, $profit, $commission, $swap, '$msd2', '$msd', '$ip', '$computer', $cld_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function delete_client_deal($client, $broker, $account_no, $prd_id, $ticket, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from clients where name = '$client'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'client not found'; return ($this->client_not_found); }
			$cli_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->delete_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and status = ".$this->new_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$cld_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into client_deals (pro_id, status, account_no, broker, ticket, ";
				$query = $query."catch_time, register_time, register_ip, computer_name, cld_id) ";
				$query = $query."values ($pro_id, ".$this->delete_order.", $account_no, '$broker', $ticket, ";
				$query = $query."'$msd2', '$msd', '$ip', '$computer', $cld_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function suborder_client_deal($client, $broker, $account_no, $prd_id, $ticket, $new_ticket, $old_lot, $new_lot, $ip, $computer, $delay, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from clients where name = '$client'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'client not found'; return ($this->client_not_found); }
			$cli_id = $row['id'];
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $new_ticket and status = ".$this->sub_order;
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] <> 0) { $err_desc = 'deal is repeated'; return ($this->deal_is_repeated); }
			$query = "select coalesce(max(ticket), 0) as ticket from provider_deals where id = $prd_id";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			$prd_ticket = $row['ticket'];
			$query = "select coalesce(max(id), 0) as id from client_deals ";
			$query = $query."where account_no = $account_no and ticket = $ticket and (status = ".$this->new_order;
			$query = $query." or status=".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'parent ticket not fond'; return ($this->parent_ticket_not_found); }
			else
			{
				$cld_id = $row['id'];
				$date = new DateTime();
				$date2 = new DateTime($date->format('Y-m-d H:i:s'));
				$date2->sub(new DateInterval('PT'.$delay.'S'));
				$msd =$date->format('Y-m-d H:i:s'); $msd2 = $date2->format('Y-m-d H:i:s');
				$query = "insert into client_deals (prd_id, cli_id, status, account_no, broker, ticket, ";
				$query = $query."prd_ticket, lot, catch_time, register_time, register_ip, computer_name, cld_id) ";
				$query = $query."values ($prd_id, $cli_id, ".$this->sub_order.", $account_no, '$broker', $new_ticket, ";
				$query = $query."$prd_ticket, $new_lot, '$msd2', '$msd', '$ip', '$computer', $cld_id)";
				$result = $mysqli->query($query);
				$id = $mysqli->insert_id;
				if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
				return ($id);
			}
		}
		//------------------------------------------------------------------------------
		public function get_client_deals($client, $account_no, $delay, $exceptions, array &$deals, &$deals_count, &$err_desc = '')
		{
			$mysqli = $this->connect();
			$query = "select coalesce(max(id), 0) as id from clients where name = '$client'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['id'] == 0) { $err_desc = 'client not found'; return ($this->client_not_found); }
			$cli_id = $row['id'];
			$result->close();
			$date = new DateTime();
			$d = $date->format('Y-m-d');
			$query = "select pro_id from provider_clients where cli_id = $cli_id and (account_no = $account_no or account_no is null)";
			$query = $query." and coalesce(from_date, '$d') <= '$d' and coalesce(to_date, '$d') >= '$d'";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$providers = '';
			while ($row = $result->fetch_assoc())
			{
				$providers = $providers.$row['pro_id'].',';
			}
			if ($providers <> '') $providers = substr($providers, 0, strlen($providers) - 1);
			if ($providers == '') { $err_desc = 'no provider found for '.$client; return ($this->client_no_provider_found); }
			$result->close();
			$date2 = new DateTime();
			$date2->sub(new DateInterval('PT'.$delay.'S'));
			$d = $date2->format('Y-m-d H:i:s');
			$query = "select * from provider_deals where not id in ($exceptions) and pro_id in ($providers)";
			$query = $query." and catch_time >= '$d' order by catch_time, status asc";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$i = -1;
			$rows = array();
			while ($row = $result->fetch_assoc())
			{
				$i++;
				$rows[$i]['id'] = $row['id'];
				$rows[$i]['prd_id'] = $row['prd_id'];
				$rows[$i]['status'] = $row['status'];
				$rows[$i]['ticket'] = $row['ticket'];
				$rows[$i]['type'] = $row['type'];
				$rows[$i]['symbol'] = $row['symbol'];
				$rows[$i]['lot'] = $row['lot'];
				$rows[$i]['equity'] = $row['equity'];
				$rows[$i]['balance'] = $row['balance'];
				$rows[$i]['open_price'] = $row['open_price'];
				$rows[$i]['sl'] = $row['sl'];
				$rows[$i]['tp'] = $row['tp'];
				$rows[$i]['close_price'] = $row['close_price'];
				$rows[$i]['catch_time'] = $row['catch_time'];
				if ($row['status'] <> $this->new_order)
				{
					if ($row['status'] == $this->sub_order)
					{
						$rows[$i]['cld_ticket'] = $this->GetClientTicket($mysqli, $cli_id, $account_no, $row['old_ticket'], $err);
					}
					else
					{
						$rows[$i]['cld_ticket'] = $this->GetClientTicket($mysqli, $cli_id, $account_no, $row['ticket'], $err);
					}
					if ($rows[$i]['cld_ticket'] == -1)
					{
						$rows[$i]['id'] = -1;
					}
				}
				else $rows[$i]['cld_ticket'] = -1;
				$rows[$i]['old_lot'] = $row['old_lot'];
				$rows[$i]['old_ticket'] = $row['old_ticket'];
			}
			$prd_ids = '';
			if ($i == -1) { $err_desc = 'no new deal'; return($this->no_new_deal); }
			for ($j = 0; $j <= $i; $j++)
			{
				$prd_ids = $prd_ids.$rows[$j]['id'].',';
			}
			if ($prd_ids <> '') $prd_ids = substr($prd_ids, 0, strlen($prd_ids) - 1);
			else $prd_ids = '0';
			$result->close();
			$query = "select prd_id from client_deals where cli_id = $cli_id and account_no = $account_no and prd_id in ($prd_ids)";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			while ($row = $result->fetch_assoc())
			{
				for ($j = 0; $j <= $i; $j++)
				{
					if ($row['prd_id'] == $rows[$j]['id']) { $rows[$j]['id'] = -1; break; }
				}
			}
			for ($k = 0; $k <= $i; $k++)
			{
				if ($rows[$k]['status'] == $this->delete_order or $rows[$k]['status'] == $this->close_order)
				{
					for ($j = 0; $j < $k; $j++)
					{
						if ($rows[$j]['id'] <> -1 and $rows[$j]['ticket'] == $rows[$k]['ticket'] and $rows[$j]['status'] == $this->new_order)
							break;
					}
					if ($j <> $k) { $rows[$k]['id'] = -1; $rows[$j]['id'] = -1; }
				}
			}
			$deals_count = 0;
			for ($j = 0; $j <= $i; $j++)
			{
				if ($rows[$j]['id'] <> -1)
				{
					$deals[$deals_count]['id'] = $rows[$j]['id'];
					$deals[$deals_count]['prd_id'] = $rows[$j]['prd_id'];
					$deals[$deals_count]['status'] = $rows[$j]['status'];
					$deals[$deals_count]['ticket'] = $rows[$j]['ticket'];
					$deals[$deals_count]['cld_ticket'] = $rows[$j]['cld_ticket'];
					$deals[$deals_count]['type'] = $rows[$j]['type'];
					$deals[$deals_count]['symbol'] = $rows[$j]['symbol'];
					$deals[$deals_count]['lot'] = $rows[$j]['lot'];
					$deals[$deals_count]['equity'] = $rows[$j]['equity'];
					$deals[$deals_count]['balance'] = $rows[$j]['balance'];
					$deals[$deals_count]['open_price'] = $rows[$j]['open_price'];
					$deals[$deals_count]['sl'] = $rows[$j]['sl'];
					$deals[$deals_count]['tp'] = $rows[$j]['tp'];
					$deals[$deals_count]['close_price'] = $rows[$j]['close_price'];
					$deals[$deals_count]['catch_time'] = $rows[$j]['catch_time'];
					$deals[$deals_count]['old_ticket'] = $rows[$j]['old_ticket'];
					$deals[$deals_count]['old_lot'] = $rows[$j]['old_lot'];
					$deals_count++;
				}
			}
			if ($deals_count == 0) { $err_desc = 'no new deal'; return($this->no_new_deal); }
			else { $err_desc = $deals_count.' deal(s) found'; return($deals_count); }
		}
		//------------------------------------------------------------------------------
		public function GetClientTicket($mysqli, $client_id, $account_no, $ticket, &$err_desc = '')
		{
			$query = "select coalesce(max(ticket), 0) as ticket from client_deals ";
			$query = $query."where cli_id = $client_id and account_no = $account_no and prd_ticket = $ticket and ";
			$query = $query."(status = ".$this->new_order." or status = ".$this->sub_order.")";
			$result = $mysqli->query($query);
			if (!$result) { $err_desc = 'Invalid query: '.$query.' '.$mysqli->error; return (-1); }
			$row = $result->fetch_assoc();
			if ($row['ticket'] == 0) { $err_desc = 'client ticket not found'; return (-1); }
			else return ($row['ticket']);
		}
		//------------------------------------------------------------------------------
	}
?>