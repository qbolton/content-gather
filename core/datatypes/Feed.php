<?php
class Feed extends CGObject {
  // ========================================================
  // Function update
  // Updates a feed row based on the items in the passed
  // data array
  // ========================================================
  static public function update($data) {
    // make sure that data is an array
    if (is_object($data)) {
      // cast it to array
      $update_data = (array) $data;
    }
    else if (is_array($data)) {
      $update_data = $data;
    }
    else {
      throw Exception('Unable to update cg_feeds table. Update data is invalid');
    }

    // make sure that the array has feed id in it
    if ((isset($update_data['fid'])) && ($update_data['fid'] > 0)) {
      $conn = new DBConnection($GLOBALS['cli']->dbinfo);
      // create table instance
      $table = new CgFeedsTable($conn);
      // execute the update
      $result = $table->update($update_data)->where('fid = ' . $update_data['fid'])->run();
    }
    else {
      throw Exception('Unable to update cg_feeds table. Feed id (fid) not present in update data array');
    }
  }
}
