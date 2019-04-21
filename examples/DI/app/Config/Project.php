<?php
return [
	'name' => 'yesf',
	'namespace' => 'YesfApp\\',
	'charset' => 'utf-8',
	'router' => [
		'extension' => true
	],
	'modules' => ['index', 'admin'],
	'module' => 'index',
	'view' => [
		'auto' => false,
		'extension' => 'phtml'
	]
];