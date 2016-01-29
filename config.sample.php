
<?php

function extractDate($d){
	$data=preg_replace('|([/\.])|','-',$d);
	$data=date_parse($data);
	return ($data['year'])?($data):(Array());
}
function decode(&$item, &$key){
	$item=(mb_detect_encoding($item)=='UTF-8')?(utf8_decode($item)):($item);
	$item=str_replace('<br/>',"",$item);
	return $item;
}

function debug($file,$data,$mode='w+'){
	if (filesize($file)>10000000) $mode="w";
	$f=fopen($file,$mode);
	ob_start();
	print date('d-m-Y H:i:s')."\n";
	print_r($data);
	$result=ob_get_contents();
	ob_end_clean();
	fwrite($f,$result."\n");
	fclose($f);
}
define('BASE_PATH',realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('MODEL',BASE_PATH."modelli".DIRECTORY_SEPARATOR);
define('LIB_DIR',BASE_PATH."lib".DIRECTORY_SEPARATOR);
define('DOC_DIR',BASE_PATH."documenti".DIRECTORY_SEPARATOR);
define('DBG_DIR',BASE_PATH."debug".DIRECTORY_SEPARATOR);
?>