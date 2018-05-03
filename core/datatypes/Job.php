<?php
class Job extends CGObject {

  public function __construct($conn,$data=NULL) {
    $this->conn = $conn;
    $this->vars = new DataStore($data);
    $this->pkey = "job_id";
    $this->table_name = "CgJobsTable";
  }

  public function save() {
    $status = 0;
    $error= "Success";
    // so if this exists in the database already
    try {
      if ($this->exists()) {
        // then do an update
        $this->update();
      }
      else {
        // do an insert
        $this->insert();
      }
    }
    catch(Exception $e) {
      throw new AppException($e->getMessage(),__METHOD__,$e->getCode());
    }
    return (object) array('status'=>$status,'msg'=>$error,'id'=>$this->vars->get($this->pkey));
  }

  public function exists() {
    $retval = FALSE;
    $table = new $this->table_name ($this->conn);
    if ($this->vars->exists($this->pkey)) {
      $pkey_value = $this->vars->get($this->pkey);
      // check to see if it's there
      $results = $table->select(array( $this->pkey ))->where("{$this->pkey} = " . $pkey_value)->run(TRUE);
      if (count($results) > 0) {
        $retval = TRUE;
      }
    }
    return $retval;
  }

  private function update() {
    // get the properties
    $data = $this->vars->get();
    $id = $this->vars->get($this->pkey);
    $table = new $this->table_name ($this->conn);
    $table->update($data)->where("{$this->pkey}=$id")->run();
  }

  private function insert() {
    // get the properties
    $data = $this->vars->get();
    $id = $this->vars->get($this->pkey);
    $table = new $this->table_name ($this->conn);
    $table->insert($data)->run();
    $this->vars->get($this->pkey,$result->getInsertId());
  }

  static public function getById($id,$returnArray=TRUE) {
    $conn = $GLOBALS['cli']->dbconn;
    $results = NULL;
    $table = new CGJobsTable ($conn);
    $result = $table->select()->where("job_id=".$id)->run(TRUE);
    // if rows
    if (!$returnArray) {
      foreach ($result as $row) {
        $results[] = new Job($conn,$row);
      }
    }
    else {
      $results = $result->asArray();
    }
      
    // handle results
    if (count($results) > 0) {
      return $results;
    }
    else {
      return NULL;
    }
  }

  static public function getByName($name,$returnArray=TRUE) {
    $conn = $GLOBALS['cli']->dbconn;
    $results = NULL;
    $table = new CgJobsTable ($conn);
    $result = $table->select()->where("job_name='".strtolower(trim($name))."'")->run(TRUE);
    // if rows
    if (!$returnArray) {
      foreach ($result as $row) {
        $results[] = new Job($conn,$row);
      }
    }
    else {
      $results = $result->asArray();
    }

    // handle results
    return $results;
  }
}
