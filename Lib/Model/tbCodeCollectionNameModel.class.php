<?php

class tbCodeCollectionNameModel extends Model
{
    // 自动验证设置
    protected $_validate = array(array('collectionID', 'require', 'ID不能为空！', 1),
        array('collectionName', 'require', '名字不能为空！', 2), array('collectionID', '',
        '该ID已经存在', 0, 'unique', 'add'), );
    // 自动填充设置
    protected $_auto = array();

}

?>