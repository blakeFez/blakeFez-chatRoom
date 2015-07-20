<?php
/**
 * @author: blakeFez
 * @date: 2015-07-19
 * @note: 随机数相关
 */
class CommonRand{
    
    /**
     * 获取随机数
     */
    public static function getRandNumber($length = 6){
        $result = (string)rand(1,9);
        for($i = 1; $i < $length; $i++){
            $result .= (string)rand(0,9);
        }
        return $result;
    }
}