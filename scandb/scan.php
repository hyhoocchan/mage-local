<?php
$database = 'partdeal';
$databaseUserName = 'root';
$databaseUserPassword = 'hocchan123456';

$connection = mysql_connect("localhost", $databaseUserName, $databaseUserPassword);

mysql_select_db($database, $connection);
if (!$connection) die ("Cannot connect Database");

//----------------
$dataforScan = strtolower(trim($_POST['scandata']));
$scanType = $_POST['scantype'];

//Scan process
$findResult = array();
$tableresult = mysql_query("show tables",$connection); 
while ($rowTables = mysql_fetch_array($tableresult)){
	$tables = $rowTables[0];
	//Cloum in tables
	$cloumnresult = mysql_query("describe {$tables}");
	//Find Primary Key;
	$primaryKey = null;
	while ($rowResultGetPRI = mysql_fetch_array($cloumnresult)) {
		if ($rowResultGetPRI[3] == 'PRI') $primaryKey[] = $rowResultGetPRI[0];
	}
	
	$cloumnresult = mysql_query("describe {$tables}");
	while ($rowCloumn = mysql_fetch_array($cloumnresult)) {
		$cloumnName = $rowCloumn[0];
		if (preg_match("/^varchar/",$rowCloumn[1])||
		    preg_match("/text$/",$rowCloumn[1])||
		    preg_match("/date/",$rowCloumn[1])		    
		){
			if ($scanType == 'on') 
				$sqlStringData = "select * from {$tables} where LOWER({$tables}.{$cloumnName}) like '%{$dataforScan}%'";
			else	
				$sqlStringData = "select * from {$tables} where LOWER({$tables}.{$cloumnName}) = '{$dataforScan}'";	
		}
		if (preg_match("/^int.*/",$rowCloumn[1])){
				$sqlStringData = "select * from {$tables} where {$tables}.{$cloumnName} = {$dataforScan}";
		}
		$ketqua = mysql_query($sqlStringData,$connection);
		
		if ($ketqua!=null && mysql_num_rows($ketqua)>0) {
			while ($childRow = mysql_fetch_assoc($ketqua)) {
				$str = "";
				foreach ($primaryKey as $item) 
				{
					$sysbol = end($primaryKey) == $item ? "":" - ";
					$str .= "<b>{$item} : {$childRow[$item]}{$sysbol}</b>"; 	
				}
				$findResult["table:".strtoupper($tables)][$cloumnName][] = $str;
			}
		}
	}
}
mysql_close($connection);
if (count($findResult)<= 0 ) die('Khong tim thay du lieu');
echo "<pre>";
	print_r($findResult);
echo "</pre>";
die();
?>