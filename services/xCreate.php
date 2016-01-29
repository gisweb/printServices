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

//ACQUISIZIONE DATI DI REQUEST
$filename = (array_key_exists("filename", $_REQUEST))?($_REQUEST["filename"]):("");
$modello=(array_key_exists("model", $_REQUEST))?($_REQUEST["model"]):("");
$data=(array_key_exists("data", $_REQUEST))?($_REQUEST["data"]):(Array());

$app=(array_key_exists("app", $_REQUEST))?($_REQUEST["app"]):("");
$mode=(array_key_exists("mode", $_REQUEST))?($_REQUEST["mode"]):("");
$group=(array_key_exists("group", $_REQUEST))?($_REQUEST["group"]):("");
$project=(array_key_exists("project", $_REQUEST))?($_REQUEST["project"]):("");
$id=($_REQUEST["id"])?($_REQUEST["id"]):(rand(1,100000));

//RIMOZIONE slashes del POST
if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
    $_REQUEST = array_map( 'stripslashes', $_REQUEST);
}
$request=$_REQUEST;
//DECODIFICA DELLA STRINGA JSON CON DATI
$_REQUEST['data']=json_decode($_REQUEST["data"],true);


//DEBUG DEI DATI DI REQUEST
debug($debugName,$_REQUEST,'a+'); 

//MODELLO DI STAMPA
if (!$modello){
    $msg="Nessun Parametro \"model\" passato al servizio";
    $result=Array("success"=>0,"message"=>$msg);
    header('Content-Type: application/json; charset=utf-8');
    print json_encode($result);
    return;
}

elseif(filter_var($modello, FILTER_VALIDATE_URL)){
    $f=fopen($modello,'rb');
    $doc= stream_get_contents($f);
    fclose($f);
	if(!$doc){
		$msg="Il modello $name non è stato recuperato correttamente dalla url $modello";
        debug($debugName,$msg,'a+');
        $result=Array("success"=>0,"message"=>$msg);
        header('Content-Type: application/json; charset=utf-8');
        print json_encode($result);
        return;
	}
    $name=pathinfo($modello,PATHINFO_BASENAME);
    $modelName="/tmp/$name";
    $f=fopen($modelName,'w');
    if (fwrite($f,$doc)) debug($debugName,"File $name scritto correttamente",'a+'); 
    else{
        $msg="Il modello $name non è stato scritto correttamente sul server";
        debug($debugName,$msg,'a+');
        $result=Array("success"=>0,"message"=>$msg);
        header('Content-Type: application/json; charset=utf-8');
        print json_encode($result);
        return;
    }
    fclose($f);
}
else{
    $modelDir=($project)?(MODEL_DIR.$project.DIRECTORY_SEPARATOR):(MODEL_DIR);
    $modelName = ($group)?($modelDir.$app.DIRECTORY_SEPARATOR.$group.DIRECTORY_SEPARATOR.$modello):($modelDir.$app.DIRECTORY_SEPARATOR.$modello);
}

$filename=($filename)?($filename):($modello);

if(!file_exists($modelName)){
    $msg="Im modello $modelName non è stato trovato!";
    debug($debugName,$msg,'a+');
    $result=Array("success"=>0,"message"=>$msg);
    header('Content-Type: application/json; charset=utf-8');
    print json_encode($result);
    return;
}

debug($debugName,"Mode : $mode\nLoading Template $modelName",'a+');

$TBS->LoadTemplate($modelName);
$TBS->SetOption('noerr',true);
debug($debugName,"Template Loaded",'a+');
$data=$_REQUEST["data"];

if (file_exists(INC_DIR.$project.".php")){
    include INC_DIR.$project.".php";
}
switch($app){
    case "ordinanze":
        $keys=array_keys($data);
        $excludedItems=Array('numero_registro_cronologico','data_pubblicazione');
        foreach($excludedItems as $k ){
                if (in_array($k,$keys) && !$data[$k]){
                        unset($data[$k]);
                }
        }
        debug($debugName,$data,'a+');
        break;
    default:
        $dbgName="get.debug";
}

if($data) {
   $data["oggi"]=date('d/m/Y');
   $TBS->LoadTemplate($modelName);
   $TBS->SetOption('noerr',true);
   array_walk_recursive($data, 'decode');
   mergeFields($TBS,$data);
   $HeaderAndFooter = $TBS->PlugIn(OPENTBS_GET_HEADERS_FOOTERS);
   for($i=0;$i<count($HeaderAndFooter);$i++){
       $f=$HeaderAndFooter[$i];
       $TBS->LoadTemplate($f);
       mergeFields($TBS,$data);
   }
}	
$docDir=($project)?(DOC_DIR."$project".DIRECTORY_SEPARATOR):(DOC_DIR);
switch($mode){
    case "show":
        $docFile = $docDir."$app/$id/$filename";
        if (!file_exists($docDir."$app")) mkdir($docDir."$app");
        if (!file_exists($docDir."$app/$id")) mkdir($docDir."$app/$id");
        $TBS->Show(OPENTBS_FILE,$docFile);		 
        $ff=fopen($docFile,'r');
        $doc=fread($ff,filesize($docFile));
        fclose($ff);
        header('Content-Type: application/application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        print $doc;
        return;
        break;
    default :
        $docFile = $docDir."$app/$id/$filename";
        if (!file_exists($docDir."$app")) mkdir($docDir."$app");
        if (!file_exists($docDir."$app/$id")) {
            if (!mkdir($docDir."$app/$id")){
                $msg="Impossibile creare la directory ".$docDir."$app/$id";
                $result=Array("success"=>0,"message"=>$msg);
                debug($debugName,$msg,'a+');
                header('Content-Type: application/json; charset=utf-8');
                print json_encode($result);
                return;
            }
        }
        $TBS->Show(OPENTBS_FILE,$docFile);
        if($TBS){
            if($mode=="download"){
                $result=Array("success"=>1,'filename'=>$docFile);
            }
            else{
                $f=fopen($docFile,'r');
				$fsize=filesize($docFile);
                $text=fread($f,$fsize);
                fclose($f);
                $result=Array("success"=>1,'filename'=>$docFile,"file"=>  base64_encode($text),"size"=>$fsize);
            }
            $msg="Il file $filename è stato creato correttamente";
            debug($debugName,$msg,'a+');
            header('Content-Type: application/json; charset=utf-8');
            print json_encode($result);
            return;
        }
        else{
            $msg="Sono stati riscontrati degli errori nella generazione del documento $filename";
            debug($debugName,$msg,'a+');
            $result=Array("success"=>0,"message"=>$msg);
            header('Content-Type: application/json; charset=utf-8');
            print json_encode($result);
            return;
        }
        break;
}
	
?>
