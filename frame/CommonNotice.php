<?php
/**
 * @author: blakeFez
 * @date: 2015-07-18
 * @note: 通知相关
 */
class CommonNotice{
    
	public static function BusinessQQNotice($users,$title,$message,$type = 1,$isRich = 0){
		if(empty($users) || empty($title) || empty($message)) return false;
		
		if($type == 1){//简单型
			$url = "https://qq.4399houtai.com/open/api/send_msg.php";
		}else{//广播型
			$url = "https://qq.4399houtai.com/open/api/broadcast.php";
		}
		$jobNumber = implode(',', $users);
		$url .= "?receiver=".$jobNumber."&title=".$title."&msg=".$message."&is_rich=".$isRich;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$data = curl_exec($curl);
		curl_close($curl);
		$result = substr($data, stripos($data, '{'));
		$result = json_decode($result,true);
		if($result['ret'] == 0){
			return true;
		}else{
			return false;
		}
	}
}