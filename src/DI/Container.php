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

use ReflectionClass;
use Psr\Container\ContainerInterface;
use Yesf\Exception\NotFoundException;
use Yesf\Exception\InvalidClassException;
use Yesf\Exception\CyclicDependencyException;

class Container implements ContainerInterface {
	const MULTI_CLONE = 1;
	const MULTI_NEW = 2;

	private $instance = [];
	private $alias = [];
	private $multi = [];
	private $creating = [];
	private static $_instance = null;
	public static function getInstance() {
		if (self::$_instance === null) {
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
	public function setMulti($id, $type = self::MULTI_CLONE) {
		$this->multi[$id] = $type;
	}
	/**
	 * Has
	 * @param string $id
	 * @return bool
	 */
	public function has($id) {
		while (isset($this->alias[$id])) {
			$id = $this->alias[$id];
		}
		if (isset($this->instance[$id])) {
			return true;
		}
		if (class_exists($id)) {
			return true;
		}
		return false;
	}
	/**
	 * Get
	 * @param string $id
     * @return object
	 */
	public function get($id) {
		while (isset($this->alias[$id])) {
			$id = $this->alias[$id];
		}
		if (isset($this->instance[$id])) {
			if (!isset($this->multi[$id])) {
				return $this->instance[$id];
			} elseif ($this->multi[$id] === self::MULTI_CLONE) {
				return clone $this->instance[$id];
			}
		}
		if (!class_exists($id)) {
			throw new NotFoundException("Class $id not found");
		}
		// Check cyclic dependency
		if (isset($this->creating[$id])) {
			throw new CyclicDependencyException("Found cyclic dependency of $id");
		}
		$this->creating[$id] = true;
		$ref = new ReflectionClass($id);
		if (!$ref->isInstantiable()) {
			throw new InvalidClassException("Can not create instance of $id");
		}
		// constructor
		$constructor = $ref->getConstructor();
		if ($constructor !== null) {
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
					$init_params[] = $this->get($typeName);
				} elseif ($param->hasType()) {
					$type = $param->getType();
					if (class_exists('ReflectionNamedType') && $type instanceof \ReflectionNamedType) {
						$typeName = $type->getName();
					} else {
						$typeName = $type->__toString();
					}
					if ($type->isBuiltin()) {
						$value = null;
						settype($value, $typeName);
						$init_params[] = $value;
					} else {
						$from[] = $typeName;
						$init_params[] = $this->get($typeName, false, $from);
					}
				} else {
					$init_params[] = null;
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
					if ($instance->$getter() === null) {
						$instance->$setter($this->get($autowire[1]));
					}
				} else {
					$is_public = $property->isPublic();
					if (!$is_public) {
						$property->setAccessible(true);
					}
					if ($property->getValue($instance) === null) {
						$property->setValue($instance, $this->get($autowire[1]));
					}
					if (!$is_public) {
						$property->setAccessible(false);
					}
				}
			}
		}
		// put into instance
		if (!isset($this->multi[$id]) || $this->multi[$id] === self::MULTI_CLONE) {
			$this->instance[$id] = $instance;
		}
		unset($this->creating[$id]);
		return $instance;
	}
}