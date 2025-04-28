<?php

/********************************************************
15926 reverse proxy by O.Paap. 20250321
Contact: onno.paap@protonmail.com

types of addressing give 2 kinds of responses

1. The empty address
e.g. https://data.15926.xyz/
this must merely open the 15926browser

2. the class ID
e.g. https://data.15926.xyz/iso/15926/-4/reference-data-item/ACTIVATED_SLUDGE_PUMP
this must open the browser opened onto the asked class; equip it with the named graph

3. A sparql query
e.g. https://data.15926.xyz/sparql
this opens the sparql query window
e.g. https://data.15926.xyz/sparql?query=select count(*) {?s ?p ?o}&format=json
this will respond with a sparql return 

note that option 3 is used by the 15926browser to retrieve data.

***************************************************************/

//the endpoint of the semantic database
$sparqlUri='http://192.236.179.169:8890/sparql'; // hostwinds
//$sparqlUri='http://190.92.134.58:8890/sparql'; // a2 hosting

$log="";

$queryString = trim($_SERVER['REQUEST_URI'], '/');
//echo $queryString.'<hr>';
$currentHost = get_current_host();

$s = "";
if (strpos($queryString, 'sparql')!==false) {
	// the request is to be passes on to the sparql endpoint, 
	// which will detect a query or just opening the sparql input page
	$s = $sparqlUri.substr($queryString,6);
} else {
	// the request is to open the 15926browser, which will detect of it 
	// is a linkeddata address or just opening the engineering search page
	$s = $currentHost . '/15926browser';
}

logstr($s);

set_error_handler("customErrorHandler");

try {
	$output = file_get_contents($s);
	$output = sanetize($output);
	echo $output;
} catch (Exception $e) {
    //echo "Error: " . $e->getMessage();
	//on error, we don't want to show the actual endpoint address. So, just say fail.
	echo "Error: request failed.";
}

restore_error_handler();

function sanetize($output) {
	// nothing found to do yet
	// this used to be necessarry: the response had bugs at some point.
	return $output;
}

function customErrorHandler($errno, $errstr) {
    throw new Exception($errstr);
}

function get_current_host() {
    $url = array();
    // set protocol
    $url['protocol'] = 'https://';
    if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] == 1)) {
        $url['protocol'] = 'https://';
    } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        $url['protocol'] = 'https://';
    }
    // set host
    $url['host'] = $_SERVER['HTTP_HOST'];
    return join('', $url);
}

function logstr ($s) {
	$log=date('Y-m-d\TH:i:s\Z') . ' - ' . $s . PHP_EOL;
	file_put_contents('./app.log', $log, FILE_APPEND);
}
	

?>

