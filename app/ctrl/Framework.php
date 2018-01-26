<?php
/**
 * 开发框架测试的代码,请勿随意修改或删除
 * User: sven
 * Date: 2017/7/7 0007
 * Time: 下午 3:49
 */

namespace main\app\ctrl;

use main\app\model\DbModel;

/**
 * Class framework
 * @package main\app\ctrl
 */
class Framework extends BaseCtrl
{
    public function index()
    {
        echo 'index';
    }


    public function get_php_ini()
    {
        $ret = new \stdClass();
        if (isset($_REQUEST['inis'])) {
            $inis = json_decode($_REQUEST['inis']);
            foreach ($inis as $i => $key) {
                $v = ini_get($key);
                $key = str_replace('.', '_', $key);
                $ret->$key = $v;
            }
        }
        $this->ajaxSuccess('ok', $ret);
    }

    public function validate_dir()
    {
        $dirs = [
            PRE_ROOT_PATH,
            APP_PATH,
            CTRL_PATH,
            MODEL_PATH,
            API_PATH,
            VIEW_PATH,
            PUBLIC_PATH,
            STORAGE_PATH,
            STORAGE_PATH . 'upload',
            STORAGE_PATH . 'cache',
            STORAGE_PATH . 'session',
            STORAGE_PATH . 'log',
        ];

        $ret = [];
        foreach ($dirs as $dir) {
            $v = [];
            $v['exists'] = file_exists($dir);
            $v['writable'] = file_exists($dir);
            $v['path'] = $dir;
            $ret[] = $v;
        }

        $this->ajaxSuccess('ok', $ret);
    }

    public function route()
    {
        echo 'route';
    }

    /**
     * 测试 伪静态路由参数
     * @return string
     */
    public function arg()
    {
        $this->ajaxSuccess('ok', $_GET['_target']);
    }

    /**
     * 触发错误异常
     * @return bool
     */
    public function show_error()
    {
        timezone_open(1202229163);
        100 / 0;
        new \DateTimeZone(1202229163);
        echo 'ok';
    }

    public function show_exception()
    {
        throw  new \framework\HornetLogicException(500, 'throw exception');
        echo 'ok';
    }


    public function db_prepare()
    {
        $dbModel = new DbModel();
        $dbModel->table = 'user';
        $dbModel->getTable();
        $dbModel->db->pdo;
        $this->ajaxSuccess('pdo', $dbModel->db->pdo);
    }

    /**
     * 纯sql语句执行时是否有注入危险
     * @return bool
     */
    public function sql_inject()
    {
        $dbModel = new DbModel();
        $pwd = md5('pwd123456');
        $time = time();
        $user = [];
        try {
            $sql = "INSERT INTO `xphp_user` ( `name`, `phone`, `password`, `email`, `status`, `reg_time`, `last_login_time`) 
                VALUES ( '帅哥', '13002510000', '{$pwd}', 'fun@163.com', 1, 0, {$time}) ;";
            $ret = $dbModel->db->exec($sql);
        } catch (\Exception $e) {
            $insert_id = $dbModel->db->getLastInsId();
            if (!empty($insert_id)) {
                $sql = "Delete From `xphp_user` Where id = $insert_id  ";
                echo $sql;
                $dbModel->db->exec($sql);
            }
            $this->ajaxSuccess($user, $e->getMessage());
            return;
        }

        if ($ret) {
            $insert_id = $dbModel->db->getLastInsId();
            $phone = $_POST['phone'];
            $pwd = $_POST['pwd'];
            $sql = "Select * From `xphp_user` Where phone='$phone' AND password='$pwd'";
            $user = $dbModel->db->getRow($sql);
            if (!empty($insert_id)) {
                $sql = "Delete From `xphp_user` Where phone = '13002510000'  ";
                //echo $sql;
                $dbModel->db->exec($sql);
            }
        }
        $this->ajaxSuccess($user, 'ok');
    }

