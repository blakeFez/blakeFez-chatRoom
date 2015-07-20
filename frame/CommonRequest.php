<?php
/**
 * @author: blakeFez
 * @date: 2015-07-18
 * @note: 获取参数相关
 */
class CommonRequest{
    
    /**
     * 获取参数
     */
    public static function getRequest($option){
        if(isset($_REQUEST[$option])){
            return $_REQUEST[$option];
        }else{
            return '';
        }
    }
}