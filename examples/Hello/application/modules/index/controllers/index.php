<?php
namespace YesfApp\controller\index;
use Yesf\ControllerAbstract;
class Index extends ControllerAbstract {
	public static function indexAction($request, $response) {
		$response->assign('message', 'Hello, Yesf!');
	}
}