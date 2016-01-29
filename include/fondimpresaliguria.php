<?php
$r=$data["azienda_richiedente_ateco"];
if (!is_array($r)) $r =Array($r);

$data["azienda_richiedente_ateco"]=implode('; ',$r).";";
$data["commissione"]=strtoupper($data["commissione"]);
$data["commissione_prima_m"]=ucwords(strtolower($data["commissione"]));
for($i=0;$i<count($data["membri_commissione_list"]);$i++) $data["membri_commissione_list"][$i]["membri"]=ucwords($data["membri_commissione_list"][$i]["membri"]);
for($i=0;$i<count($data["membri_sindacati_list"]);$i++) {
        $data["membri_sindacati_list"][$i]["membri"]=ucwords($data["membri_sindacati_list"][$i]["membri"]);
        $data["membri_sindacati_list"][$i]["sindacato"]=strtoupper($data["membri_sindacati_list"][$i]["sindacato"]);
}


?>