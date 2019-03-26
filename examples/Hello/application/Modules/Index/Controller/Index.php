<?php
namespace YesfApp\Modules\Index\Controller;

use Yesf\ControllerInterface;
use Yesf\ControllerAbstract;

class Index extends ControllerAbstract implements ControllerInterface {
	public function IndexAction($request, $response) {
		$response->assign('message', 'Hello, Yesf!');
	}
}