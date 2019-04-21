<?php
namespace YesfApp\Module\Index\Controller;

class Index {
	public function IndexAction($request, $response) {
		$response->assign('message', 'Hello, Yesf!');
	}
}