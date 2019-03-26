<?php
return [
	'name' => 'yesf',
	'namespace' => 'YesfApp',
	'charset' => 'utf-8',
	'bootstrap' => 'Bootstrap',
	'router' => [
		'type' => 'map',
		'extension' => TRUE
	],
	'modules' => ['index', 'admin'],
	'module' => 'index',
	'view' => [
		'auto' => FALSE,
		'extension' => 'phtml'
	]
];