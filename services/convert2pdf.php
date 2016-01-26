<?php
/*
Converte il contenuto binario di un file .docx in un contenuto binario di tipo pdf 
*/
function convert2Pdf($content){
    $docName="/tmp/".uniqid().".docx";
    $f=fopen($docName,'w');
    //Controllare che il file venga scritto correttamente
    fwrite($f,$content);
    fclose($f);

    $cmd=sprintf("HOME=/tmp/pdfout %ssoffice \"-env:UserInstallation=file:///tmp/pdfout\" --headless --invisible --nologo --convert-to pdf %s --outdir /tmp",LIBREOFFICE,escapeshellarg($docName));
    $res=exec($cmd);

    $msg1="Overwriting:";
    $msg2="convert";
    if (stripos($res,$msg1)===FALSE and stripos($res,$msg2)===FALSE){
        debug($debugName,$res);
        return Array("success"=>0,"message"=>$res);
    }
    else{
        $pdfName=str_replace('.odt','',str_replace('.docx','',$docName)).".pdf";
        $f = fopen($pdfName,'r');
        $pdfContent=fread($f,filesize($pdfName));
        return Array("success"=>1,"content"=>$pdfContent);
    }

}
