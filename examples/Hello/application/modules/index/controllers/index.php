<?php
namespace yesfApp\controller\index;
use \yesf\ControllerAbstract;
class Index extends ControllerAbstract {
	public static function indexAction($request, $response) {
		$response->assign('message', 'Hello, Yesf!');
	}
}