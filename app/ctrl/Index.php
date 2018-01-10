<?php

namespace main\app\ctrl;

use main\app\model\RuntimeErrorModel;

class Index extends BaseCtrl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * index
     */
    public function index()
    {
        1 / 0;
        echo 'index';
    }

    public function runtimeError()
    {
        $model = new RuntimeErrorModel();
        $data = [];
        if (!isset($_GET['file']) || !isset($_GET['line'])) {
            echo 'param_error';
            die;
        }
        $date = date('Y-m-d');
        $data['md5'] = md5($_GET['file'] . $_GET['line'].$date);
        $data['file'] = $_GET['file'];
        $data['line'] = $_GET['line'];
        $data['time'] = time();
        $data['date'] = $date;
        list($ret,$msg) = $model->insert($data);
        if($ret){
            echo "Send mail";
        }else{
            echo 'Failed';
        }

    }


}