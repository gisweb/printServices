<?php
error_reporting(E_ERROR);
define('BASE_PATH',realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('LIB_DIR',BASE_PATH."lib".DIRECTORY_SEPARATOR);
define('INC_DIR',BASE_PATH."include".DIRECTORY_SEPARATOR);
define('MODEL_DIR',BASE_PATH."modelli".DIRECTORY_SEPARATOR);
define('DOC_DIR',BASE_PATH."documenti".DIRECTORY_SEPARATOR);
define('DBG_DIR',BASE_PATH."debug".DIRECTORY_SEPARATOR);
define('TMP_DIR',"D:".DIRECTORY_SEPARATOR."temp".DIRECTORY_SEPARATOR);
//define('LIBREOFFICE',"/opt/libreoffice3.6/program/");
define('LIBREOFFICE',"");
define('MODEL',BASE_PATH."modelli".DIRECTORY_SEPARATOR);
define('CMD_DIR',"");
$Library=Array(LIB_DIR."tbs_class.php",LIB_DIR."tbs_plugin_opentbs.php",LIB_DIR."nusoap/nusoap.php",LIB_DIR."utils.php",LIB_DIR."utils.class.php",LIB_DIR."print.class.php");
foreach($Library as $lib) require_once $lib;
?>
