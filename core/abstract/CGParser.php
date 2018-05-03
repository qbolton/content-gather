<?php
abstract class CGParser extends CGObject {
  public $exclude = FALSE;
  public $raw_item = NULL;

  public function __construct($raw_item) {
    parent::__construct();
    $this->raw_item = $raw_item;
  }
  public function __destruct() {
    parent::__destruct();
    unset($this->exclude);
    unset($this->raw_item);
  }
  protected function parse() {}
  protected function exclude() { return $this->exclude; }
}
