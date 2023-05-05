<?php
	//==================================================================================
	require_once("config.php");
	include_once 'db.php';
	//==================================================================================
	if (isset($_GET['status'])) $status = intval($_GET['status']); else die('-1<br>invalid parameter format');
	if (isset($_GET['client'])) $client = $_GET['client']; else die('-1<br>invalid parameter format');
	if (isset($_GET['broker'])) $broker = $_GET['broker']; else die('-1<br>invalid parameter format');
	if (isset($_GET['account_no'])) $account_no = $_GET['account_no']; else die('-1<br>invalid parameter format');
	if (isset($_GET['computer'])) $computer = $_GET['computer']; else die('-1<br>invalid parameter format');
	if (isset($_GET['ip'])) $ip = $_GET['ip']; else die('-1<br>invalid parameter format');
	if (isset($_GET['prd_id'])) $prd_id = $_GET['prd_id']; else die('-1<br>invalid parameter format');
	if (isset($_GET['ticket'])) $ticket = $_GET['ticket']; else die('-1<br>invalid parameter format');
	if (isset($_GET['delay'])) $delay = $_GET['delay']; else die('-1<br>invalid parameter format');
	$ip = substr($ip, 0, 50);
	switch ($status)
	{
	  case $new_order:
		if (isset($_GET['symbol'])) $symbol = $_GET['symbol']; else die('-1<br>invalid parameter format');
		if (isset($_GET['type'])) $type = $_GET['type']; else die('-1<br>invalid parameter format');
		if (isset($_GET['lot'])) $lot = $_GET['lot']; else die('-1<br>invalid parameter format');
		if (isset($_GET['balance'])) $balance = $_GET['balance']; else die('-1<br>invalid parameter format');
		if (isset($_GET['equity'])) $equity = $_GET['equity']; else die('-1<br>invalid parameter format');
		if (isset($_GET['open_time'])) $open_time = $_GET['open_time']; else die('-1<br>invalid parameter format');
		if (isset($_GET['open_price'])) $open_price = $_GET['open_price']; else die('-1<br>invalid parameter format');
		if (isset($_GET['sl'])) $sl = $_GET['sl']; else die('-1<br>invalid parameter format');
		if (isset($_GET['tp'])) $tp = $_GET['tp']; else die('-1<br>invalid parameter format');
		$db = new db();
		$error = '';
		$id = $db->add_client_deal($client, $broker, $account_no, $prd_id, $ticket, $symbol, $type, $lot,
			$balance, $equity, $open_time, $open_price, $sl, $tp, $ip, $computer, $delay, $error);
		if ($id < 0) echo $id.'<br>'.$error;
		else echo $id.'<br>succeeded';
		break;
	  case $close_order:
		if (isset($_GET['close_price'])) $close_price = $_GET['close_price']; else die('-1<br>invalid parameter format');
		if (isset($_GET['profit'])) $profit = $_GET['profit']; else die('-1<br>invalid parameter format');
		if (isset($_GET['commission'])) $commission = $_GET['commission']; else die('-1<br>invalid parameter format');
		if (isset($_GET['swap'])) $swap = $_GET['swap']; else die('-1<br>invalid parameter format');
		$db = new db();
		$error = '';
		$id = $db->close_client_deal($client, $broker, $account_no, $prd_id, $ticket, $close_price, $profit,
			$commission, $swap, $ip, $computer, $delay, $error);
		if ($id < 0) echo $id.'<br>'.$error;
		else echo $id.'<br>succeeded';
		break;
	  case $delete_order:
		$db = new db();
		$error = '';
		$id = $db->delete_client_deal($client, $broker, $account_no, $prd_id, $ticket, $ip, $computer, $delay, $error);
		if ($id < 0) echo $id.'<br>'.$error;
		else echo $id.'<br>succeeded';
		break;
	  case $modify_order:
		if (isset($_GET['open_price'])) $open_price = $_GET['open_price']; else die('-1<br>invalid parameter format');
		if (isset($_GET['sl'])) $sl = $_GET['sl']; else die('-1<br>invalid parameter format');
		if (isset($_GET['tp'])) $tp = $_GET['tp']; else die('-1<br>invalid parameter format');
		$db = new db();
		$error = '';
		$id = $db->modify_client_deal($client, $broker, $account_no, $prd_id, $ticket, $open_price, $sl, $tp,
			$ip, $computer, $delay, $error);
		if ($id < 0) echo $id.'<br>'.$error;
		else echo $id.'<br>succeeded';
		break;
	  case $sub_order:
		if (isset($_GET['old_ticket'])) $old_ticket = $_GET['old_ticket']; else die('-1<br>invalid parameter format');
		if (isset($_GET['lot'])) $lot = $_GET['lot']; else die('-1<br>invalid parameter format');
		if (isset($_GET['old_lot'])) $old_lot = $_GET['old_lot']; else die('-1<br>invalid parameter format');
		$db = new db();
		$error = '';
		$id = $db->suborder_client_deal($client, $broker, $account_no, $prd_id, $old_ticket, $ticket,
			$old_lot, $lot, $ip, $computer, $delay, $error);
		if ($id < 0) echo $id.'<br>'.$error;
		else echo $id.'<br>succeeded';
		break;
	}
	//==================================================================================
?>