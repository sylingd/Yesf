<?php
/**
 * 容器基本类
 * 
 * @author ShuangYa
 * @package Yesf
 * @category DI
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017-2018 ShuangYa
 * @license https://yesf.sylibs.com/license
 */
namespace Yesf\DI;

use Psr\Container\ContainerInterface;
use Yesf\Exception\NotFoundException;
use Yesf\Exception\InvalidClassException;
use Yesf\Exception\CyclicDependencyException;

class Container implements ContainerInterface {
	private $instance = [];
	private $alias = [];
	private static $_instance = NULL;
	public static function getInstance() {
		if (self::$_instance === NULL) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	private function __construct() {
		// Do nothing
	}
	public function setAlias($id1, $id2) {
		$this->alias[$id1] = $id2;
	}
	/**
	 * Has
	 * @param string $id
	 * @return bool
	 */
	public function has(string $id) {
		while (isset($this->alias[$id])) {
			$id = $this->alias[$id];
		}
		if (isset($this->instance[$id])) {
			return TRUE;
		}
		if (class_exists($id)) {
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * Get
	 * @param string $id
	 * @param boolean $must_create
	 * @param array $from Check cyclic dependency
     * @return object
	 */
	public function get(string $id, bool $must_create = FALSE, array $from = []) {
		while (isset($this->alias[$id])) {
			$id = $this->alias[$id];
		}
		if (isset($this->instance[$id]) && !$must_create) {
			return $this->instance[$id];
		}
		if (!class_exists($id)) {
			throw new NotFoundException("Class $id not found");
		}
		// Check cyclic dependency
		if (in_array($id, $from, TRUE)) {
			throw new CyclicDependencyException("Found cyclic dependency of $id");
		}
		$ref = new ReflectionClass($id);
		if (!$ref->isInstantiable()) {
			throw new InvalidClassException("Can not create instance of $id");
		}
		// constructor
		$constructor = $ref->getConstructor();
		if ($constructor !== NULL) {
			// Read comment for alias
			$comment = $constructor->getDocComment();
			$is_autowire = preg_match_all('/@Autowired\s+([\w\\\\]+)\s+([\w\\\\]+)\s+/', $comment, $autowire_matches);
			$alias = [];
			if ($is_autowire && count($autowire_matches[1]) > 0) {
				foreach ($autowire_matches[1] as $k => $v) {
					$alias[$v] = $autowire_matches[2][$k];
				}
			}
			$params = $constructor->getParameters();
			$init_params = [];
			foreach ($params as $param) {
				if ($param->isOptional()) {
					$init_params[] = $param->getDefaultValue();
				} elseif (isset($alias[$param->getName()])) {
					$typeName = $alias[$param->getName()];
					$from[] = $typeName;
					$init_params[] = $this->get($typeName, FALSE, $from);
				} elseif ($param->hasType()) {
					$type = $param->getType();
					if (class_exists('ReflectionNamedType') && $type instanceof \ReflectionNamedType) {
						$typeName = $type->getName();
					} else {
						$typeName = $type->__toString();
					}
					if ($type->isBuiltin()) {
						$value = NULL;
						settype($value, $typeName);
						$init_params[] = $value;
					} else {
						$from[] = $typeName;
						$init_params[] = $this->get($typeName, FALSE, $from);
					}
				} else {
					$init_params[] = NULL;
				}
			}
			$instance = $ref->newInstance(...$init_params);
		} else {
			$instance = $ref->newInstance();
		}
		// properties
		$properties = $ref->getProperties();
		foreach ($properties as $property) {
			if ($property->isStatic()) {
				continue;
			}
			$comment = $property->getDocComment();
			$is_autowire = preg_match('/@Autowired\s+([\w\\\\]+)\s+/', $comment, $autowire);
			if ($is_autowire) {
				// Using getter and setter
				$propertyName = $property->getName();
				$setter = 'set' . ucfirst($propertyName);
				$getter = 'get' . ucfirst($propertyName);
				if (method_exists($instance, $setter) && method_exists($instance, $getter)) {
					if ($instance->$getter() === NULL) {
						$from[] = $autowire[1];
						$instance->$setter($this->get($autowire[1], FALSE, $from));
					}
				} else {
					$is_public = $property->isPublic();
					if (!$is_public) {
						$property->setAccessible(TRUE);
					}
					if ($property->getValue($instance) === NULL) {
						$from[] = $autowire[1];
						$property->setValue($instance, $this->get($autowire[1], FALSE, $from));
					}
					if (!$is_public) {
						$property->setAccessible(FALSE);
					}
				}
			}
		}
		// put into instance
		$this->instance[$id] = $instance;
		return $instance;
	}
}