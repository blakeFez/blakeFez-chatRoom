<?php
/**
 * @Author: blakeFez
 * @Date: 2015-07-19
 * @note: websocket类
 */
class SocketService{
	// 允许的域名
	private $domain = WEBSOCKET_DOMAIN;
	// 监听的资源
	private $socket = null;
	// 全部用户
	private $accept = array();
	// 全部类型
	private $type = array();
	// 绑定储存数据
	private $bind = array();
	// time 时间
	private $time = array();
	// 阻塞请求
	private $cycle = array();
	// 储存类
	private $class = array();
	
	/**
	 * 启动
	 */
	function run(){
		if(!$this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)){
			return false;
		}
		// 允许使用本地 地址
		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, true);
		// 接收缓冲区 最大字节
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, WEBSOCKET_RCVBUF);
		// 发送缓冲区 最大字节
		socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, WEBSOCKET_SNDBUF);
		// 绑定端口
		if(!socket_bind($this->socket, WEBSOCKET_HOST, WEBSOCKET_PORT)){
			return false;
		}
		if(!socket_listen($this->socket, WEBSOCKET_MAX)){
			return false;
		}
		
		$this->class[WEBSOCKET_TYPE_1] = new class_websocket_1;
		$this->class[WEBSOCKET_TYPE_2] = new class_websocket_2;

		while(true){
			// 设置阻塞请求
			$this->cycle = $this->accept;
			$this->cycle[] = $this->socket;
			socket_select($this->cycle, $write, $except, null);
			foreach($this->cycle as $v){
				if($v === $this->socket){//第一次连接进来
					//建立连接
					$accept = socket_accept($v);
					if(!$accept){
						continue;
					}
					$this->accept[] = $accept;
					$index = array_keys($this->accept);
					$index = end($index);
					$this->type[$index] = false;
					$this->bind[$index] = array();
					$this->time[$index] = time();
					continue;		
				}
				
				//被删除
				if(($index = $this->search($v)) === false){
					continue;;
				}
				
				// 接收数据
				if(!socket_recv($v, $data, WEBSOCKET_MAX, 0) || !$data){//没有数据，将其关闭
					$this->close($v);
					continue;
				}
				
				$type = $this->type[$index];
				
				//还没有传head的
				if($type === false){
					$type = $this->header($data, $v);
					if($type === false){
						$this->close($v);
						continue;
					}
					$time = time();
					$this->type[$index] = $type;
					$this->time[$index] = $time;
					$this->bind[$index]['ip'] = $this->ip($v);
					//按一定概率去除长期没有活动的socket
					if(rand(0,100)){
						continue;
					}
					foreach($this->accept as $ka=>$va){
						if(empty($this->time[$ka]) || ($time - $this->time[$ka]) > 900) {
							$this->close($va);
						}
					}
					continue;
				}
				if(!$data = $this->get($index, $data)){//解码
					$this->close($v);
					continue;
				}
				foreach($data as $vv){
					$this->getAndSend($vv, $v, $index);
				}
			}
		}
		return true;
	}
	
	/**
	 * 搜索连接的index
	 */
	function search($accept){
		$search = array_search($accept, $this->accept, true);
		if($search === null){
			$search = false;
		}
		return $search;
	}
	
	
	/**
	 * 关闭连接
	 */
	function close($accept){
		if(($index = $this->search($accept)) === false){
			return false;
		}
		socket_close($accept);
		$bind = array();
		if(isset($this->accept[$index])){
			unset($this->accept[$index]);
		}
		
		if(isset($this->type[$index])){
			unset($this->type[$index]);
		}

		if(isset($this->bind[$index])){
			$bind = $this->bind[$index];
			unset($this->bind[$index]);
		}
		
		if(isset($this->cycle[$index])){
			unset($this->cycle[$index]);
		}
		if(isset($this->time[$index])){
			unset($this->time[$index]);
		}
		
		//通知所有用户，有人离开
		if(empty($bind['name'])){
			return false;
		}
		$this->sendAll(array('list'=>array(array($bind['name'], false))));
		$this->sendAll(array('msg'=>'<div class="msg logout"><strong class="name">'.$bind['name'].'</strong>离开聊天室</div>'));
		return true;
	}
	
	/**
	 * 解码数据
	 */
	function get($index, $data){
		if(!$data){
			return false;
		}		
		$type = $this->type[$index];
		if(empty($this->class[$type])){
			return false;
		}
		return $this->class[$type]->decode($data);
	}
	
	/**
	 * 获取数据并发送数据
	 */
	function getAndSend($data, $accept, $index){
		// 超过 1024 字节就结束
		if(strlen($data) > 1024){
			return false;
		}
		
		$data = CommonUtil::string_turn_array($data);
		
		//呼吸time包
		if(!empty($data['time'])){
			$time = time();
			$this->time[$index] = $time;
		}elseif(!empty($data['name'])){//添加名称
			$name = htmlspecialchars((string) $data['name'], ENT_QUOTES);
			$number = htmlspecialchars((string) $data['number'], ENT_QUOTES);
			//您已经有名称了
			if(!empty($this->bind[$index]['name'])){
				return  $this->send(array('msg'=>'<div class="msg error">您已经有名称了</div>'), $index);
			}
			$list = array();
			//判断名字是否已经存在
			foreach($this->bind as $k=>$v){
				if(!empty($v['name'])){
				    if($v['number'] == $number){
				        return  $this->send(array('error' => 'true', 'msg' => '<div class="msg error">您已经在其他页面打开该聊天室，6秒后将自动关闭此页面。</div>'), $index);
				    }
					if($v['name'] == $name){
						return $this->send(array('msg' => '<div class="msg error">名称已存在</div>'), $index);
					}
					$list[] = array($v['name'], true);
				}
			}
		
			$this->sendAll(array('list' => array(array($name, true))));
			$this->sendAll(array('msg' => '<div class="msg login"><strong class="name">'. $name .'</strong>登录聊天室</div>'));
		
			$this->bind[$index]['name'] = $name;
			$this->bind[$index]['number'] = $number;
			$list[] = array($name, true);
			$this->send(array('list' => $list), $index);
			return $this->send(array('name' => true, 'msg' => '<div class="msg yes">登录聊天室成功</div>'), $index);
		}elseif(!empty($data['chat'])){//聊天内容
		    $time = date('Y-m-d H:i:s');
			$name  = empty($this->bind[$index]['name']) ? '' : $this->bind[$index]['name'];
			$chat = (string)$data['chat'];
			if(!$name){
				return $this->send(array('msg' => '<div class="msg error">您还没有输入您的名称</div>'), $index);
			}
			$this->send(array('chat'=>'<div class="chat self"><div class="name">'.$name.'  '.$time.'</div><p>'.$chat.'</p></div>'),$index);
			return $this->sendChat(array('chat'=>'<div class="chat"><div class="name">'.$name.'  '.$time.'</div><p>'.$chat.'</p></div>'),$index);
		}
	}
	
	/**
	 * 发送数据
	 */
	function send($data, $index){
		$accept = $this->accept[$index];
		if(!$accept){
			return false;
		}
		$type = $this->type[$index];
		if(empty($this->class[$type])){
			return false;
		}
		if(!$data = $this->class[$type]->encode($data)){
			return false;;
		}
		if(!$write = socket_write($accept, $data, strlen($data))){
			$this->close($accept);
		}
		return true;
	}
	
	/**
	 * 给所有用户发送信息
	 */
	function sendAll($data){
		if(!$data || !$this->accept){
			return false;
		}
		foreach($this->bind as $k=>$v){
			if(empty($v['name'])){
				continue;
			}
			$this->send($data, $k);
		}
	}
	
	/**
	 * 给用户发送聊天信息
	 */
	function sendChat($data,$index){
	    if(!$data || !$this->accept){
	        return false;
	    }
	    foreach($this->bind as $k=>$v){
	        if(empty($v['name']) || $k == $index){
	            continue;
	        }
	        $this->send($data, $k);
	    }
	}
	
	/**
	 * 获取连接的ip地址
	 */
	function ip($accept){
		socket_getpeername($accept, $ip);
		return $ip;
	}
	
	/**
	 * 解析head
	 */
	function header($data, $accept){
		$header = CommonUtil::parse_header($data, true);
		$msg = '';
		
		//最多 4096 信息
		if(strlen($data) >= 4096){
			return false;
		}

		//超过最大在线
		if(WEBSOCKET_ONLINE <= count($this->accept)){
			return false;
		}
		
		//来源
		$origin = empty($header['origin']) ? empty($header['websocket-origin']) ? '' : $header['websocket-origin'] : $header['origin'];
		$parse = parse_url($origin);
		$scheme = empty($parse['scheme']) || $parse['scheme'] != 'https' ? '' : 's';
		$origin = $origin && !empty($parse['host']) ? 'http' . $scheme . '://' . $parse['host'] : '';
		
		//无效来源
		if($this->domain && !empty($parse['host']) && !preg_match('/(^|\.)'. preg_quote($this->domain, '/') .'$/i', $parse['host'])){
			return false;
		}
		
		//10以后的版本
		if(!empty($header['sec-websocket-key'])){
			$type = WEBSOCKET_TYPE_2;
			$a = base64_encode(sha1(trim($header['sec-websocket-key']) . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
			
			$msg .= "HTTP/1.1 101 Switching Protocols\r\n";
			$msg .= "Upgrade: websocket\r\n";
			$msg .= "Connection: Upgrade\r\n";
			if($origin){
				$msg .= "Sec-WebSocket-Origin:{$origin}\r\n";
			}
			$msg .= "Sec-WebSocket-Accept: $a\r\n";
			$msg .= "\r\n";
			if(!socket_write($accept, $msg, strlen($msg))){
				return false;
			}
			return WEBSOCKET_TYPE_2;
		}
		
		//10以前的版本
		if(!empty($header['sec-websocket-key1']) && !empty($header['sec-websocket-key2']) && !empty($header['html'])){			
			$key1 = $header['sec-websocket-key1'];
			$key2 = $header['sec-websocket-key2'];
			$key3 = $header['html'];
			if(!preg_match_all('/([\d]+)/', $key1, $key1_num) || !preg_match_all('/([\d]+)/', $key2, $key2_num)){
				return false;
			}
			$key1_num = implode($key1_num[0]);
			$key2_num = implode($key2_num[0]);
			
			if(!preg_match_all('/([ ]+)/', $key1, $key1_spc) || !preg_match_all('/([ ]+)/', $key2, $key2_spc)){
				return false;
			}
			
			$key1_spc = strlen(implode($key1_spc[0]));
			$key2_spc = strlen(implode($key2_spc[0]));
			
			$key1_sec = pack("N", $key1_num / $key1_spc);
			$key2_sec = pack("N", $key2_num / $key2_spc);
	
			$msg .= "HTTP/1.1 101 Web Socket Protocol Handshake\r\n";
			$msg .= "Upgrade: WebSocket\r\n";
			$msg .= "Connection: Upgrade\r\n";
			if($origin){
				$msg .= "Sec-WebSocket-Origin:{$origin}\r\n";
			}
			$msg .= "Sec-WebSocket-Location: ws{$scheme}://{WEBSOCKET_HOST}:{WEBSOCKET_PORT}{$this->path}\r\n";
			$msg .= "\r\n";
			$msg .= md5($key1_sec.$key2_sec . $key3, true);
			if(!socket_write($accept, $msg, strlen($msg))){
				return false;
			}
			return WEBSOCKET_TYPE_1;
		}
		return false;
	}
	
	/**
	 * 返回错误
	 */
	function error(){
		if(!$this->socket){
			return -1;
		}
		return socket_last_error($this->socket);
	}
}


/**
 * websocket第一个版本的
 */
class class_websocket_1{
	function decode($data){
		$len = strlen($data);
		if($len < 3){
			return false;
		}
		$r = array();
		$k = -1;
		$str = '';
		for($i = 0; $i < $len; $i++){
			$ord = ord($data[$i]);
			if($ord == 0){
				$k++;
				$str = '';
				continue;
			}
			if($ord == 255){
				$r[$k] = $str;
				continue;
			}
			
			$str .= $data[$i];
		}
		return $r;
	}
	
	function encode($data){
		$data = is_array($data) || is_object($data) ? json_encode($data) :(string) $data;
		return chr(0) . $data . chr(255);
	}
}


/**
 *	websocket第二个版本的
 */
class class_websocket_2{
	/**
	 * 解码
	 */
	function decode($data){
		if(strlen($data) < 6){
			return array();
		}
		$r = array();
		$back = $data;
		while($back){
			$type = bindec(substr(sprintf('%08b', ord($back[0])) , 4, 4));
			$encrypt =(bool) substr(sprintf('%08b', ord($back[1])), 0, 1);
			$payload = ord($back[1]) & 127;
			$datalen = strlen($back);
			if($payload == 126){
				if($datalen <= 8){
					break;
				}
				$len = substr($back, 2, 2);
				$len = unpack('n*', $len);
				$len = end($len);
				
				if($datalen < 8 + $len){
					break;
				}
				$mask = substr($back, 4, 4);
				$data = substr($back, 8, $len);
				$back = substr($back, 8 + $len);
			}elseif($payload == 127){
				if($datalen <= 14){
					break;
				}
				$len = substr($back, 2, 8);
				$len = unpack('N*', $len);
				$len = end($len);
				if($datalen < 14 + $len){
					break;
				}
				$mask = substr($back, 10, 4);
				$data = substr($back, 14, $len);
				$back = substr($back, 14 + $len);
			}else{
				$len = $payload;
				if($datalen < 6 + $len){
					break;
				}
				$mask = substr($back, 2, 4);
				$data = substr($back, 6, $len);
				$back = substr($back, 6 + $len);
			}
			
			if($type != 1){
				continue;
			}
			$str = '';
			if($encrypt){
				$len = strlen($data);
				for($i = 0; $i < $len; $i++){
					$str .= $data[$i] ^ $mask[$i % 4];
				}
			}else{
				$str = $data;
			}
			$r[] = $str;
		}
		return $r;
	}
	
	/**
	*	编码
	**/
	function encode($data){
		$data = is_array($data) || is_object($data) ? json_encode($data) :(string) $data;
		$len = strlen($data);
		$head[0] = 129;
		if($len <= 125){
			$head[1] = $len;
		}elseif($len <= 65535){
			$split = str_split(sprintf('%016b', $len), 8);
			$head[1] = 126;
			$head[2] = bindec($split[0]);
			$head[3] = bindec($split[1]);
		}else{
			$split = str_split(sprintf('%064b', $len), 8);
			$head[1] = 127;
			for($i = 0; $i < 8; $i++){
				$head[$i+2] = bindec($split[$i]);
			}
			if($head[2] > 127){
				return false;
			}
		}
		foreach($head as $k => $v){
			$head[$k] = chr($v);
		}
		return implode('', $head) . $data;
	}
}