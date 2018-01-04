<?php
namespace main\app\server\async;

$serv = new \swoole_server("127.0.0.1", 9501);
$serv->set(array(
    'worker_num' => 2,   //工作进程数量
    'daemonize' => false, //是否作为守护进程
));
$serv->on('connect', function ($serv, $fd){
    echo "Client:Connect.\n";
});
$serv->on('receive', function ($serv, $fd, $from_id, $data) {

    //echo "Client:Data. fd=$fd|from_id=$from_id|data=$data\n";

    $json_obj = json_decode( $data );
    if( !isset($json_obj->cmd) ){
        $serv->send($fd,['ret'=>0,'msg'=>'cmd is null']);
        $serv->close($fd);
        return ;
    }

    list ( $class, $method ) = explode('.',$json_obj->cmd);

    $full_class = sprintf("main\\app\\server\\async\\%s", $class);
    require_once "{$class}.php";
    $class_obj = new $full_class();
    if (! method_exists($class_obj, $method)) {
        $error_code = 500;
        $error_msg = $class.'->'.$method . ' no found;';
        //$serv->send($fd,['ret'=>$error_code,'msg'=>$error_msg]);
        $serv->close($fd);
        echo error_code.' '.$error_msg."\n";
        return;
    }
    // 开始执行worker
    call_user_func_array( [ $class_obj, $method ], [$json_obj] );
});
$serv->on('close', function ($serv, $fd) {
    echo "Client: Close.\n";
});
$serv->start();
