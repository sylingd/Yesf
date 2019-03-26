<?php
use yesf\Constant;
use yesf\Router;
use yesf\Swoole;
use yesfApp\library\PluginHandler;

class Bootstrap {
	public function run() {
		Yesf::getLoader()->addPsr4('yesfApp\\library\\', APP_PATH . 'library');
		PluginHandler::register();
	}
}