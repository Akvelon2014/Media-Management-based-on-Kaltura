<link type="text/css" href="css/onprem.css" rel="Stylesheet" />
<?php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'conn.inc');
$script_name=basename(__FILE__);
if (isset($_GET["orderby"])){
    $order_by = $_GET["orderby"];
}else{
    $order_by = 'name';
}
echo '<html>
<head>
<META HTTP-EQUIV="refresh" CONTENT="60">
</head>
<body>
<title>OnPrem Clients ordered by '.$order_by.'</title>
<form action="'.$script_name.'" method="GET">';

$db=new SQLite3($dbfile,SQLITE3_OPEN_READWRITE) or die("Unable to connect to database $dbfile");
$result=$db->query('select * from customers order by '.$order_by);
echo '<table class="onprem">
<h3 class="onprem">OnPrem Clients (by '.$order_by.')<h3>
<tr>
<th><a href='.$script_name.'?orderby=id>Id</a></th>
<th><a href='.$script_name.'?orderby=name>Client name</a></th>
<th><a href='.$script_name.'?orderby=customer_tech_contact>Technical contact</a></th>
<th><a href='.$script_name.'?orderby=pm>PM</a></th>
<th><a href='.$script_name.'?orderby=am>AM</a></th>
<th><a href='.$script_name.'?orderby=ps_tech_contact>PS technical contact</a></th>
<th><a href='.$script_name.'?orderby=on_prem_version>Version</a></th>
<th>Notes</th>
</tr>';
$index=0;
while($res = $result->fetchArray(SQLITE3_ASSOC)){
    if ($index%2){
	$color='green';
    }else{
	$color='yellow';
    }
    echo '<tr class="'.$color.'">
    <td> <a href=customer.php?id='.$res['id'].'>'. $res['id'].'<a></td>
    <td>' . $res['name'].'</td>
    <td>' . $res['customer_tech_contact'].'</td>
    <td>' . $res['pm'].'</td>'. '</td>
    <td>' . $res['am'].'</td>
    <td>' . $res['ps_tech_contact'].'</td>
    <td>' . $res['on_prem_version'].'</td>
    <td>' . $res['notes'].'</td>
    </tr>';
    $index++;
}
echo '</table></form></body></html>';
?>