    /**
     * 纯sql语句执行时是否有注入危险,注入一个删除一个表全部数据
     *
     */
    public function sql_inject_delete()
    {
        $dbModel = new DbModel();
        $pwd = md5('pwd123456');
        $time = time();

        $sql = "INSERT INTO `xphp_user` ( `name`, `phone`, `password`, `email`, `status`, `reg_time`, `last_login_time`) 
            VALUES ( '帅哥', '13002510000', '{$pwd}', 'fun@163.com', 1, 0, {$time}) ;";
        $dbModel->db->exec($sql);

        $insert_id = $dbModel->db->getLastInsId();
        $phone = $_POST['phone'];
        $sql = "Select * From `xphp_user` Where phone='$phone'  limit 1";
        //echo $sql;
        $dbModel->db->getRow($sql);
        $sql = "Select * From `xphp_user`    limit 1";
        $user = $dbModel->db->getRow($sql);
        if (!empty($insert_id)) {
            $sql = "Delete From `xphp_user` Where id = '$insert_id'  ";
            $dbModel->db->exec($sql);
        }

        $this->ajaxSuccess($user, 'ok');
    }

    /**
     * 手工检测sq注入
     */
    public function do_sql_inject()
    {
        $url = ROOT_URL . "/framework/sql_inject";
        $post_data['phone'] = "13002510000' or '1'='1 ";
        $post_data['pwd'] = "121";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    /**
     * 手工检测sq注入
     */
    public function do_sql_inject_delete()
    {
        $url = ROOT_URL . "/framework/sql_inject_delete";
        $post_data['phone'] = "13002510000'  ; DELETE FROM xphp_user;Select * From `xphp_user` WHERE 1 or phone = '";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    /**
     * 打开第一个会话页面
     * @require_type {}
     */
    public function session_step1()
    {
        if (isset($_SESSION['test_session1'])) {
            unset($_SESSION['test_session1']);
        }
        $_SESSION['test_session1'] = time();
        $_SESSION['session_id'] = session_id();
        $this->ajaxSuccess('ok', $_SESSION);
    }

    /**
     * 打开第二个会话页面
     */
    public function session_step2()
    {
        $this->ajaxSuccess('ok', $_SESSION);
    }


    /**
     * 返回符合期望的格式
     * @require_type []
     * @return array
     */
    public function expect_json()
    {
        $arr = [9, 1, 8, 3, 4, 1, 8, 5, 7];
        $arr = array_unique($arr);
        $newArr = [];
        // 或者使用 array_values 函数
        foreach ($arr as $k => $v) {
            $newArr[] = $v;
        }
        $this->ajaxSuccess('ok', $newArr);
    }

    /**
     * 故意返回不符合期望的格式
     * @require_type []
     * @return array
     */
    public function not_expect_json()
    {
        $arr = [9, 1, 8, 3, 4, 1, 8, 5, 7];
        // 注意,经过 array_unique 处理后返回值不是一个纯粹的数组,
        // 而是以字符串下标的新数组 ["0": 9,"1": 1,"2": 8,"3": 3,"4": 4,"7": 5,"8": 7]
        $arr = ["0"=> 9,"1"=> 1,"2"=>  8,"3"=> 3,"4"=> 4,"7"=> 5,"8"=>  7];//array_unique($arr);
        $this->ajaxSuccess('ok', $arr);
    }

    /**
     * 返回不符合的期望格式
     * @require_type {"name":"","id":121,"fish":[1,3,4],"props":{"x":123,"y":128}}
     * @return array
     */
    public function not_expect_mix_json()
    {
        $obj = new \stdClass();
        $obj->id = 12333;
        $obj->name = "sven";
        $obj->fish = [232, 4, 34, 5];
        $obj->props = [];
        $this->ajaxSuccess('ok', $obj);
    }

    /**
     * 返回符合期望的格式
     * @require_type {"name":"","id":121,"fish":[1,3,4],"props":{"x":123,"y":128}}
     * @return array
     */
    public function expect_mix_json()
    {
        //return json_decode( '[]' );
        $obj = new \stdClass();
        $obj->id = 12333;
        $obj->name = "sven";
        $obj->fish = [232, 4, 34, 5];
        $obj_props = new \stdClass();
        $obj_props->x = 123;
        $obj_props->y = 128;
        $obj->props = $obj_props;
        $this->ajaxSuccess('ok', $obj);
    }

    public function ajax_data()
    {
        $ret = [];
        $page = request_get('page');
        if (empty($page)) {
            $page = 1;
        } else {
            $page = intval($page);
        }
        $ret['page_str'] = getPageStrByAjax(1000, $page, 10);
        $ret['list'] = print_r($_REQUEST, true);
        $this->ajaxSuccess('ok', $ret);
    }

    public function ajax_page()
    {
        $this->render('example/ajax_page.php');
    }
}
