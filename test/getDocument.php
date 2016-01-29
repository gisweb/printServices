<?php
function directoryToArray($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '') {
        $arrayItems = array();
        $skipByExclude = false;
        $handle = opendir($directory);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
            preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
            if($exclude){
                preg_match($exclude, $file, $skipByExclude);
            }
            if (!$skip && !$skipByExclude) {
                if (is_dir($directory. DIRECTORY_SEPARATOR . $file)) {
                    if($recursive) {
                        $arrayItems = array_merge($arrayItems, directoryToArray($directory. DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude));
                    }
                    if($listDirs){
                        $file = $directory . DIRECTORY_SEPARATOR . $file;
                        $arrayItems[] = $file;
                    }
                } else {
                    if($listFiles){
                        $file = $directory . DIRECTORY_SEPARATOR . $file;
                        $arrayItems[] = $file;
                    }
                }
            }
        }
        closedir($handle);
        }
        return $arrayItems;
    }


    require_once "../config.php";
    $projects=Array("<option value=''>Seleziona un progetto</option>");
    $pr=  directoryToArray('../documenti',false,true,false);
    foreach($pr as $v) {
        $tmp=explode("/",$v);
        $projects[]="<option value='".$tmp[count($tmp)-1]."'>".$tmp[count($tmp)-1]."</option>";
    }
    $options["project"]=implode("",$projects);
?>

<html>
    <head>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
        <style>
            div.container{
                width:500px;
                padding:10px;
            }
            select.data,input.data{
                width:200px;
                margin-left:20px;
                float:right;
            }
        </style>
    </head>
    <body>
        <form method="POST" action="../services/xGetDocument.php" target="_new">
            <div class="container">
                <label for="project">Progetto</label>
                <select class="data" name="project" id="project">
                    <?php echo $options["project"] ?>
                </select>
            </div>
            <div class="container">
                <label for="app">Applicazione</label>
                <input class="data" type="text" name="app" id="app" value=""/>
            </div>
            
            <div class="container">
                <label for="id">Id</label>
                <input class="data" type="text" name="id" id="id" value=""/>
            </div>
            <div class="container">
                <label for="filename">File</label>
                <input class="data" type="text" name="filename" id="filename" value=""/>
            </div>
            <input type="submit" value="Invia"/>
        </form>
        
    </body>
</html>