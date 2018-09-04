<?php
namespace yesfApp\controller\api;
use \yesf\library\ControllerAbstract;
class Index extends ControllerAbstract {
	public static function indexAction($request, $response) {
		$response->write(json_encode(['message' => 'Hello']));
	}
}