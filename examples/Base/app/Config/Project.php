<?php
return [
	'name' => 'yesf',
	'namespace' => 'YesfApp\\',
	'charset' => 'utf-8',
	'router' => [
		'map' => true,
		'extension' => true
	],
	'static' => [
		'enable' => true,
		'prefix' => '/',
		'dir' => '@APP/Static'
	],
	'modules' => ['index', 'admin'],
	'module' => 'index',
	'view' => [
		'auto' => false,
		'extension' => 'phtml'
	]
];