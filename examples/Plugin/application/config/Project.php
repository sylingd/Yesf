<?php
return [
	'name' => 'yesf',
	'namespace' => 'yesfApp',
	'charset' => 'utf-8',
	'bootstrap' => 'Bootstrap',
	'router' => [
		'type' => 'map',
		'extension' => TRUE
	],
	'modules' => ['api', 'admin'],
	'module' => 'api',
	'view' => [
		'auto' => FALSE,
		'extension' => 'phtml'
	]
];