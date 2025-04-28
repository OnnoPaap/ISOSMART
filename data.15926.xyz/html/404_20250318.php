<?php

/********************************************************
ISO 15926 reverse proxy by O.Paap. 2016-01-19
Contact: onno.paap@gmail.com

This proxy will hold 31 endpoints (to start with)
The main endpoint is https://data.15926.xyz/rdl
Other endpoints can be https://data.15926.xyz/din https://data.15926.xyz/astm https://data.15926.xyz/dm etc

3 types of addressing give 3 kinds of responses

1. The empty address
e.g. https://data.15926.org/rdl
this must merely open the browser, but equip it with the named graph
https://data.15926.xyz/15926browser?default-graph-uri=http://data.15926.org/rdl



2. the class ID
e.g. https://data.15926.org/rdl/RDS267929 (id for TRANSMITTER)
this must open the browser opened onto the asked class; equip it with the named graph
https://data.15926.xyz/15926browser?id=https://data.15926.org/rdl/RDS267929


NOTE none of these results are allowed to be a redirect. So use a reverse proxy.

NOTE on QA testing: the dataqa.15926.org is the QA place for testing (Godaddy servers have strange quircks so test it)
The code runs on dataqa and on data.15926.org both, without any need to change it.

***************************************************************/

include_once 'endpoints.php';
$log="";

//echo $_SERVER['REQUEST_URI'];

$shortNameSpace=$_SERVER['REQUEST_URI'];
$querystring = '';

if (substr($shortNameSpace,-1)=="/") {
	//no trailing slash /
	$shortNameSpace=substr($shortNameSpace, 0, -1);
}

if (strpos($shortNameSpace,"?")!==false) {
	//echo 'found a ? mark'.'<br>';
	$i = strpos($shortNameSpace,'?');
	$querystring = substr($shortNameSpace,$i+1);
	$shortNameSpace=substr($shortNameSpace,0,$i);
} elseif (strpos($shortNameSpace,"/iso/15926/-4/reference-data-item")!==false) {
	//echo 'ISO smart call'.'<br>';
	$i=33;
	$querystring = substr($shortNameSpace,$i+1);
	$shortNameSpace=substr($shortNameSpace,1,$i-1);
} elseif (strpos($shortNameSpace,"/")!==false) {
	//echo 'found a linkeddata'.'<br>';
	$i = strrpos($shortNameSpace,'/');
	$querystring = substr($shortNameSpace,$i+1);
	$shortNameSpace=substr($shortNameSpace,0,$i);
}
//remove any slash, also trailing
//$shortNameSpace=str_replace('/','',$shortNameSpace);

//echo '$querystring='.$querystring.'<br>';
//echo 'shortNameSpace='.$shortNameSpace.'<br>';

$currentHost = get_current_host();

//echo 'currentHost='.$currentHost.'<br>';

$found=false;
$list="";
foreach ($endpoints as $endpoint) {

	$address=$currentHost.'/'.$endpoint[0];
	$descr=$endpoint[1];
	if ($endpoint[0]==$shortNameSpace) {
		$found=true;
		break;
	}

	$list.=$graphPrefix.$endpoint[0] . " - " . $endpoint[1] . "<br>";
}

//echo 'found='.$found.' - '.$shortNameSpace.'<br>';

if (!$found) {
	header('HTTP/1.1 404 Not Found');
	echo '$querystring='.$querystring.'<br>';
	echo 'shortNameSpace='.$shortNameSpace.'<br>';
	echo 'currentHost='.$currentHost.'<br>'; 
	echo 'sparqlUri='.$sparqlUri.'<br>'; 
	die("<hr>404 - Not Found<br>$targetPath</br>on this endpoint list<br>$list<hr>");
}

//logstr ('$querystring='.$querystring.'<br>');
//logstr ( 'shortNameSpace='.$shortNameSpace.'<br>');
//logstr ( 'currentHost='.$currentHost.'<br>'); 
//logstr ( 'sparqlUri='.$sparqlUri.'<br>'); 

//echo "<br>shortNameSpace: " . $shortNameSpace ;
//echo "<br>querystring: " . $querystring ;

//make the decision which way to go, browser, sparql dialog or sparql query
if (strpos($querystring,"query=")!==false) {
	//it is a sparql query
	if ($shortNameSpace=='sparql') {
		//it already has default-graph-uri, as the 15926browser does it; pass it on
		$querystring=str_replace('default-graph-uri=http%3A%2F%2Fdata.15926.org%2Fall&', '', $querystring);
		logstr ('$querystring='.$querystring.'<br>');
		$s = $sparqlUri . '?' . $querystring;
	} elseif (strpos($querystring,"default-graph-uri=")!==false || $shortNameSpace=='sparql') {
		//it already has default-graph-uri, as the 15926browser does it; pass it on
		$querystring=str_replace('default-graph-uri=http%3A%2F%2Fdata.15926.org%2Fall&', '', $querystring);
		logstr ('$querystring='.$querystring.'<br>');
		$s = $sparqlUri . '?' . $querystring;
	} else {
		if ($shortNameSpace == 'all') {
			// no named graph restrictions
			$s = $sparqlUri . '?' . substr($querystring, strpos($querystring,'query='));
		} elseif ($shortNameSpace == 'rdl') {
			//if the query is rdl then include dm, lci and prov
			$s=$sparqlUri.
				'?default-graph-uri=http://data.15926.org/rdl'.
				'&default-graph-uri=http://data.15926.org/dm'.
				'&default-graph-uri=http://data.15926.org/prov'.
				'&default-graph-uri=http://data.15926.org/lci'.'&'.$querystring; 
		} else {
			$s=$sparqlUri. 
				'?default-graph-uri='.str_replace('https://','http://',str_replace('.xyz','.org', $currentHost)) . '/' . $shortNameSpace .
				'&default-graph-uri=http://data.15926.org/dm&'.
				'default-graph-uri=http://data.15926.org/prov'.
				'&default-graph-uri=http://data.15926.org/lci'.'&'.$querystring; 
		}
	}	
} elseif ($shortNameSpace=="iso/15926/-4/reference-data-item") {
	//echo "going for id: ".$querystring;
	//It is an id. Open the human browser with the id opened
	$s=$currentHost . '/15926browser?uri='.$querystring;
	//echo "<br>".$s;
} elseif ($shortNameSpace=='sparql') {
	$s=$sparqlUri; /*from endpoints.php file */
} elseif (substr($querystring,0,1)=="R") {
	//echo "going for id: ".$querystring;
	//It is an id. Open the human browser with the id opened
	$s=$currentHost . '/15926browser?id=http://data.15926.org/'.$shortNameSpace.'/'.$querystring;
} else {
	//It is not a sparql query nor id. Open the human browser
	$s=$currentHost . '/15926browser?'.$querystring;
}

logstr($s);

$output=file_get_contents($s);
$output = trim($output);
//echo 's='.$s.'<br>';
echo $output;


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

