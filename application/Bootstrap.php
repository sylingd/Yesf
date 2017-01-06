<?php
use yesf\library\http\Router;
class Bootstrap {
	public function run() {
		//注册一个路由
		Router::addRewrite('article/:id/*', ['module' => 'index', 'controller' => 'index', 'action' => 'index']);
	}
}