<?php
/**
 * @author: blakeFez
 * @date: 2015-07-19
 * @note: webSocket服务端
 */
class SocketCommand extends Command{
    
    /**
     * 运行socket服务端
     */
    public function index(){
        require('socketConfig.php');
        if(!CommonUtil::add_lock('lock')){//用于判断是否已经开启
            die('Running');
        }
        //设置超时时间
        ignore_user_abort(true);
        set_time_limit(0);
        
        //修改内存
        ini_set('memory_limit', WEBSOCKET_MEMORY);
        
        $webSocket = new SocketService();
        $webSocket->run();
        echo socket_strerror($webSocket->error());
    }
}