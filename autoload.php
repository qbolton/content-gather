<?php
function cg_class_autoload($class_name) {
  // unserialize the array
  $import_files = unserialize(AUTOLOAD_IMPORT_FILES);
  if (array_key_exists($class_name,$import_files)) {
    include_once($import_files[$class_name]);
  }
}
spl_autoload_register('cg_class_autoload');
