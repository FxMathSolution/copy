<?
$ini_set['dbhost']='localhost';
$ini_set['dbusername']='rfx6_copier';
$ini_set['dbpassword']='copier123321';
$ini_set['dbdatabase']='rfx6_copier';

$now=mktime(date("G"),date("i"),date("s"),date("m"),date("d"),date("Y"));

class db{
var $query='';
var $db='';

function db(){

global $ini_set;
$this->db=@mysql_connect($ini_set['dbhost'], $ini_set['dbusername'], $ini_set['dbpassword']);
if (!$this->db) die ($this->debug(true));
$selectdb=@mysql_select_db($ini_set['dbdatabase']);
if (!$selectdb) die ($this->debug());
}

function select($sql){
$rs=@mysql_query($sql,$this->db);
if ($this->error()) die ($this->debug());
$arr=array();
$x=1;
while($row = @mysql_fetch_array($rs)) {
for($y=0;$y<@mysql_num_fields($rs);$y++){
$arr[$x][$y]=$row[$y];
}
$x++;
}
@mysql_free_result($rs);
if($x>1)
return $arr;

}

function non_select($sql){
mysql_query($sql);
if ($this->error()&&mysql_errno($this->db)!="1062") die ($this->debug());
}

function do_transaction($sql_1,$sql_2){
if(!mysql_query ("BEGIN",$this->db))
return (0);
if(!mysql_query ($sql_1,$this->db))
return (0);
if(!mysql_query ($sql_2,$this->db))
return (0);
if(!mysql_query ("COMMIT",$this->db))
return (0);
return (1);
}

function do_rollback(){
mysql_query ("ROLLBACK",$this->db);
}

function debug($type="", $action="", $tablename=""){
switch ($type){
case "connect":
$message = "MySQL Error Occured";
$result = mysql_errno() . ": " . mysql_error();
$query = "";
$output = "Could not connect to the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
break;
case "array":
$message = $action." Error Occured";
$result = "Could not update ".$tablename." as variable supplied must be an array.";
$query = "";
$output = "Sorry an error has occured accessing the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
break;
default:
if (mysql_errno($this->db)){
$message = "MySQL Error Occured";
$result = mysql_errno($this->db) . ": " . mysql_error($this->db);
$output = "Sorry an error has occured accessing the database. Be sure to check that your database connection settings are correct and that the MySQL server in running.";
}else {
$message = "MySQL Query Executed Succesfully.";
$result = mysql_affected_rows($this->db) . " Rows Affected";
$output = "view logs for details";
}
$linebreaks = array("\n", "\r");
if($this->query != "") $query = "QUERY = " . str_replace($linebreaks, " ", $this->query); else $query = "";
break;
}
		
$output = "<b style='font-family: Arial, Helvetica, sans-serif; color: #0B70CE; font-size:10pt;'>".$message."</b><br />\n<span style='font-family: Arial, Helvetica, sans-serif; color: #000000; font-size:9pt;'>".$result."</span><br />\n<p style='Courier New, Courier, mono; border: 1px dashed #666666; padding: 10px; color: #000000; font-size:9pt;'>".$query."</p>\n";
return $output;
}
	
function error()
{
if (mysql_errno($this->db)) return true; else return false;
}
	
	
function affected(){
return mysql_affected_rows($this->db);
}
	
function close(){
mysql_close($this->db);
} 

function state(){
return mysql_stat($this->db);
}

function last_insert(){
return mysql_insert_id($this->db);
}

}


    class ArrayToXML
    {
    /**
    * The main function for converting to an XML document.
    * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
    *
    * @param array $data
    * @param string $rootNodeName - what you want the root node to be - defaultsto data.
    * @param SimpleXMLElement $xml - should only be used recursively
    * @return string XML
    */
    public static function toXml($data, $rootNodeName = 'data', $xml=null)
    {
    // turn off compatibility mode as simple xml throws a wobbly if you don't.
    if (ini_get('zend.ze1_compatibility_mode') == 1)
    {
    ini_set ('zend.ze1_compatibility_mode', 0);
    }
     
    if ($xml == null)
    {
    $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
    }
     
    // loop through the data passed in.
    foreach($data as $key => $value)
    {
    // no numeric keys in our xml please!
    if (is_numeric($key))
    {
    // make string key...
    $key = "unknownNode_". (string) $key;
    }
     
    // replace anything not alpha numeric
    $key = preg_replace('/[^a-z]/i', '', $key);
     
    // if there is another array found recrusively call this function
    if (is_array($value))
    {
    $node = $xml->addChild($key);
    // recrusive call.
    ArrayToXML::toXml($value, $rootNodeName, $node);
    }
    else
    {
    // add single node.
    $value = htmlentities($value);
    $xml->addChild($key,$value);
    }
     
    }
    // pass back as string. or simple xml object if you want!
    return $xml->asXML();
    }
    }
	
	
class assoc_array2xml {
var $text;
var $arrays, $keys, $node_flag, $depth, $xml_parser;
/*Converts an array to an xml string*/
function array2xml($array) {
//global $text;
$this->text="<?xml version='1.0' encoding='utf-8'?><worldvpn>";
$this->text.= $this->array_transform($array);
$this->text .="</worldvpn>";
return $this->text;
}

function array_transform($array){
//global $array_text;
foreach($array as $key => $value){
if(!is_array($value)){
 $this->text .= "<$key>$value</$key>";
 } else {
 $this->text.="<$key>";
 $this->array_transform($value);
 $this->text.="</$key>";
 }
}
return $array_text;

}
/*Transform an XML string to associative array "XML Parser Functions"*/
function xml2array($xml){
$this->depth=-1;
$this->xml_parser = xml_parser_create();
xml_set_object($this->xml_parser, $this);
xml_parser_set_option ($this->xml_parser,XML_OPTION_CASE_FOLDING,0);//Don't put tags uppercase
xml_set_element_handler($this->xml_parser, "startElement", "endElement");
xml_set_character_data_handler($this->xml_parser,"characterData");
xml_parse($this->xml_parser,$xml,true);
xml_parser_free($this->xml_parser);
return $this->arrays[0];

}
function startElement($parser, $name, $attrs)
 {
   $this->keys[]=$name; //We add a key
   $this->node_flag=1;
   $this->depth++;
 }
function characterData($parser,$data)
 {
   $key=end($this->keys);
   $this->arrays[$this->depth][$key]=$data;
   $this->node_flag=0; //So that we don't add as an array, but as an element
 }
function endElement($parser, $name)
 {
   $key=array_pop($this->keys);
   //If $node_flag==1 we add as an array, if not, as an element
   if($this->node_flag==1){
     $this->arrays[$this->depth][$key]=$this->arrays[$this->depth+1];
     unset($this->arrays[$this->depth+1]);
   }
   $this->node_flag=1;
   $this->depth--;
 }

}//End of the class

?>