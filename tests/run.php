<?php
$exit_status = 0;
go(function () use (&$exit_status) {
    global $argc, $argv;
    try {
        require __DIR__ . '/../vendor/phpunit/phpunit/phpunit';
    } catch (\Swoole\ExitException $e) {
        $exit_status = $e->getStatus();
        return;
    }
});

\Swoole\Event::wait();
exit($exit_status);