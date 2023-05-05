<?
require_once('include.php');


$action='';
if(isset($_POST['action']))
$action=$_POST['action'];

$password='';
if(isset($_POST['password']))
$password=$_POST['password'];

$username='';
if(isset($_POST['username']))
$username=$_POST['username'];

$email='';
if(isset($_POST['email']))
$email=$_POST['email'];


$period='';
if(isset($_POST['period']))
$period=$_POST['period'];

$account=array('username' => $username,'password' => $password, 'period' => $period, 'email' => $email);

switch($action){
case 'addaccount':
add_account($account);
break;
case 'renewaccount':
renew_account($account);
break;



}



function add_account($account) {
$username=$account['username'];
$password=md5($account['password']);
$period=$account['period'];
$email=$account['email'];

global $now;
$expire=mktime(0,0,0,date("m")+$period,date("d"),date("Y"));

$db=new db();

$db->non_select("insert into clients (name,password,full_name,email,expire) values ('$username','$password',' ','$email','$expire')");
$record=$db->last_insert();
if($record){
$array= new ArrayToXML();
echo $array->toXml(array('action'=>'addaccount','result'=>'success'),'fxturn');

}else{
$array= new ArrayToXML();
echo $array->toXml(array('action'=>'addaccount','result'=>'error','message'=>'Username is not valid'),'fxturn');
}
$db->close();

}


function renew_account($account) {
$username=$account['username'];
$period=$account['period'];

global $now;
$expire=mktime(0,0,0,date("m")+$period,date("d"),date("Y"));

$db=new db();


$db->non_select("update clients set expire='$expire' where name='$username'");
$record=$db->last_insert();

$db->close();

if($record){
$array= new ArrayToXML();
echo $array->toXml(array('action'=>'renewaccount','result'=>'success'),'fxturn');
}else{
$array= new ArrayToXML();
echo $array->toXml(array('action'=>'renewaccount','result'=>'error','message'=>'Failed'),'fxturn');
}
$db->close();
}



?>
