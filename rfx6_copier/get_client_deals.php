<?php
	//==================================================================================
	require_once("config.php");
	include_once 'db.php';
	//==================================================================================
	if (isset($_GET['client'])) $client = $_GET['client']; else die('-1<br>invalid parameter format');
	if (isset($_GET['account_no'])) $account_no = $_GET['account_no']; else die('-1<br>invalid parameter format');
	if (isset($_GET['delay'])) $delay = intval($_GET['delay']); else die('-1<br>invalid parameter format');
	if (isset($_GET['exceptions'])) $exceptions = $_GET['exceptions']; else $exceptions = '0';
	$db = new db();
	$deals = array(); $deals_count = 0; $error = '';
	$id = $db->get_client_deals($client, $account_no, $delay, $exceptions, $deals, $deals_count, $error);
	if ($id < 0) echo $id.'<br>'.$error;
	else
	{
		echo $deals_count.'<br>'.$deals_count.' deals found<br>';
		for ($i = 0; $i < $deals_count; $i++)
		{
			$date = new DateTime();
			$date2 = new DateTime($deals[$i]['catch_time']);
			$late = $date->format('U') - $date2->format('U');
			$deal = '';
			switch (intval($deals[$i]['status']))
			{
				case $new_order:
					$deal = $new_order.','.$deals[$i]['id'].','.$late.','.$deals[$i]['type'].',';
					$deal = $deal.$deals[$i]['lot'].','.$deals[$i]['open_price'].','.$deals[$i]['sl'].',';
					$deal = $deal.$deals[$i]['tp'].','.$deals[$i]['symbol'].','.$deals[$i]['balance'].',';
					$deal = $deal.$deals[$i]['equity'].'<br>';
					break;
				case $close_order:
					$deal = $close_order.','.$deals[$i]['id'].','.$late.','.$deals[$i]['cld_ticket'].',';
					$deal = $deal.$deals[$i]['close_price'].'<br>';
					break;
				case $delete_order:
					$deal = $delete_order.','.$deals[$i]['id'].','.$late.','.$deals[$i]['cld_ticket'].'<br>';
					break;
				case $modify_order:
					$deal = $modify_order.','.$deals[$i]['id'].','.$late.','.$deals[$i]['cld_ticket'].',';
					$deal = $deal.$deals[$i]['open_price'].','.$deals[$i]['sl'].','.$deals[$i]['tp'].'<br>';
					break;
				case $sub_order:
					$deal = $sub_order.','.$deals[$i]['id'].','.$late.','.$deals[$i]['cld_ticket'].',';
					$deal = $deal.$deals[$i]['lot'].','.$deals[$i]['old_lot'].'<br>';
					break;
			}
			echo $deal;
		}
	}
	//==================================================================================
?>
