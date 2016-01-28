<?php
function mergeFields($T,$data){
    foreach($data as $key=>$value){
        if(is_array($value)){
                $T->MergeBlock($key, $value);
        }
        else{
                $T->MergeField($key, $value);
        }
    }
}

require_once "../config.php";
$debugName=DBG_DIR."debug-create.txt";

require_once LIB_DIR."tbs_class.php";
require_once LIB_DIR."tbs_plugin_opentbs.php";
$TBS = new clsTinyButStrong; // new instance of TBS
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin

//RIMOZIONE slashes del POST
if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
    $_REQUEST = array_map( 'stripslashes', $_REQUEST);
}

//DEBUG DEI DATI DI REQUEST
debug($debugName,$_REQUEST,'w'); 

//VERIFICA MODELLO DI STAMPA
if (empty($_REQUEST["model"])){
    $msg="Nessun Parametro \"model\" passato al servizio";
    $result=Array("success"=>0,"message"=>$msg);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    die();
}

$binary_contents = base64_decode($_REQUEST["model"],true);
$data = json_decode($_REQUEST["data"],true);
$data["oggi"]=date('d/m/Y');

$handle = tmpfile();
fwrite($handle, $binary_contents);
$TBS->LoadTemplate($handle);

//$TBS->SetOption('noerr',true);
array_walk_recursive($data, 'decode');

//TODO CONTROLLO DEGLI ERRORI
mergeFields($TBS,$data);
 
$TBS->Show(OPENTBS_STRING);
$content = $TBS->Source;
$result=Array("success"=>1,"message"=>"","content"=>base64_encode($content));

if(isset($_REQUEST["pdf"])){
    require_once "convert2pdf.php";
    $pdfContent = convert2Pdf($content);
    if($pdfContent["success"] == 1){
        $result["pdfContent"] = base64_encode($pdfContent["content"]);
    }
    else{
        $result["success"] = 0;
        $result["message"] = $pdfContent["message"];
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
die();