<?php
go(function () {
    global $argc, $argv;
    try {
        require __DIR__ . '/../vendor/phpunit/phpunit/phpunit';
    } catch (\Swoole\ExitException $e) {
        return;
    }
});

\Swoole\Event::wait();