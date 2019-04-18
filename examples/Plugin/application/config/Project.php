<?php
return [
	'name' => 'yesf',
	'namespace' => 'YesfApp\\',
	'charset' => 'utf-8',
	'bootstrap' => 'Bootstrap',
	'router' => [
		'extension' => true
	],
	'modules' => ['api', 'admin'],
	'module' => 'api',
	'view' => [
		'auto' => false,
		'extension' => 'phtml'
	]
];