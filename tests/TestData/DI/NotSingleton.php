<?php
namespace TestApp\DI;

class NotSingleton {
	public $id;
	public function __construct() {
		$this->id = uniqid();
	}
}