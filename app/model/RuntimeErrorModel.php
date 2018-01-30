<?php

namespace main\app\model;


/**
 * 运行时错误
 * @package main\app\model
 */
class RuntimeErrorModel extends DbModel
{

    public $prefix = 'log_';

    public $table = 'runtime_error';

    public $fields = ' * ';

    public $primary_key = 'id';

}