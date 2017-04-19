<?php
require_once "../config.php";
$debugName=DBG_DIR."debug-convert.txt";
$debugRequest=DBG_DIR."debug-convert-request.txt";
$docURL=$_REQUEST["docurl"];
$filename=($_REQUEST["filename"])?($_REQUEST["filename"]):(array_pop(explode('/',$_REQUEST["docurl"])));
$file=$_REQUEST["file"];
debug($debugRequest,$_REQUEST);


//Nome del file convertito
$info=pathinfo($filename);
$filename=$info["basename"];


if($file){
    //RECUPERO FILE DA POST
    $doc = base64_decode($file,true);
	if(!$doc){
		$msg="Il file non è stato codificato in base64";
		debug($debugName,$msg);
		header('Content-Type: application/json; charset=utf-8');
		$result=Array("success"=>0,"message"=>$msg);
        echo json_encode($result);
        return;
	}
}
else{
    //LETTURA DEL FILE DA URL
    if (false === @file_get_contents($docURL,0,null,0,1)) {
		$msg="Il file $docURL non è stato trovato";
		debug($debugName,$msg);
                $result=Array("success"=>-1,"message"=>$msg);
		header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result);
        return;
    }
    $f=fopen($docURL,'rb');
    $doc= stream_get_contents($f);
    fclose($f); 
}



//SCRITTURA DEL FILE IN LOCALE
$docName=DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename;


$f=fopen($docName,'w');
if (fwrite($f,$doc)) {
	$msg="File $docName scritto correttamente.";
	debug($debugName,$msg);
}
fclose($f);

//CONTROLLO CHE FILE SIA STATO SCRITTO
if (file_exists($docName) && filesize($docName)){
  
        $cmd=sprintf("export HOME=/tmp &&  %ssoffice --headless --invisible --nologo --convert-to pdf:writer_pdf_Export  %s --outdir /tmp",LIBREOFFICE,$docName);
	debug($debugName,$cmd);
        $res=shell_exec($cmd);      
//	$cmd=sprintf("HOME=/tmp/pdfout %ssoffice \"-env:UserInstallation=file:///tmp/pdfout\" --headless --invisible --nologo --convert-to pdf %s --outdir /tmp",LIBREOFFICE,escapeshellarg($docName));
//	$res=exec($cmd);
	$msg1="Overwriting:";// $dirname/$filename";
	$msg2="convert";// $dirname/$filename";
	if (stripos($res,$msg1)===FALSE and stripos($res,$msg2)===FALSE){
			debug($debugName,$res);
			header('Content-Type: application/json; charset=utf-8');
            echo json_encode(Array("success"=>0,"message"=>$res));
            return;
        }
	else{
		$pdfName=str_replace('.odt','',str_replace('.docx','',$docName)).".pdf";
		debug($debugName,"File Convertito correttamente");
		$f = fopen($pdfName,'r');
		$text=fread($f,filesize($pdfName));
		fclose($f);
		if ($_REQUEST['mode']=="show"){
			unlink($docName);
			unlink($pdfName);
			header('Content-type: application/pdf');
			print $text;
			return;
			//print json_encode(Array("success"=>1,"filename"=>$text));
		}
		else{
			
			$app=$_REQUEST["app"];
			$id=$_REQUEST["id"];
			$date=$_REQUEST["data"];
			
			$d=extractDate($date);
			$appDir=DOC_DIR.$app;
			if (!file_exists($appDir)) mkdir($appDir);
			if($d){
				$yearDir=$appDir.DIRECTORY_SEPARATOR.$d["year"];
				if (!file_exists($yearDir)) mkdir($yearDir);
				$appDir=$yearDir;
			}
			$folderDir=$appDir.DIRECTORY_SEPARATOR.$id;
			if (!file_exists($folderDir)) mkdir($folderDir);
			$location=$folderDir.DIRECTORY_SEPARATOR.$filename;
			$f=fopen($location,'w');
			fwrite($f,$text);
			fclose($f);
			//unlink($docurl);
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(Array("success"=>1,"file"=>base64_encode($text)));
			return;
		}
			
	}
		
		
}
else{
	
	echo json_encode(Array("success"=>0,"message"=>"FILE \"$dirname/$filename\" NOT FOUND"));
}
?>
