<?php
class Tag extends CGObject {

  public function __construct($conn,$data=NULL) {
    $this->conn = $conn;
    $this->vars = new DataStore($data);
    $this->pkey = "tagid";
    $this->table_name = "CgTagsTable";

    // clean up the tags
    $this->clean();
  }

  private function clean() {
    if ($this->vars->exists('tag_name')) {
      $tag_name = trim( strtolower($this->vars->get('tag_name')) );
      $tag_slug = trim( CleanText::sanitize(strtolower($this->vars->get('tag_name'))) );
      $this->vars->set('tag_name',$tag_name);
      $this->vars->set('tag_slug',$tag_slug);
    }
    else {
      throw new Exception("Missing required identifier 'tag_name'");
    }
  }

  public function save() {
    $status = 0;
    $error= "Success";
    // so if this exists in the database already
    try {
      if ($this->exists()) {
        // not sure we need to do an update here
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
    if ($this->vars->exists('tag_slug')) {
      $slug = $this->vars->get('tag_slug');
      // check to see if it's there
      //$results = $table->sql("SELECT EXISTS(SELECT 1 FROM cg_tags_table WHERE tag_slug = '" . $slug . "')")->run(TRUE);
      $results = $table->select(array( $this->pkey ))->where("tag_slug = '" . $slug ."'")->run(TRUE);
      if (count($results) > 0) {
        $this->vars->set($this->pkey,$results[0]->tagid);
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
    $table = new CGTagsTable ($conn);
    $result = $table->select()->where("tagid=".$id)->run(TRUE);
    // if rows
    if (!$returnArray) {
      foreach ($result as $row) {
        $results[] = new Tag($conn,$row);
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
    $table = new CgTagsTable ($conn);
    $result = $table->select()->where("tag_name='".strtolower(trim($name))."'")->run(TRUE);
    // if rows
    if (!$returnArray) {
      foreach ($result as $row) {
        $results[] = new Tag($conn,$row);
      }
    }
    else {
      $results = $result->asArray();
    }

    // handle results
    return $results;
  }
}
