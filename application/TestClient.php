<?php
echo "test tcp\n";
$tcp = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
$tcp->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,       //第N个字节是包长度的值
    'package_body_offset'   => 4,       //第几个字节开始计算长度
    'package_max_length'    => 2000000,  //协议最大长度
));
$tcp->on("receive", function(swoole_client $cli, $data){
    echo "Receive TCP: $data\n";
});
$tcp->on("connect", function(swoole_client $cli) {
	for ($i = 0; $i < 5; $i++) {
		$sendStr = str_repeat('test', rand(1, 5));
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		$cli->send($sendData);
		echo "send length=" . strlen($sendData) . "\n";
	}
});
$tcp->on("error", function(swoole_client $cli){
    echo "tcp error\n";
});
$tcp->on("close", function(swoole_client $cli){
    echo "tcp connection close\n";
});
$tcp->connect('127.0.0.1', 9502);


echo "test udp\n";
$udp = new swoole_client(SWOOLE_SOCK_UDP, SWOOLE_SOCK_ASYNC);
$udp->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,       //第N个字节是包长度的值
    'package_body_offset'   => 4,       //第几个字节开始计算长度
    'package_max_length'    => 2000000,  //协议最大长度
));
$udp->on("receive", function(swoole_client $cli, $data){
    echo "Receive UDP: $data\n";
});
$udp->on("connect", function(swoole_client $cli) {
	for ($i = 0; $i < 5; $i++) {
		$sendStr = str_repeat('test', rand(1, 5));
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		$cli->send($sendData);
		echo "send length=" . strlen($sendData) . "\n";
	}
});
$udp->on("error", function(swoole_client $cli){
    echo "udp error\n";
});
$udp->connect('127.0.0.1', 9503);



echo "test unix\n";
$unix = new swoole_client(SWOOLE_UNIX_STREAM, SWOOLE_SOCK_ASYNC);
$unix->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,       //第N个字节是包长度的值
    'package_body_offset'   => 4,       //第几个字节开始计算长度
    'package_max_length'    => 2000000,  //协议最大长度
));
$unix->on("receive", function(swoole_client $cli, $data){
    echo "Receive UNIX: $data\n";
});
$unix->on("connect", function(swoole_client $cli) {
	for ($i = 0; $i < 5; $i++) {
		$sendStr = str_repeat('test', rand(1, 5));
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		$cli->send($sendData);
		echo "send length=" . strlen($sendData) . "\n";
	}
});
$unix->on("error", function(swoole_client $cli){
    echo "unix error\n";
});
$unix->on("close", function(swoole_client $cli){
    echo "unix connection close\n";
});
$unix->connect('/data/Yesf/unix.sock');
echo "test unix dgram\n";


$unix_dgram = new swoole_client(SWOOLE_UNIX_DGRAM, SWOOLE_SOCK_ASYNC);
$unix_dgram->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,       //第N个字节是包长度的值
    'package_body_offset'   => 4,       //第几个字节开始计算长度
    'package_max_length'    => 2000000,  //协议最大长度
));
$unix_dgram->on("connect", function(swoole_client $cli) {
	for ($i = 0; $i < 5; $i++) {
		$sendStr = str_repeat('test', rand(1, 5));
		$sendData = pack('N', strlen($sendStr)) . $sendStr;
		$cli->send($sendData);
		echo "send length=" . strlen($sendData) . "\n";
	}
});
$unix_dgram->on("error", function(swoole_client $cli){
    echo "UNIX dgram error\n";
});

$unix_dgram->connect('/data/Yesf/unix_dgram.sock');
