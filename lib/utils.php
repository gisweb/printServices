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
	$f=fopen($file,$mode);
	ob_start();
	print_r($data);
	$result=ob_get_contents();
	ob_end_clean();
	fwrite($f,$result."\n");
	fclose($f);
}
?>