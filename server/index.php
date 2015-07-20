<?php
require('config.php');
function __autoload($className){
	$file = 'class/'.$className.'.php';
	require($file);
}

if(!CommonUtil::add_lock('lock')){//用于判断是否已经开启
	die('Running');
}

//设置超时时间
ignore_user_abort(true);
set_time_limit(0);

//修改内存
ini_set('memory_limit', WEBSOCKET_MEMORY);

$webSocket = new WebSocket();
$webSocket->run();
echo socket_strerror($webSocket->error());