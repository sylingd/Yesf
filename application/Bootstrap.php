<?php
use yesf\Constant;
use yesf\library\Router;
use yesf\library\Swoole;
class Bootstrap {
	public function run() {
		//注册一个路由
		Router::addRewrite('article/:id/*', ['module' => 'index', 'controller' => 'index', 'action' => 'index']);
		//测试TCP监听
		$config = [
			'ip' => '0.0.0.0',
			'port' => '9502', //监听端口
			'advanced' => [ //关于Swoole的高级选项
				'open_length_check' => 1,
				'package_length_type' => 'N',
				'package_length_offset' => 0,
				'package_body_offset' => 4,
				'package_max_length' => 2097152, // 1024 * 1024 * 2,
				'buffer_output_size' => 3145728, //1024 * 1024 * 3,
				'open_tcp_nodelay' => 1,
				'backlog' => 3000,
			]
		];
		Swoole::addListener(Constant::LISTEN_TCP, $config, [$this, 'tcpCallback']);
		//测试UDP监听
		$config = [
			'ip' => '0.0.0.0',
			'port' => '9503', //监听端口
			'advanced' => [ //关于Swoole的高级选项
				'open_length_check' => 1,
				'package_length_type' => 'N',
				'package_length_offset' => 0,
				'package_body_offset' => 4,
				'package_max_length' => 2097152, // 1024 * 1024 * 2
			]
		];
		Swoole::addListener(Constant::LISTEN_UDP, $config, [$this, 'udpCallback']);
	}
	public function tcpCallback($type, $fd, $from_id, $data = NULL) {
		if ($type === 'receive') {
			$data = substr($data, 4); //前四位是包长度
			echo 'Receive tcp data: ', $data, '(', strlen($data), ')', "\n";
			$sendStr = 'success';
			$sendStr = pack('N', strlen($sendStr)) . $sendStr;
			Swoole::send($sendStr, $fd, $from_id);
		}
	}
	public function udpCallback($type, $fd, $from_id, $data = NULL) {
		if ($type === 'receive') {
			$data = substr($data, 4); //前四位是包长度
			echo 'Receive udp data: ', $data, '(', strlen($data), ')', "\n";
			$sendStr = 'success';
			$sendStr = pack('N', strlen($sendStr)) . $sendStr;
			Swoole::send($sendStr, $fd, $from_id);
		}
	}
}