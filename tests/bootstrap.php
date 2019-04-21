<?php
use Yesf\Yesf;
use Yesf\Event\Internal;
define('PROJECT_PATH', realpath(__DIR__ . '/..') . '/');
define('APP_PATH', __DIR__ . '/TestApp/');
define('TEST_SRC', __DIR__ . '/Cases/');
define('TEST_APP', __DIR__ . '/TestApp/');
define('YESF_ROOT', PROJECT_PATH . 'src/');
define('YESF_UNIT', 1);
define('YESF_TEST', __DIR__ . '/');
require(PROJECT_PATH . '/vendor/autoload.php');

function getLoader() {
	static $loader = null;
	if ($loader === null) {
		$classes = get_declared_classes();
		foreach ($classes as $clazz) {
			if (strpos($clazz, 'ComposerAutoloaderInit') === 0 && method_exists($clazz, 'getLoader')) {
				$loader = $clazz::getLoader();
				break;
			}
		}
		if ($loader === null) {
			throw new Exception('Composer loader not found');
		}
	}
	return $loader;
}

$app = new Yesf();
$app->setEnvConfig(APP_PATH . 'Config/env.ini');

getLoader()->addPsr4('YesfTest\\', TEST_SRC);
getLoader()->addPsr4('TestApp\\', TEST_APP);

Internal::onWorkerStart();