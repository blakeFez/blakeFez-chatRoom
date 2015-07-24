<?php
/**
 * @Author: blakeFez
 * @Date: 2015-07-17
 * @note: 公用函数
 */
class CommonUtil{
	
	/**
	 * 解析http头部
	 */
	public static function parse_header($html, $strtolower = false){
		if(!$html){
			return array();
		}
		$html = str_replace( "\r\n", "\n", $html );
		$html = explode( "\n\n", $html, 2 );
		$header = explode( "\n", $html[0] );
		$r = array();
		foreach($header as $k => $v ){
			if($v){
				$v = explode(':', $v, 2);
				if(isset($v[1])){
					if($strtolower){
						$v[0] = strtolower( $v[0] );
					}
					if(substr($v[1], 0 , 1) == ' '){
						$v[1] = substr( $v[1], 1 );
					}
					$r[trim($v[0])] = $v[1];
				}elseif(empty($r['status']) && preg_match('/^(HTTP|GET|POST)/', $v[0])){
					$r['status'] = $v[0];
				}else{
					$r[] = $v[0];
				}
			}
		}
		if(!empty($html[1])){
			$r['html'] = $html[1] ;
		}
		return $r;
	}
	
	/**
	 * 字符串转为数组
	 */
	public static function string_turn_array($s){
		if(is_array($s)){
			return $s;
		}
		parse_str($s, $r);
		if(get_magic_quotes_gpc()){
			$r = self::stripslashes_array($r);
		}
		return $r;
	}
	
	/**
	 * 取消转义 数组
	 */
	public static function stripslashes_array($value) {
		if(is_array($value)){
			$value = array_map(__FUNCTION__, $value);
		}elseif(is_object($value)){
			$vars = get_object_vars($value);
			foreach ($vars as $key=>$data) {
				$value->{$key} = self::stripslashes_array( $data );
			}
		}else{
			$value = stripslashes($value);
		}
		return $value;
	}
	
	/**
	 * 给一个文件加锁，用于保证就一个系统在跑
	 */
	public static function add_lock(){
		$file = fopen(DIR_WEBSOCKET.'/lock.txt', 'w+');
		if(!flock($file, LOCK_EX)){
			fclose($file);
			return false;
		}
		return true;
	}
}