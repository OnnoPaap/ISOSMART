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
$log=new Log("./app.log");


$shortNameSpace=$_SERVER['REQUEST_URI'];
$querystring = '';

//echo strpos($shortNameSpace,'?').'<br>';

if (strpos($shortNameSpace,"?")!==false) {
	//echo 'found a ? mark'.'<br>';
	$i = strpos($shortNameSpace,'?');
	$querystring = substr($shortNameSpace,$i+1);
	$shortNameSpace=substr($shortNameSpace,0,$i);
}
if (strpos($shortNameSpace,"/R")!==false) {
	//echo 'found a R mark'.'<br>';
	$i = strpos($shortNameSpace,'/R');
	$querystring = substr($shortNameSpace,$i+1);
	$shortNameSpace=substr($shortNameSpace,0,$i);
}
//remove any slash, also trailing
$shortNameSpace=str_replace('/','',$shortNameSpace);

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

//echo 'found='.$found.'<br>';

if (!$found) {
	header('HTTP/1.1 404 Not Found');
	die("<hr>404 - Not Found<br>$targetPath</br>on this endpoint list<br>$list<hr>");
}

//make the decision which way to go, browser, sparql dialog or sparql query
if (strpos($querystring,"query=")!==false) {
	//it is a sparql query
	$s=$sparqlUri; /*from endpoints.php file */
	$s.='?'.$querystring;
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

//echo 's='.$s.'<br>';

$log->log($s);
$output=file_get_contents($s);
$output = trim($output);
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

/**
 * Class Log
 * A really simple logging class that writes flat data to a file.
 * @author Dennis Thompson
 * @license MIT
 * @version 1.0
 * @copyright AtomicPages LLC 2014
 */
class Log {

	private $handle, $dateFormat;

	public function __construct($file, $mode = "a") {
		$this->handle = fopen($file, $mode);
		$this->dateFormat = "d/M/Y H:i:s";
	}

	public function dateFormat($format) {
		$this->dateFormat = $format;
	}

	public function getDateFormat() {
		return $this->dateFormat;
	}

	/**
	 * Writes info to the log
	 * @param mixed, string or an array to write to log
	 * @access public
	 */
	public function log($entries) {
		if(is_string($entries)) {
			fwrite($this->handle, "[" . date($this->dateFormat) . "] " . $entries . "\n");
		} else {
			foreach($entries as $value) {
				fwrite($this->handle, "[" . date($this->dateFormat) . "] " . $value . "\n");
			}
		}
	}

}

?>

