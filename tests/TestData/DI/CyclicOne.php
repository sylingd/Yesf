<?php
namespace TestApp\DI;

class CyclicOne {
	/** @Autowired TestApp\DI\CyclicTwo */
	public $two;
}