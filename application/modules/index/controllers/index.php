<?php
namespace yesfApp\controller;
class index {
	public static function indexAction($request, $response) {
		$response->assign('server', $request->server);
		$response->assign('header', $request->header);
		$response->assign('get', $request->get);
		$response->assign('post', $request->post);
		$response->assign('param', $request->param);
	}
}