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
	'modules' => ['index', 'admin'],
	'module' => 'index',
	'view' => [
		'auto' => FALSE,
		'extension' => 'phtml'
	]
];