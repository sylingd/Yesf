<?php
use yesf\Constant;
use yesf\library\Router;
use yesf\library\Swoole;
use yesfApp\library\PluginHandler;

class Bootstrap {
	public function run() {
		Yesf::getLoader()->addPsr4('yesfApp\\library\\', APP_PATH . 'library');
		PluginHandler::register();
	}
}