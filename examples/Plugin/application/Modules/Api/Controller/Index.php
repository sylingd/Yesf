<?php
namespace YesfApp\Modules\Api\Controller;

use Yesf\ControllerInterface;
use Yesf\ControllerAbstract;

class Index extends ControllerAbstract implements ControllerInterface {
	public static function IndexAction($request, $response) {
		$response->write(json_encode(['message' => 'Hello']));
	}
}