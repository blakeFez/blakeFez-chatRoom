<?php
//项目路径
define( 'DIR_WEBSOCKET', dirname(__FILE__) );
//最大数据 8K
define( 'WEBSOCKET_MAX', 1024 * 8 );
//最大使用内存
define( 'WEBSOCKET_MEMORY', '512M' );
//最大同时在线数
define( 'WEBSOCKET_ONLINE', 50 );
//HOST
define( 'WEBSOCKET_HOST', 'localhost' );
//PORT
define( 'WEBSOCKET_PORT', 843 );
//允许的域名
define( 'WEBSOCKET_DOMAIN', '' );
//api 的key
define( 'WEBSOCKET_KEY', 'Q#WHJGIOU*(&_}{:?PO-78SE#$%^&*()O' );
//管理员密码
define( 'ADMIN_PASS', '123456' );
//接收缓冲区 最大字节
define( 'WEBSOCKET_RCVBUF', WEBSOCKET_MAX );
//发送缓冲区 最大字节
define( 'WEBSOCKET_SNDBUF', WEBSOCKET_MAX );
//10以前的版本请求类型
define( 'WEBSOCKET_TYPE_1', 1 );
//10以后的版本请求类型
define( 'WEBSOCKET_TYPE_2', 2 );