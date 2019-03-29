<?php
$exit_status = 0;
go(function () use (&$exit_status) {
	global $argc, $argv;
	try {
		require __DIR__ . '/../vendor/phpunit/phpunit/phpunit';
	} catch (\Swoole\ExitException $e) {
		echo $e;
		$exit_status = $e->getStatus();
		return;
	} catch (\Throwable $e) {
		echo $e;
		$exit_status = 1;
	}
});

\Swoole\Event::wait();
exit($exit_status);