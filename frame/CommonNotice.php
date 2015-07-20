<?php
/**
 * @author: blakeFez
 * @date: 2015-07-18
 * @note: 通知相关
 */
class CommonNotice{
    
	/**
	 * 企业qq通知，不同企业的企业qq的api是不同的
	 */
	public static function BusinessQQNotice($users,$title,$message,$type = 1,$isRich = 0){
		if(empty($users) || empty($title) || empty($message)) return false;
		
		if($type == 1){//简单型
			$url = "SOMEURL";//FIXME 这里的url要根据实际情况修改
		}else{//广播型
			$url = "SOMEURL";//FIXME 这里的url要根据实际情况修改
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