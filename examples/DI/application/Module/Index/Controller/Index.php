<?php
namespace YesfApp\Module\Index\Controller;

class Index {
	/**
	 * You can aslo using DI with those comment:
	 * @Autowired YesfApp\Utils
	 */
	private $utils;

	public function IndexAction($request, $response) {
		$response->write($this->utils->getTime());
	}
}