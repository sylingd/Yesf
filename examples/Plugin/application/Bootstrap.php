<?php
use Yesf\Router;
use Yesf\Swoole;
use YesfApp\library\PluginHandler;

class Bootstrap {
	public function run() {
		Yesf::getLoader()->addPsr4('yesfApp\\library\\', APP_PATH . 'library');
		PluginHandler::register();
	}
}