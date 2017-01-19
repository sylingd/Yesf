<?php
/**
 * 控制台
 * 
 * @author ShuangYa
 * @package Yesf
 * @category Tool
 * @link https://www.sylingd.com/
 * @copyright Copyright (c) 2017 ShuangYa
 * @license https://yesf.sylibs.com/license
 */

class Client {
	public $client = NULL;
	public $ip;
	public $port;
	public function __construct($ip, $port) {
		$this->ip = $ip;
		$this->port = $port;
		$this->connect();
		$this->close();
	}
	protected function connect() {
		$this->client = new swoole_client(SWOOLE_TCP);
		$this->client->set([
			'open_length_check' => 1,
			'package_length_type' => 'N',
			'package_length_offset' => 0,
			'package_body_offset' => 4,
			'package_max_length' => 2000000,
			'open_tcp_nodelay' => 1
		]);
		if (!$this->client->connect($this->ip, $this->port, 1)) {
			throw new \Exception('Can not connect to server');
		}
	}
	public function close() {
		$this->client->close();
		$this->client = NULL;
	}
	public function send($action, $data = []) {
		$this->connect();
		$data['action'] = $action;
		$sendStr = json_encode($data);
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		$this->client->send($sendData);
		$data = $this->client->recv();
		if ($data === FALSE) {
			$rs = FALSE;
		}
		$rs = json_decode(substr($data, 4), 1);
		$this->close();
		return $rs;
	}
	public function getError() {
		return $this->client->errCode;
	}
}