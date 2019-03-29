<?php
trait CoTrait {
	public function ut($func) {
        $process = new \Swoole\Process(function(\Swoole\Process $process) use ($func) {
            go(function() use ($func) {
				$func();
            });
        });
        $process->start();
        $process->wait();
	}
}