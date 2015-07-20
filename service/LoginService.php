<?php
/**
 * @author: blakeFez
 * @date: 2015-07-19
 * @note: 聊天室主页面
 */
class LoginService{
    
    /**
     * 判断登录
     */
    public static function checkLogin(){
        if(empty($_SESSION['number'])){
            header('Location:index.php?c=chat&a=login');
        }else{
            if(empty($_SESSION['nickName'])){
                $mysql = CommonMysql::getInstance('localhost','root','');
                $data = $mysql->query("select nickName from test.user where number=:number",array(':number'=>$_SESSION['number']));
                if(!empty($data)){
                	$_SESSION['nickName'] = $data[0]['nickName'];
                }
            }
            $user['number'] = $_SESSION['number'];
            $user['nickName'] = $_SESSION['nickName'];
            return $user;
        }
    }
}