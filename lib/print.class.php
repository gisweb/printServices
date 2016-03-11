<?php

class utilsPrint {
    
    static function mergeFields($T,$data){
        foreach($data as $key=>$value){
            if(is_array($value)){
                    $T->MergeBlock($key, $value);
            }
            else{
                    $T->MergeField($key, $value);
            }
        }
    }
    
    static function createDoc($model,$data){
        if (!file_exists($model)){
            return Array("success"=>-1,"message"=>"Il File $model non è stato trovato");
        }
        $TBS = new clsTinyButStrong; // new instance of TBS
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
        $TBS->LoadTemplate($model);
        $TBS->SetOption('noerr',true);
        array_walk_recursive($data, 'decode');
        self::mergeFields($TBS,$data);
        $TBS->PlugIn(OPENTBS_SELECT_HEADER);
        self::mergeFields($TBS,$data);
        $TBS->PlugIn(OPENTBS_SELECT_FOOTER);
        self::mergeFields($TBS,$data);
        $TBS->Show(OPENTBS_STRING);
        return Array("success"=>1,"message"=>"","file"=>$TBS->Source);
    }
    static function rand_str($length = 8, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
        $chars_length = (strlen($chars) - 1);
        $string = $chars{rand(0, $chars_length)};
        for ($i = 1; $i < $length; $i = strlen($string)){
            $r = $chars{rand(0, $chars_length)};
            if ($r != $string{$i - 1}) $string .=  $r;
        }
        return $string;
    }
    static function writeFile($text,$ext,$name="",$dir="/tmp/"){
        $nome=($name)?($name):(self::rand_str());
        $nomefile=($ext)?(sprintf("%s.%s",$nome,$ext)):($nome);
        $textTmp=base64_decode($text,true);
        $text=($textTmp)?($textTmp):($text);
        $f=fopen($dir.$nomefile,'w');
        $text=($base64)?(base64_decode($text,true)):($text);
        if(fwrite($f, $text)){
            fclose($f);
            return Array("success"=>1,"message"=>"","file"=>$dir.$nomefile);
        }
        else{
            return Array("success"=>0,"message"=>"Impossibile scrivere il file","file"=>"");
        }
    }
    static function convertToPdf($file){
        if (!file_exists($file)){
            return Array("success"=>-1,"message"=>"Il file $fName non esiste","file"=>"");
        }
        $fileInfo= pathinfo($file);
        $cmd=CMD_DIR."soffice  --headless --invisible --nologo --convert-to pdf ".escapeshellarg($file)." --outdir ".TMP_DIR;
        echo $cmd;
        $res=exec($cmd);
	$msg1="Overwriting:";// $dirname/$filename";
	$msg2="convert";// $dirname/$filename";
	if (stripos($res,$msg1)===FALSE and stripos($res,$msg2)===FALSE){
            return Array("success"=>-1,"message"=>$res,"file"=>"");
        }
        $fName=TMP_DIR.$fileInfo["filename"].".pdf";
        if (file_exists($fName)){
            $f=fopen($fName,'r');
            $text=fread($f,filesize($fName));
            fclose($f);
            unlink($fName);
            return Array("success"=>1,"message"=>"","file"=>$text);
        }
        else{
            return Array("success"=>-1,"message"=>"Errore generico nella conversione del file","file"=>"");
        }
    }
    static function decodeData($data){
        if ( in_array( strtolower( ini_get( 'magic_quotes_gpc' ) ), array( '1', 'on' ) )){
            $data = array_map( 'stripslashes', $data);
        }

        //DECODIFICA DELLA STRINGA JSON CON DATI
        $result=json_decode($data,true);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return Array("success"=>1,"message"=>"","result"=>$result);
                break;
            case JSON_ERROR_DEPTH:
                return Array("success"=>-1,"message"=>'Maximum stack depth exceeded',"result"=>$result);
            break;
            case JSON_ERROR_STATE_MISMATCH:
                return Array("success"=>-1,"message"=>'Underflow or the modes mismatch',"result"=>$result);
            break;
            case JSON_ERROR_CTRL_CHAR:
                return Array("success"=>-1,"message"=>'Unexpected control character found',"result"=>$result);
            break;
            case JSON_ERROR_SYNTAX:
                return Array("success"=>-1,"message"=>'Syntax error, malformed JSON',"result"=>$result);
            break;
            case JSON_ERROR_UTF8:
                return Array("success"=>-1,"message"=>'Malformed UTF-8 characters, possibly incorrectly encoded',"result"=>$result);
            break;
            default:
                return Array("success"=>-1,"message"=>'Unknown error',"result"=>$result);
            break;
        }


    }
}
