<?php
define("SCRIPT_PATH",dirname(__FILE__));
define("GWP_PATH",dirname(dirname(__FILE__)));

$GLOBALS['import_files'] = array();

function listFiles($dir) {
  //$import_files = $GLOBALS['import_files'];
  if (is_dir($dir)) {
    foreach (new DirectoryIterator($dir) as $fileInfo) {
      if($fileInfo->isDot()) continue;
      if($fileInfo->isDir()) {
        listFiles($fileInfo->getPathname());
      }
      else {
        $file_name = $fileInfo->getFilename(); 
        if (stristr($file_name,"php") === false) { 
          continue; 
        }
        else {
          // grab the base name
          $file_base_name = basename($file_name);
          //print $file_base_name . "\n";
          $file_parts = explode(".",$file_name);
          //$GLOBALS['import_files'][$file_base_name] = $fileInfo->getPathname();
          $GLOBALS['import_files'][$file_parts[0]] = $fileInfo->getPathname();
        }
      }
    }
  }
  return $GLOBALS['import_files'];
}

// build the include file
$path_array = array(
  GWP_PATH . "/model", 
  GWP_PATH . "/core", 
  GWP_PATH . "/modules",
  GWP_PATH . "/custom" 
);
foreach ($path_array as $dir) {
  listFiles($dir);
}

// write the files into the lib import file
$fp = fopen(GWP_PATH . "/import.php",'w');

// serialize the import directory
$import_files = serialize($GLOBALS['import_files']);

fwrite($fp,"<?php\n");
fwrite($fp,"define('AUTOLOAD_IMPORT_FILES','{$import_files}');\n");
//foreach($GLOBALS['import_files'] as $key => $value) {
//fwrite($fp,"define(\"AUTOLOAD_IMPORT_FILES\"\n");
//}
fclose($fp);
?>
