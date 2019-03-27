<?php
namespace TestApp\DI;

class MultiWithClone {
	public $id;
	public $cloned = FALSE;
	public function __construct() {
		$this->id = '';
	}
	public function __clone() {
		$this->id = uniqid();
		$this->cloned = TRUE;
	}
}