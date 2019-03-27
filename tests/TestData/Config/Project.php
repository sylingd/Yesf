<?php
return [
	'name' => 'yesf',
	'namespace' => 'YesfApp',
	'charset' => 'utf-8',
	'bootstrap' => 'Bootstrap',
	'router' => [
		'type' => 'map',
		'extension' => true
	],
	'modules' => ['index', 'admin'],
	'module' => 'index',
	'view' => [
		'auto' => true,
		'extension' => 'phtml'
	]
];