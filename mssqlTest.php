<?php
$serverName = "(local)"; //���ݿ��������ַ
$uid = "sa"; //���ݿ��û���
$pwd = "078515"; //���ݿ�����
$connectionInfo = array("UID"=>$uid, "PWD"=>$pwd, "Database"=>"IMS");
echo $serverName."<br>";
var_dump($connectionInfo);
//return;

$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn == false)
{
    echo "����ʧ�ܣ�";
    die( print_r( sqlsrv_errors(), true));
}
$query = sqlsrv_query($conn, "SELECT TOP 10 productID,productName FROM IMS.dbo.tbProduct");
//$row = sqlsrv_fetch_array($query);
//var_dump($query);
///*
while($row = sqlsrv_fetch_array($query)){
    echo $row['productID']."-----".$row['productName']."<br/>";
}
//*/
?>