<?php
abstract class CGModule extends CGObject {
  protected $post = NULL;

  public function __construct($post) {
    parent::__construct();
    $this->post = $post;
  }
  public function __destruct() {
    parent::__destruct();
    unset($this->post);
  }
  protected function run() {}
}
