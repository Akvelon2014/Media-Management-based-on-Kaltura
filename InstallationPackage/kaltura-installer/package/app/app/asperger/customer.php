<link type="text/css" href="css/onprem.css" rel="Stylesheet" />
<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'conn.inc');
if (isset($_GET["id"])){
    $id = $_GET["id"];
}else{
    'You need to pass a customer ID from customers.id';
}
$db=new SQLite3($dbfile,SQLITE3_OPEN_READWRITE) or die("Unable to connect to database $dbfile");
$result=$db->query('select * from customers where id='.$id);
var_dump ($result->fetchArray(SQLITE3_ASSOC));
?>
