<?php
require_once('./nusoap/lib/nusoap.php');
$client = new nusoap_client("http://211.68.70.10:9003/databank/services/QueryData?wsdl", 'wsdl');
$err = $client->getError();
if ($err) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
$client->soap_defencoding = 'UTF-8';


$client->setCredentials('pt.jcsj','11111','certificate');
$result = $client->call('queryDataByID', array('auditid'=>830037));
//$result = $client->call('getMaxAuditID', array());
// Check for a fault
if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
} else {
	// Check for errors
	$err = $client->getError();
	if ($err) {
		// Display the error
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		// Display the result
		echo '<h2>Result</h2><pre>';
		print_r($result);
		echo '</pre>';
	}
}
//echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

?>