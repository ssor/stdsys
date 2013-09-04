<?php
class tbInfoClassModel extends Model {
	// 自动验证设置
	protected $_validate	 =	 array(
		array('iClassID','require','所属数据类必须！',1),
		array('vId','require','ID必须！',1),
		array('vName','require','名称必须！',1),
		array('vNamechn','require','中文名称必须！',1),
		array('vType','require','数据类型必须！',1),
		array('iLength','require','数据长度必须！',1),
		array('vId','','该ID已经存在',0,'unique','add'),
		);
	// 自动填充设置
	protected $_auto	 =	 array(
		);

}
?>