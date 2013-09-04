<?php

class ClassModel extends Model
{
    // 自动验证设置
    protected $_validate = array(array('tbname', 'require', '必须添加所属表！', 1), );
    // 自动填充设置
    protected $_auto = array();
    protected $_map = array
    (
            'tbname' => 'table_name',
            'name'=> 'name',
            'comment' => 'comment',
    );

}

?>