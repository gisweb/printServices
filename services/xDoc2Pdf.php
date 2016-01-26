<?php
require_once "../config.php";
require_once "convert2pdf.php";
$debugName=DBG_DIR."debug-convert.txt";

//DEBUG DEI DATI DI REQUEST
debug($debugName,$_REQUEST,'w'); 
$binary_contents = base64_decode($_REQUEST["content"],true);
$result = getPdfContent($content);

if($result["success"] == 1){
    $result["content"] = base64_encode($result["content"]);
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
die();
