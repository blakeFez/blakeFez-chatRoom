<?php
/**
 * @author: blakeFez
 * @date: 2015-07-19
 * @note: 聊天室主页面
 */
class ChatController extends Controller{
	
    /**
     * 聊天室主页面
     */
    public function index(){
        $user = LoginService::checkLogin();
        $this->out['user'] = $user;
    }
    
    /**
     * 登录界面
     */
    public function login(){
    	$this->out['browser'] = false;
    	if(strpos($_SERVER["HTTP_USER_AGENT"],"Chrome") === false && strpos($_SERVER["HTTP_USER_AGENT"],"Firefox") === false){//浏览器检测
    		$this->out['browser'] = true;
    	}
    }
    
    /**
     * 获取验证码
     */
    public function getAutoCode(){
        $this->outType = 'json';
        $number = CommonRequest::getRequest('number');
        $randNumber = CommonRand::getRandNumber(6);
        $cache = CommonCache::getInstance('localhost', 11211);
        $cache->set($number."_authCode",$randNumber,MEMCACHE_COMPRESSED,600);
        $title = 'BlakeFez聊天室验证码';
        $message = '您的BlakeFez聊天室验证码为'.$randNumber.',祝您聊天愉快！';
        CommonNotice::BusinessQQNotice(array($number), $title, $message);
        $this->out = array('flag'=>true);
    }
    
    /**
     * 判断验证码是否正确
     */
    public function checkAuthCode(){
    	$this->outType = 'json';
        $number = CommonRequest::getRequest('number');
        $autoCode = CommonRequest::getRequest('authCode');
        $cache = CommonCache::getInstance('localhost', 11211);
        $randNumber = $cache->get($number."_authCode");
        if($autoCode == $randNumber){
        	$_SESSION['number'] = $number;
        	$this->out = array('flag'=>true);
        }else{
        	$this->out = array('flag'=>false);
        }
    }
    
    /**
     * 设置昵称
     */
    public function setNickName(){
        $this->outType = 'json';
        $number = $_SESSION['number'];
        $name = CommonRequest::getRequest('name');
        $mysql = CommonMysql::getInstance('localhost','root','');
        $data = $mysql->query('select nickName from test.user where nickName=:name', array('name'=>$name));
        if(!empty($data)){
        	$this->out = array('flag'=>true,'msg'=>'该昵称已被占用！');
        }else{
        	$mysql->execute("insert into test.user(number,nickName) value ('$number','$name')");
        	$id = $mysql->getInsertID();
        	if(!empty($id)){
        		$_SESSION['nickName'] = $name;
        		$this->out = array('flag'=>true,'msg'=>'该昵称已被占用！');
        	}else{
        		$this->out = array('flag'=>false,'msg'=>'网络异常，请稍后重试！');
        	}
        }
    }
}