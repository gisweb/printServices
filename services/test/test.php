<?php
require_once "../../config.php";
$data=Array(
    "cognome"=>"Carbone",
    "nome"=>"Marco",
    "data"=> date("d/m/Y"),
    "protocollo"=>"098708",
    "data_prot"=>"22/07/2014"
);

$res=utilsPrint::createDoc(MODEL_DIR."modello-1.docx", $data);
if ($res["success"]==1){
    $r=utilsPrint::writeFile($res["file"], "docx", false, utilsPrint::rand_str(), DOC_DIR);
    $r1=utilsPrint::convertToPdf($r["file"]);
    utils::dump($r1);
}
 else {
     utils::dump($res);
}
?>
