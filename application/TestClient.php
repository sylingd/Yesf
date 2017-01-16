<?php
$client = new swoole_client(SWOOLE_SOCK_TCP);

$client->set(array(
    'open_length_check'     => true,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,       //第N个字节是包长度的值
    'package_body_offset'   => 4,       //第几个字节开始计算长度
    'package_max_length'    => 2000000,  //协议最大长度
));

if (!$client->connect('127.0.0.1', 9502)) {
    exit("connect failed\n");
}

for ($i = 0; $i < 10; $i++)
{
	$sendStr = str_repeat('test', rand(1, 5));
    $sendData = pack('N', strlen($sendStr)) . $sendStr;
	$client->send($sendData);
    echo "send length=" . strlen($sendData) . "\n";

    $resp = $client->recv();
    $data2 = substr($resp, 4);
    echo "recv " . $resp . "\n";
}
sleep(2);
$client->close();