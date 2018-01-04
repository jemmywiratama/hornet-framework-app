<?php

$client = new swoole_client(SWOOLE_SOCK_TCP);

if (!$client->connect('127.0.0.1','9501')) {
    echo "connect failed\n";
    die;
}
$_config = array(
    'host' => 'smtp.vip.163.com',
    'port' => 25,
    'from' => array('address' => 'ismond@vip.163.com', 'name' => 'Administrator'),
    'encryption' => 'ssl',
    'username' => 'ismond@vip.163.com',
    'password' => 'ismond163vip',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'amdin_email' => 'ismond@vip.163.com',
    'timeout'=>30
);

$json_data = json_encode(['cmd' => 'email.send_by_api', 'to'=>'weichaoduo@163.com', 'config'=>$_config, 'subject' => '测试发送', 'content' => '内容']);
$client->send($json_data);
$client->close();
