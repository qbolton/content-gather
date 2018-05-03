<?php
class Taxonomy extends CGObject {

  // Can handle inserting multiple taxonomy rows
  static private function insert($data) {
    $insert_stmt = new SQLExecutor($GLOBALS['cli']->dbconn);
    $insert_item = array();
    // loop over potential insert data
    foreach ($data as $tx) {
      // make sure each tx object has the right stuff
      if ( isset($tx->site_id) && isset($tx->tagid) && isset($tx->pid) ) {
        // build statement
        $insert_item[] = "({$tx->site_id},{$tx->tagid},{$tx->pid})";
      }
    }

    // complete insert statement if there are insert items
    if (count($insert_item) > 0) {
      $insert_list = implode(",",$insert_item);
      $insert_list = rtrim($insert_list,",");
      $insert_results = $insert_stmt->sql('INSERT INTO table (site_id, tagid, pid) VALUES ' . $insert_list)->run();
    }
  }

}
