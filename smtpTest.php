<?php
$smtp_conn ="";

if(empty($smtp_conn)) {
	echo "start  conn is empty <br>";
}
$host = "smtp.163.com";
$port = 25;
$tval=30;
/* TODO: Add code here */
$smtp_conn = @fsockopen($host,    // the host of the server
		$port,    // the port to use
		$errno,   // error number if any
		$errstr,  // error message if any
		$tval);   // give up after ? secs
// verify we connected properly
if(empty($smtp_conn)) {
	$error = array("error" => "Failed to connect to server",
			"errno" => $errno,
			"errstr" => $errstr);
	echo "SMTP -> ERROR: " . $this->error["error"] . ": $errstr ($errno)" . $this->CRLF . '<br />';
}
else
{
	echo "connect success!";
}
?>