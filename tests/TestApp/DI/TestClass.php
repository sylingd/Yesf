<?php
namespace TestApp\DI;

class TestClass {
	/**
	 * Should be ignored
	 * @Autowired TestApp\DI\ClassOne
	 */
	public static $static_one = null;

	/** @Autowired ClassOneAlias */
	public $one_alias;

	/** @Autowired TestApp\DI\ClassOne */
	private $one;

	/** @Autowired TestApp\DI\ClassTwo */
	private $two;

	public $obj1_from_constructor;
	public $obj2_from_constructor;
	public $plain_var;
	public $plain_var_with_type;
	public $plain_var_with_default;

	/**
	 * @Autowired obj2_from_constructor TestApp\DI\ClassTwo
	 */
	public function __construct(
		ClassOne $obj1_from_constructor,
		$obj2_from_constructor,
		$plain_var,
		string $plain_var_with_type,
		$plain_var_with_default = 10)
	{
		$this->obj1_from_constructor = $obj1_from_constructor;
		$this->obj2_from_constructor = $obj2_from_constructor;
		$this->plain_var = $plain_var;
		$this->plain_var_with_type = $plain_var_with_type;
		$this->plain_var_with_default = $plain_var_with_default;
	}

	public function setTwo($obj) {
		$this->two = $obj;
	}
	public function getTwo() {
		return $this->two;
	}

	public function getOneResult() {
		return $this->one->getName();
	}
}