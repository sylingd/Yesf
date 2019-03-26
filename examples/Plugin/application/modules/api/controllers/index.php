<?php
namespace YesfApp\controller\api;
use \yesf\ControllerAbstract;
class Index extends ControllerAbstract {
	public static function indexAction($request, $response) {
		$response->write(json_encode(['message' => 'Hello']));
	}
}