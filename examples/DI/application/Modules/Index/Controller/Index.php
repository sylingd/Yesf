<?php
namespace YesfApp\Modules\Index\Controller;

use Yesf\ControllerInterface;
use Yesf\ControllerAbstract;

class Index extends ControllerAbstract implements ControllerInterface {
	/**
	 * You can aslo using DI with those comment:
	 * @Autowired YesfApp\Utils
	 */
	private $utils;

	public function IndexAction($request, $response) {
		$response->write($this->utils->getTime());
	}
}