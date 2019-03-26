<?php
namespace TestApp\DI;

class CyclicTwo {
	/** @Autowired TestApp\DI\CyclicOne */
	public $one;
}